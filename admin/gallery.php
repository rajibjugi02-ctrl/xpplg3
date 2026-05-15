<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title  = 'Kelola Gallery';
$active_menu = 'gallery';
$msg = '';

$uploadDir = '../assets/uploads/gallery/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// UPLOAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'upload') {
        $caption = trim($_POST['caption'] ?? '');
        $files   = $_FILES['photos'];
        $allowed = ['jpg','jpeg','png','webp','gif'];
        $count   = 0;
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $filename = 'gallery_' . time() . '_' . $i . '.' . $ext;
                    if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $filename)) {
                        $pdo->prepare("INSERT INTO gallery (image, caption) VALUES (?, ?)")
                            ->execute([$filename, $caption]);
                        $count++;
                    }
                }
            }
        }
        $msg = $count > 0
            ? ['type'=>'success','text'=>"$count foto berhasil diunggah!"]
            : ['type'=>'danger', 'text'=>'Gagal mengunggah foto. Pastikan format JPG/PNG/WEBP.'];
    }

    // DELETE
    if ($_POST['action'] === 'delete') {
        $id  = (int)$_POST['id'];
        $row = $pdo->prepare("SELECT image FROM gallery WHERE id=?");
        $row->execute([$id]);
        $img = $row->fetch();
        if ($img) {
            $file = $uploadDir . $img['image'];
            if (file_exists($file)) unlink($file);
            $pdo->prepare("DELETE FROM gallery WHERE id=?")->execute([$id]);
            $msg = ['type'=>'success','text'=>'Foto berhasil dihapus!'];
        }
    }

    // UPDATE CAPTION
    if ($_POST['action'] === 'update_caption') {
        $pdo->prepare("UPDATE gallery SET caption=? WHERE id=?")
            ->execute([trim($_POST['caption']), (int)$_POST['id']]);
        $msg = ['type'=>'success','text'=>'Keterangan foto diperbarui!'];
    }
}

$galleries = $pdo->query("SELECT * FROM gallery ORDER BY id DESC")->fetchAll();
include 'includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><i class="fa-solid fa-circle-info"></i> <?= $msg['text'] ?></div>
<?php endif; ?>

<!-- Header Bar -->
<div class="card">
    <div class="card-header">
        <div>
            <h3><i class="fa-regular fa-images"></i> Foto di Gallery (<?= count($galleries) ?>)</h3>
        </div>
        <button class="btn btn-primary" onclick="openModal('uploadModal')">
            <i class="fa-solid fa-cloud-arrow-up"></i> Upload Foto
        </button>
    </div>

    <div class="card-body">
        <?php if (empty($galleries)): ?>
        <div style="text-align:center;padding:4rem 2rem;color:#94a3b8;">
            <i class="fa-regular fa-image" style="font-size:3.5rem;display:block;margin-bottom:1rem;color:#cbd5e1;"></i>
            <h3 style="color:#64748b;font-size:1.15rem;margin-bottom:0.5rem;">Belum Ada Foto</h3>
            <p style="font-size:0.9rem;">Klik tombol <strong>Upload Foto</strong> di atas untuk menambahkan foto kegiatan kelas.</p>
        </div>
        <?php else: ?>
        <div class="admin-gallery-grid">
            <?php foreach ($galleries as $g): ?>
            <div class="gallery-thumb">
                <img src="../assets/uploads/gallery/<?= htmlspecialchars($g['image']) ?>"
                     alt="<?= htmlspecialchars($g['caption']) ?>" loading="lazy">
                <div class="gallery-thumb-actions">
                    <span class="gallery-thumb-caption" title="<?= htmlspecialchars($g['caption']) ?>">
                        <?= $g['caption'] ? htmlspecialchars($g['caption']) : '<em style="color:#94a3b8;">No caption</em>' ?>
                    </span>
                    <div style="display:flex;gap:0.35rem;flex-shrink:0;">
                        <button class="btn btn-outline btn-sm"
                                onclick="openEditCaption(<?= $g['id'] ?>, '<?= htmlspecialchars($g['caption'],ENT_QUOTES) ?>')">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <form id="del-gal-<?= $g['id'] ?>" method="POST" class="delete-form">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $g['id'] ?>">
                        </form>
                        <button class="btn btn-danger btn-sm"
                                onclick="confirmDelete('del-gal-<?= $g['id'] ?>', 'foto ini')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== UPLOAD MODAL ===== -->
<div class="admin-modal" id="uploadModal">
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-cloud-arrow-up"></i> Upload Foto Gallery</h3>
            <button class="modal-close" onclick="closeModal('uploadModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="action" value="upload">
            <div class="modal-body" style="display:flex;flex-direction:column;gap:1.25rem;">

                <!-- Drop Zone -->
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('photoInput').click()">
                    <div class="drop-zone-inner" id="dropZoneInner">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size:2rem;color:#94a3b8;margin-bottom:0.75rem;display:block;"></i>
                        <p style="font-weight:600;color:#475569;margin-bottom:0.25rem;">Klik atau seret foto ke sini</p>
                        <p style="font-size:0.8rem;color:#94a3b8;">JPG, PNG, WEBP, GIF &bull; Bisa lebih dari satu foto</p>
                    </div>
                    <input type="file" name="photos[]" id="photoInput" multiple accept="image/*"
                           style="display:none;" onchange="handleFiles(this.files)">
                </div>

                <!-- Preview Grid -->
                <div id="previewGrid" style="display:none;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.6rem;">
                        <span id="previewCount" style="font-size:0.825rem;font-weight:600;color:#475569;"></span>
                        <button type="button" style="font-size:0.78rem;color:#ef4444;background:none;border:none;cursor:pointer;font-weight:600;"
                                onclick="clearFiles()"><i class="fa-solid fa-xmark"></i> Hapus Semua</button>
                    </div>
                    <div id="previewContainer" style="display:flex;flex-wrap:wrap;gap:0.5rem;"></div>
                </div>

                <div class="form-group" style="margin:0;">
                    <label>Keterangan <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                    <input type="text" name="caption" class="form-control" placeholder="Contoh: Upacara 17 Agustus, Praktikum, dll.">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('uploadModal')">Batal</button>
                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                    <i class="fa-solid fa-cloud-arrow-up"></i> Upload Foto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== EDIT CAPTION MODAL ===== -->
<div id="captionModal" class="admin-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Edit Keterangan Foto</h3>
            <button class="modal-close" onclick="closeModal('captionModal')"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_caption">
            <input type="hidden" name="id" id="captionId">
            <div class="modal-body">
                <div class="form-group" style="margin:0;">
                    <label>Keterangan Foto</label>
                    <input type="text" name="caption" id="captionText" class="form-control" placeholder="Nama kegiatan/acara">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('captionModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
.drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 0.875rem;
    padding: 2rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s;
    background: #f8fafc;
}
.drop-zone:hover, .drop-zone.dragover {
    border-color: var(--primary);
    background: #eff6ff;
}
.drop-zone.dragover .drop-zone-inner i { color: var(--primary); }
.preview-img-wrap {
    position: relative;
    width: 76px;
    height: 76px;
    border-radius: 0.5rem;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.preview-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
</style>

<script>
let selectedFiles = new DataTransfer();

function handleFiles(files) {
    Array.from(files).forEach(file => selectedFiles.items.add(file));
    document.getElementById('photoInput').files = selectedFiles.files;
    renderPreviews();
}

function renderPreviews() {
    const grid    = document.getElementById('previewGrid');
    const count   = document.getElementById('previewCount');
    const container = document.getElementById('previewContainer');
    const btn     = document.getElementById('uploadBtn');
    const total   = selectedFiles.files.length;

    if (total === 0) {
        grid.style.display = 'none';
        btn.disabled = true;
        return;
    }

    grid.style.display = 'block';
    count.textContent  = total + ' foto dipilih';
    btn.disabled       = false;
    container.innerHTML = '';

    Array.from(selectedFiles.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div');
            wrap.className = 'preview-img-wrap';
            wrap.innerHTML = `<img src="${e.target.result}" alt="${file.name}">`;
            container.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}

function clearFiles() {
    selectedFiles = new DataTransfer();
    document.getElementById('photoInput').files = selectedFiles.files;
    renderPreviews();
}

// Drag & Drop
const dropZone = document.getElementById('dropZone');
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

function openEditCaption(id, caption) {
    document.getElementById('captionId').value   = id;
    document.getElementById('captionText').value = caption;
    openModal('captionModal');
}

// Reset upload modal on close
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        clearFiles();
        closeModal('uploadModal');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
