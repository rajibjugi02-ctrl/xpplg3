<?php
session_start();
require_once '../includes/db.php';
require_once 'includes/auth.php'; // ensure admin is logged in

$page_title = 'Activity Log - Admin Panel';
$active_menu = 'activity_log';

// Handle Clear All Logs
if (isset($_POST['clear_logs'])) {
    $pdo->exec("TRUNCATE TABLE activity_logs");
    $_SESSION['flash_msg'] = "Semua riwayat aktivitas berhasil dihapus.";
    $_SESSION['flash_type'] = "success";
    header("Location: activity_log.php");
    exit;
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search and Filter
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$whereClause = "WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $whereClause .= " AND user_type = ?";
    $params[] = $filter;
}

if (!empty($search)) {
    $whereClause .= " AND (user_identifier LIKE ? OR action LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Get total for pagination
$countQuery = "SELECT COUNT(id) FROM activity_logs $whereClause";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$total_records = $stmtCount->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get logs for current page
$query = "SELECT * FROM activity_logs $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Custom Style for Data Table -->
<style>
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.search-form { display: flex; gap: 0.5rem; }
.search-form input, .search-form select {
    padding: 0.5rem 1rem; border: 1px solid var(--border); border-radius: 0.5rem;
    background: var(--bg); color: var(--text-main); font-family: 'Inter', sans-serif;
}
.search-form button {
    padding: 0.5rem 1rem; background: var(--primary); color: #fff; border: none;
    border-radius: 0.5rem; cursor: pointer; font-weight: 600;
}
.search-form button:hover { background: var(--primary-hover); }

.log-table { width: 100%; border-collapse: collapse; background: var(--card-bg); border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.log-table th, .log-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--border); }
.log-table th { background: rgba(0,0,0,0.02); font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
.log-table tbody tr:hover { background: rgba(0,0,0,0.01); }
.log-table tbody tr:last-child td { border-bottom: none; }

.badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600; text-transform: capitalize; }
.badge-admin { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.badge-student { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
.badge-visitor { background: rgba(16, 185, 129, 0.1); color: #10b981; }

.time-text { font-size: 0.85rem; color: var(--text-muted); }
.action-text { font-weight: 500; }

.btn-danger { background: #ef4444; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
.btn-danger:hover { background: #dc2626; }
</style>

<div class="content-header" style="margin-bottom: 2rem;">
    <h2><i class="fa-solid fa-clock-rotate-left"></i> Riwayat Aktivitas</h2>
    <p>Pantau semua aktivitas admin, siswa, dan pengunjung di website.</p>
</div>

<div class="card">
    <div class="toolbar">
        <form method="GET" action="" class="search-form">
            <select name="filter">
                <option value="all" <?= $filter == 'all' ? 'selected' : '' ?>>Semua Role</option>
                <option value="admin" <?= $filter == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="student" <?= $filter == 'student' ? 'selected' : '' ?>>Siswa</option>
                <option value="visitor" <?= $filter == 'visitor' ? 'selected' : '' ?>>Pengunjung</option>
            </select>
            <input type="text" name="search" placeholder="Cari nama atau aksi..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fa-solid fa-search"></i> Cari</button>
            <?php if (!empty($search) || $filter !== 'all'): ?>
                <a href="activity_log.php" style="padding: 0.5rem 1rem; background: var(--bg); color: var(--text-main); text-decoration: none; border-radius: 0.5rem; border: 1px solid var(--border);">Reset</a>
            <?php endif; ?>
        </form>
        
        <form method="POST" id="clearForm" onsubmit="return false;">
            <button type="button" class="btn-danger" onclick="confirmClear()">
                <i class="fa-solid fa-trash-can"></i> Bersihkan Log
            </button>
            <input type="hidden" name="clear_logs" value="1">
        </form>
    </div>

    <div style="overflow-x: auto;">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Waktu (WIB)</th>
                    <th>Role</th>
                    <th>Nama User</th>
                    <th>Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="time-text">
                                <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= htmlspecialchars($log['user_type']) ?>">
                                    <?= htmlspecialchars($log['user_type']) ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;">
                                <?= htmlspecialchars($log['user_identifier']) ?>
                            </td>
                            <td class="action-text">
                                <?= htmlspecialchars($log['action']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Belum ada riwayat aktivitas.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div style="font-size: 0.85rem; color: var(--text-muted);">
            Menampilkan <?= count($logs) ?> dari <?= $total_records ?> log
        </div>
        <div style="display: flex; gap: 0.25rem;">
            <?php
            $qs = $_GET; // preserve other query parameters
            
            // Previous button
            if ($page > 1) {
                $qs['page'] = $page - 1;
                $prevUrl = '?' . http_build_query($qs);
                echo "<a href='$prevUrl' style='padding: 0.5rem 0.75rem; background: var(--bg); border: 1px solid var(--border); border-radius: 0.5rem; color: var(--text-main); text-decoration: none;'><i class='fa-solid fa-chevron-left'></i></a>";
            }
            
            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                // only show nearby pages
                if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)) {
                    $qs['page'] = $i;
                    $url = '?' . http_build_query($qs);
                    $activeStyle = ($i == $page) ? "background: var(--primary); color: white; border-color: var(--primary);" : "background: var(--bg); color: var(--text-main); border: 1px solid var(--border);";
                    echo "<a href='$url' style='padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; $activeStyle'>$i</a>";
                } elseif ($i == $page - 3 || $i == $page + 3) {
                    echo "<span style='padding: 0.5rem; color: var(--text-muted);'>...</span>";
                }
            }
            
            // Next button
            if ($page < $total_pages) {
                $qs['page'] = $page + 1;
                $nextUrl = '?' . http_build_query($qs);
                echo "<a href='$nextUrl' style='padding: 0.5rem 0.75rem; background: var(--bg); border: 1px solid var(--border); border-radius: 0.5rem; color: var(--text-main); text-decoration: none;'><i class='fa-solid fa-chevron-right'></i></a>";
            }
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Tampilkan alert flash_msg jika ada (menggunakan sweetalert dari layout utama jika ada)
<?php if (isset($_SESSION['flash_msg'])): ?>
    Swal.fire({
        icon: '<?= $_SESSION['flash_type'] ?? 'success' ?>',
        title: 'Berhasil',
        text: '<?= addslashes($_SESSION['flash_msg']) ?>',
        timer: 3000,
        showConfirmButton: false
    });
    <?php unset($_SESSION['flash_msg']); unset($_SESSION['flash_type']); ?>
<?php endif; ?>

function confirmClear() {
    Swal.fire({
        title: 'Bersihkan Log?',
        text: "Semua riwayat aktivitas akan dihapus permanen. Lanjutkan?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('clearForm').submit();
        }
    })
}
</script>

<?php include 'includes/footer.php'; ?>
