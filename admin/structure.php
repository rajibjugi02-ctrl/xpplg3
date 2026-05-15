<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title  = 'Kelola Struktur';
$active_menu = 'structure';
$msg = '';

// Ensure upload folder exists
$uploadDir = '../assets/uploads/structure/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $photo_name = null;

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $ext   = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed)) {
            $photo_name = 'struct_' . $id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $photo_name);
        } else {
            $msg = ['type'=>'danger','text'=>'Format file tidak didukung. Gunakan JPG, PNG, atau WEBP.'];
        }
    }

    if (!$msg) {
        if ($photo_name) {
            $pdo->prepare("UPDATE structure SET name=?, photo=? WHERE id=?")->execute([$name, $photo_name, $id]);
        } else {
            $pdo->prepare("UPDATE structure SET name=? WHERE id=?")->execute([$name, $id]);
        }
        $msg = ['type'=>'success','text'=>'Struktur berhasil diperbarui!'];
    }
}

$members = $pdo->query("SELECT * FROM structure ORDER BY order_num ASC")->fetchAll();
include 'includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><i class="fa-solid fa-circle-info"></i> <?= $msg['text'] ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Edit Anggota Struktur Organisasi</h3>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.25rem;">
            <?php foreach ($members as $m): 
                $photo = !empty($m['photo']) ? '../assets/uploads/structure/' . $m['photo']
                       : 'https://ui-avatars.com/api/?name=' . urlencode($m['name']) . '&background=e2e8f0&color=475569&size=120&bold=true';
            ?>
            <div class="struct-edit-card">
                <div class="struct-photo-wrap">
                    <img src="<?= $photo ?>" alt="<?= htmlspecialchars($m['name']) ?>" id="preview-<?= $m['id'] ?>">
                    <span class="struct-role-badge"><?= htmlspecialchars($m['role']) ?></span>
                </div>
                <form method="POST" enctype="multipart/form-data" class="struct-form">
                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($m['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Foto <span style="color:#94a3b8;font-weight:400;">(jpg/png/webp)</span></label>
                        <input type="file" name="photo" class="form-control" accept="image/*"
                               onchange="previewImg(this, 'preview-<?= $m['id'] ?>')">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%">
                        <i class="fa-solid fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.struct-edit-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:0.85rem; padding:1.25rem; }
.struct-photo-wrap { text-align:center; margin-bottom:1rem; position:relative; display:inline-block; width:100%; }
.struct-photo-wrap img { width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #e2e8f0; }
.struct-role-badge { display:block; margin-top:0.5rem; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:var(--primary); }
</style>

<script>
function previewImg(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById(previewId).src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include 'includes/footer.php'; ?>
