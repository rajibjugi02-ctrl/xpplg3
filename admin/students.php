<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title  = 'Kelola Siswa';
$active_menu = 'students';
$msg = '';

$uploadDir = '../assets/uploads/students/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $id   = trim($_POST['id'] ?? $_POST['new_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $photo = '';

        // Handle File Upload
        if (!empty($_FILES['photo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $photo = 'student_' . preg_replace('/[^a-zA-Z0-9]/', '', $id) . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo);
            }
        }

        if ($action === 'add') {
            if ($id && $name) {
                try {
                    $pdo->prepare("INSERT INTO students (id, name, photo) VALUES (?,?,?)")
                        ->execute([$id, $name, $photo ?: null]);
                    $msg = ['type'=>'success','text'=>'Siswa berhasil ditambahkan!'];
                } catch (Exception $e) {
                    $msg = ['type'=>'danger','text'=>'ID sudah digunakan atau terjadi error.'];
                }
            }
        } elseif ($action === 'edit') {
            $old_id = trim($_POST['old_id']);
            $new_id = trim($_POST['new_id']);

            if ($photo) {
                // Remove old photo if exists
                $row = $pdo->prepare("SELECT photo FROM students WHERE id=?");
                $row->execute([$old_id]);
                $oldImg = $row->fetchColumn();
                if ($oldImg && file_exists($uploadDir . $oldImg)) unlink($uploadDir . $oldImg);

                $pdo->prepare("UPDATE students SET id=?, name=?, photo=? WHERE id=?")
                    ->execute([$new_id, $name, $photo, $old_id]);
            } else {
                $pdo->prepare("UPDATE students SET id=?, name=? WHERE id=?")
                    ->execute([$new_id, $name, $old_id]);
            }
            $msg = ['type'=>'success','text'=>'Data siswa berhasil diperbarui!'];
        }
    } elseif ($action === 'delete') {
        $id = trim($_POST['id']);
        $row = $pdo->prepare("SELECT photo FROM students WHERE id=?");
        $row->execute([$id]);
        $img = $row->fetchColumn();
        if ($img && file_exists($uploadDir . $img)) unlink($uploadDir . $img);

        $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
        $msg = ['type'=>'success','text'=>'Siswa berhasil dihapus!'];
    }
}

$students = $pdo->query("SELECT * FROM students ORDER BY id ASC")->fetchAll();
include 'includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><i class="fa-solid fa-circle-info"></i> <?= $msg['text'] ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Daftar Siswa (<?= count($students) ?>)</h3>
        <div style="display:flex;gap:0.75rem;align-items:center;">
            <input type="text" id="searchInput" class="form-control"
                   style="width:180px;padding:0.45rem 0.75rem;" placeholder="Cari siswa...">
            <button class="btn btn-primary btn-sm" onclick="openModal('addModal')">
                <i class="fa-solid fa-plus"></i> Tambah
            </button>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table id="studentsTable">
                <thead>
                    <tr><th>No.</th><th>Foto</th><th>ID</th><th>Nama Siswa</th><th style="text-align:right;">Aksi</th></tr>
                </thead>
                <tbody>
                <?php $no = 1; foreach ($students as $s): ?>
                    <tr>
                        <td style="color:#94a3b8;font-size:0.8rem;"><?= $no++ ?></td>
                        <td>
                            <?php if (!empty($s['photo'])): ?>
                            <img src="../assets/uploads/students/<?= htmlspecialchars($s['photo']) ?>"
                                 style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #eff6ff;">
                            <?php else: ?>
                            <div style="width:40px;height:40px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#94a3b8;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-blue"><?= htmlspecialchars($s['id']) ?></span></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td style="text-align:right;white-space:nowrap;">
                            <button class="btn btn-outline btn-sm"
                                onclick="openEditModal('<?= htmlspecialchars(addslashes($s['id']), ENT_QUOTES) ?>','<?= htmlspecialchars(addslashes($s['name']), ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form id="del-<?= $s['id'] ?>" method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($s['id']) ?>">
                            </form>
                            <button class="btn btn-danger btn-sm"
                                onclick="confirmDelete('del-<?= $s['id'] ?>', '<?= htmlspecialchars(addslashes($s['name']), ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===== ADD MODAL ===== -->
<div class="admin-modal" id="addModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Tambah Siswa Baru</h3>
            <button class="modal-close" onclick="closeModal('addModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label>ID Siswa</label>
                    <input type="text" name="id" class="form-control" placeholder="e.g. 046" required maxlength="10">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama siswa" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Foto Profile <span style="color:#94a3b8;font-weight:400;">(Opsional)</span></label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== EDIT MODAL ===== -->
<div class="admin-modal" id="editModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Data Siswa</h3>
            <button class="modal-close" onclick="closeModal('editModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="old_id" id="editOldId">
            <div class="modal-body">
                <div class="form-group">
                    <label>ID Siswa</label>
                    <input type="text" name="new_id" id="editId" class="form-control" required maxlength="10">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Ganti Foto <span style="color:#94a3b8;font-weight:400;">(Biarkan kosong jika tidak ingin mengubah foto)</span></label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#studentsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
function openEditModal(id, name) {
    document.getElementById('editOldId').value = id;
    document.getElementById('editId').value    = id;
    document.getElementById('editName').value  = name;
    openModal('editModal');
}
</script>

<?php include 'includes/footer.php'; ?>
