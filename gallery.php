<?php
require_once 'includes/db.php';
$page_title = "Gallery - X PPLG 3";
$active_page = "gallery";

$galleries = $pdo->query("SELECT * FROM gallery ORDER BY id DESC")->fetchAll();
include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="page-header reveal">
            <h1 class="page-title">Gallery <span>Kelas</span></h1>
            <p class="page-subtitle">Momen dan kegiatan seru bersama X PPLG 3 &bull; Setiap acara, setiap kenangan.</p>
        </div>

        <?php if (empty($galleries)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-regular fa-image"></i></div>
            <h3>Belum Ada Foto</h3>
            <p>Gallery kelas masih kosong. Foto kegiatan akan segera ditambahkan oleh admin.</p>
        </div>
        <?php else: ?>
        <div class="gallery-grid reveal-group">
            <?php foreach ($galleries as $img): ?>
            <div class="gallery-item">
                <img src="assets/uploads/gallery/<?= htmlspecialchars($img['image']) ?>"
                     alt="<?= htmlspecialchars($img['caption']) ?>"
                     loading="lazy">
                <?php if (!empty($img['caption'])): ?>
                <div class="gallery-overlay">
                    <p><?= htmlspecialchars($img['caption']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
