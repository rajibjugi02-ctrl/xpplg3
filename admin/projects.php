<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title  = 'Kelola Project';
$active_menu = 'projects';
$msg = '';

$uploadDir = '../assets/uploads/projects/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add' || $action === 'edit') {
        $title = trim($_POST['title']);
        $desc  = trim($_POST['description']);
        $link  = trim($_POST['link']);
        $makers = trim($_POST['makers'] ?? '');
        $photo = null;

        if (!empty($_FILES['image']['name'])) {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $photo = 'project_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $photo);
            }
        }

        if ($action === 'add') {
            $pdo->prepare("INSERT INTO projects (title,description,makers,image,link) VALUES (?,?,?,?,?)")
                ->execute([$title, $desc, $makers, $photo, $link]);
            $msg = ['type'=>'success','text'=>'Project berhasil ditambahkan!'];
        } else {
            $id = (int)$_POST['id'];
            if ($photo) {
                $pdo->prepare("UPDATE projects SET title=?,description=?,makers=?,image=?,link=? WHERE id=?")
                    ->execute([$title, $desc, $makers, $photo, $link, $id]);
            } else {
                $pdo->prepare("UPDATE projects SET title=?,description=?,makers=?,link=? WHERE id=?")
                    ->execute([$title, $desc, $makers, $link, $id]);
            }
            $msg = ['type'=>'success','text'=>'Project berhasil diperbarui!'];
        }
    } elseif ($action === 'delete') {
        $id  = (int)$_POST['id'];
        $row = $pdo->prepare("SELECT image FROM projects WHERE id=?");
        $row->execute([$id]);
        $proj = $row->fetch();
        if ($proj && $proj['image'] && file_exists($uploadDir . $proj['image'])) unlink($uploadDir . $proj['image']);
        $pdo->prepare("DELETE FROM projects WHERE id=?")->execute([$id]);
        $msg = ['type'=>'success','text'=>'Project berhasil dihapus!'];
    }
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
include 'includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><i class="fa-solid fa-circle-info"></i> <?= $msg['text'] ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Daftar Project (<?= count($projects) ?>)</h3>
        <button class="btn btn-primary btn-sm" onclick="openModal('addProjModal')">
            <i class="fa-solid fa-plus"></i> Tambah Project
        </button>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($projects)): ?>
        <div style="text-align:center;padding:3rem;color:#94a3b8;">
            <i class="fa-solid fa-laptop-code" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            Belum ada project. Klik "Tambah Project" untuk memulai.
        </div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Gambar</th><th>Judul & Deskripsi</th><th>Link</th><th style="text-align:right;">Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                    <tr>
                        <td>
                            <?php if ($p['image']): ?>
                            <img src="../assets/uploads/projects/<?= htmlspecialchars($p['image']) ?>"
                                 style="width:52px;height:40px;object-fit:cover;border-radius:0.4rem;border:1px solid #e2e8f0;">
                            <?php else: ?>
                            <div style="width:52px;height:40px;background:#f1f5f9;border-radius:0.4rem;display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i class="fa-solid fa-image"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="display:block;font-size:0.9rem;"><?= htmlspecialchars($p['title']) ?></strong>
                            <span style="font-size:0.78rem;color:#94a3b8;"><?= mb_substr(htmlspecialchars($p['description']),0,55) ?>...</span>
                        </td>
                        <td>
                            <?php if ($p['link']): ?>
                            <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank" style="font-size:0.8rem;color:var(--primary);"><i class="fa-solid fa-link"></i> Link</a>
                            <?php else: ?><span style="color:#94a3b8;font-size:0.8rem;">—</span><?php endif; ?>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <button class="btn btn-outline btn-sm"
                                onclick="openEditProj(<?= $p['id'] ?>,'<?= htmlspecialchars($p['title'],ENT_QUOTES) ?>','<?= htmlspecialchars(str_replace(["\r","\n"],' ',$p['description']),ENT_QUOTES) ?>','<?= htmlspecialchars($p['makers'] ?? '',ENT_QUOTES) ?>','<?= htmlspecialchars($p['link'],ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form id="del-proj-<?= $p['id'] ?>" method="POST" class="delete-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            </form>
                            <button class="btn btn-danger btn-sm"
                                onclick="confirmDelete('del-proj-<?= $p['id'] ?>', '<?= htmlspecialchars($p['title'],ENT_QUOTES) ?>')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD MODAL -->
<div class="admin-modal" id="addProjModal">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus"></i> Tambah Project Baru</h3>
            <button class="modal-close" onclick="closeModal('addProjModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="modal-body" style="display:grid;gap:1rem;">
                <div class="form-group" style="margin:0;">
                    <label>Judul Project</label>
                    <input type="text" name="title" class="form-control" placeholder="Nama project" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jelaskan project ini..."></textarea>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Kelompok Pembuat</label>
                    <input type="text" name="makers" class="form-control" placeholder="Contoh: Budi, Andi, Siti">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label>Gambar <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Link <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="url" name="link" class="form-control" placeholder="https://...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('addProjModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div class="admin-modal" id="editProjModal">
    <div class="modal-box" style="max-width:560px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Project</h3>
            <button class="modal-close" onclick="closeModal('editProjModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="editProjId">
            <div class="modal-body" style="display:grid;gap:1rem;">
                <div class="form-group" style="margin:0;">
                    <label>Judul Project</label>
                    <input type="text" name="title" id="editProjTitle" class="form-control" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Deskripsi</label>
                    <textarea name="description" id="editProjDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Kelompok Pembuat</label>
                    <input type="text" name="makers" id="editProjMakers" class="form-control" placeholder="Contoh: Budi, Andi, Siti">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label>Ganti Gambar <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label>Link</label>
                        <input type="url" name="link" id="editProjLink" class="form-control" placeholder="https://...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('editProjModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditProj(id, title, desc, makers, link) {
    document.getElementById('editProjId').value     = id;
    document.getElementById('editProjTitle').value  = title;
    document.getElementById('editProjDesc').value   = desc;
    document.getElementById('editProjMakers').value = makers;
    document.getElementById('editProjLink').value   = link;
    openModal('editProjModal');
}
</script>

<?php include 'includes/footer.php'; ?>
