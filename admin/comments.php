<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title = 'Moderasi Komentar';
$active_menu = 'comments';

// Handle Toggle Visibility
if (isset($_POST['toggle_id'])) {
    $id = intval($_POST['toggle_id']);
    try {
        // Toggle the is_visible status
        $pdo->query("UPDATE project_comments SET is_visible = NOT is_visible WHERE id = $id");
        $msg = "Status komentar berhasil diubah.";
        $msgType = "success";
    } catch(Exception $e) {
        $msg = "Gagal mengubah status: " . $e->getMessage();
        $msgType = "error";
    }
}

// Handle Delete (optional, but good to have)
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    try {
        // Delete comment and its replies
        $pdo->query("DELETE FROM project_comments WHERE id = $id OR parent_id = $id");
        $msg = "Komentar berhasil dihapus.";
        $msgType = "success";
    } catch(Exception $e) {
        $msg = "Gagal menghapus komentar: " . $e->getMessage();
        $msgType = "error";
    }
}

// Fetch comments with project title
$comments = $pdo->query("
    SELECT c.*, p.title as project_title 
    FROM project_comments c 
    JOIN projects p ON c.project_id = p.id 
    ORDER BY c.created_at DESC
")->fetchAll();

include 'includes/header.php';
?>

<div class="admin-page-header">
    <div class="header-content">
        <h1>Moderasi Komentar</h1>
        <p>Kelola komentar pengguna pada project kelas.</p>
    </div>
</div>

<div class="admin-card">
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="15%">Pengguna</th>
                    <th width="20%">Project</th>
                    <th width="30%">Komentar</th>
                    <th width="10%">Status</th>
                    <th width="10%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($comments)): ?>
                <tr><td colspan="6" class="text-center">Belum ada komentar</td></tr>
                <?php else: foreach ($comments as $c): ?>
                <tr>
                    <td><?= date('d M Y H:i', strtotime($c['created_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($c['user_name']) ?></strong></td>
                    <td><?= htmlspecialchars($c['project_title']) ?></td>
                    <td>
                        <?= nl2br(htmlspecialchars($c['comment'])) ?>
                        <?php if ($c['parent_id']): ?>
                        <div style="font-size: 0.8rem; color: var(--primary); margin-top: 0.2rem;"><i class="fa-solid fa-reply"></i> Balasan</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($c['is_visible']): ?>
                        <span class="badge" style="background:#dcfce7;color:#166534;padding:0.2rem 0.5rem;border-radius:0.25rem;font-size:0.8rem;">Ditampilkan</span>
                        <?php else: ?>
                        <span class="badge" style="background:#fee2e2;color:#991b1b;padding:0.2rem 0.5rem;border-radius:0.25rem;font-size:0.8rem;">Disembunyikan</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="toggle_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-icon" style="background:<?= $c['is_visible'] ? '#f59e0b' : '#10b981' ?>;color:#fff;" title="<?= $c['is_visible'] ? 'Sembunyikan' : 'Tampilkan' ?>">
                                    <i class="fa-solid <?= $c['is_visible'] ? 'fa-eye-slash' : 'fa-eye' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus komentar ini?');">
                                <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-icon btn-delete" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (isset($msg)): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $msgType ?>',
            title: '<?= $msgType === "success" ? "Berhasil" : "Gagal" ?>',
            text: '<?= addslashes($msg) ?>',
            timer: 2000,
            showConfirmButton: false
        });
    });
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
