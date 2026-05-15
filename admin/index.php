<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title  = 'Dashboard';
$active_menu = 'dashboard';

$total_students  = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_gallery   = $pdo->query("SELECT COUNT(*) FROM gallery")->fetchColumn();
$total_projects  = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$total_structure = $pdo->query("SELECT COUNT(*) FROM structure")->fetchColumn();

include 'includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
        <div>
            <div class="stat-number"><?= $total_students ?></div>
            <div class="stat-label">Total Siswa</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-images"></i></div>
        <div>
            <div class="stat-number"><?= $total_gallery ?></div>
            <div class="stat-label">Foto Gallery</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-laptop-code"></i></div>
        <div>
            <div class="stat-number"><?= $total_projects ?></div>
            <div class="stat-label">Total Project</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-sitemap"></i></div>
        <div>
            <div class="stat-number"><?= $total_structure ?></div>
            <div class="stat-label">Anggota Struktur</div>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div class="card">
    <div class="card-header">
        <h3><i class="fa-solid fa-bolt"></i> Akses Cepat</h3>
    </div>
    <div class="card-body" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap:1rem;">
        <a href="structure.php" class="quick-btn">
            <i class="fa-solid fa-sitemap"></i> Kelola Struktur
        </a>
        <a href="students.php" class="quick-btn">
            <i class="fa-solid fa-users"></i> Kelola Siswa
        </a>
        <a href="gallery.php" class="quick-btn">
            <i class="fa-solid fa-images"></i> Kelola Gallery
        </a>
        <a href="projects.php" class="quick-btn">
            <i class="fa-solid fa-laptop-code"></i> Kelola Project
        </a>
        <a href="contact.php" class="quick-btn">
            <i class="fa-solid fa-address-book"></i> Kelola Contact
        </a>
        <a href="../index.php" target="_blank" class="quick-btn">
            <i class="fa-solid fa-eye"></i> Lihat Website
        </a>
    </div>
</div>

<style>
.quick-btn {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1.25rem; border-radius: 0.75rem;
    background: #f8fafc; border: 1px solid #e2e8f0;
    color: #334155; font-weight: 600; font-size: 0.9rem;
    text-decoration: none; transition: all 0.2s;
}
.quick-btn i { color: var(--primary); font-size: 1.1rem; }
.quick-btn:hover { background: #eff6ff; border-color: #93c5fd; transform: translateY(-2px); }
</style>

<?php include 'includes/footer.php'; ?>
