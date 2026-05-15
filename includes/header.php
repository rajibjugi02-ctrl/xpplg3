<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($page_title)) $page_title = 'PPLG 3 Engineering';
if (!isset($active_page)) $active_page = '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&family=Fira+Code:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/animations.css">
    <?php if ($active_page == 'home'):      ?><link rel="stylesheet" href="assets/css/home.css"><?php endif; ?>
    <?php if ($active_page == 'structure'): ?><link rel="stylesheet" href="assets/css/structure.css"><?php endif; ?>
    <?php if ($active_page == 'students'):  ?><link rel="stylesheet" href="assets/css/students.css"><?php endif; ?>
    <?php if ($active_page == 'gallery'):   ?><link rel="stylesheet" href="assets/css/gallery.css"><?php endif; ?>
    <?php if ($active_page == 'projects'):  ?><link rel="stylesheet" href="assets/css/projects.css"><?php endif; ?>
    <?php if ($active_page == 'contact'):   ?><link rel="stylesheet" href="assets/css/contact.css"><?php endif; ?>
</head>
<body>

<?php
// Fetch logo
$globalLogo = '';
if (isset($pdo)) {
    try {
        $globalLogo = $pdo->query("SELECT logo FROM contact LIMIT 1")->fetchColumn();
    } catch(Exception $e) {}
}
?>
<header class="navbar">
    <a href="index.php" class="logo">
        <?php if (!empty($globalLogo)): ?>
            <img src="assets/uploads/logo/<?= htmlspecialchars($globalLogo) ?>" alt="Logo" style="height:32px; width:auto; object-fit:contain; border-radius:4px;">
            <span style="display:none;">PPLG 3 Engineering</span>
        <?php else: ?>
            <i class="fa-solid fa-code"></i> PPLG 3 Engineering
        <?php endif; ?>
    </a>
    <nav class="nav-links">
        <a href="index.php"    class="<?= ($active_page=='home')      ? 'active':'' ?>">Beranda</a>
        <a href="structure.php" class="<?= ($active_page=='structure') ? 'active':'' ?>">Struktur</a>
        <a href="students.php"  class="<?= ($active_page=='students')  ? 'active':'' ?>">Siswa</a>
        <a href="gallery.php"   class="<?= ($active_page=='gallery')   ? 'active':'' ?>">Gallery</a>
        <a href="projects.php"  class="<?= ($active_page=='projects')  ? 'active':'' ?>">Project</a>
        <a href="contact.php"   class="<?= ($active_page=='contact')   ? 'active':'' ?>">Contact</a>
    </nav>
    <div class="navbar-right">
        <?php if (isset($_SESSION['visitor_logged_in']) && $_SESSION['visitor_logged_in'] === true): ?>
            <a href="logout.php" class="btn btn-primary" style="background-color: #ef4444;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout (<?= htmlspecialchars($_SESSION['visitor_name']) ?>)
            </a>
        <?php elseif (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true): ?>
            <a href="siswa/dashboard.php" class="btn btn-primary">
                <i class="fa-solid fa-user"></i> Profil Siswa
            </a>
        <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <a href="admin/index.php" class="btn btn-primary" style="background: var(--dark);">
                <i class="fa-solid fa-gauge"></i> Admin Panel
            </a>
            <a href="admin/logout.php" class="btn btn-outline" style="border-color: #ef4444; color: #ef4444;">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        <?php else: ?>
            <div class="dropdown" style="position: relative; display: inline-block;">
                <button class="btn btn-primary dropdown-toggle" style="cursor: pointer;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login <i class="fa-solid fa-chevron-down" style="font-size: 0.8em; margin-left: 4px;"></i>
                </button>
                <div class="dropdown-menu" style="display: none; position: absolute; top: 100%; right: 0; background: var(--card-bg); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.5rem; min-width: 160px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 100; margin-top: 0.5rem;">
                    <a href="siswa/login.php" style="display: block; padding: 0.5rem 1rem; color: var(--text-main); text-decoration: none; border-radius: 0.25rem; transition: 0.2s;"><i class="fa-solid fa-user-graduate" style="width: 20px;"></i> Portal Siswa</a>
                    <a href="pengunjung_login.php" style="display: block; padding: 0.5rem 1rem; color: var(--text-main); text-decoration: none; border-radius: 0.25rem; transition: 0.2s;"><i class="fa-solid fa-users-viewfinder" style="width: 20px;"></i> Pengunjung</a>
                    <hr style="border: none; border-top: 1px solid var(--border); margin: 0.25rem 0;">
                    <a href="admin/login.php" style="display: block; padding: 0.5rem 1rem; color: var(--text-main); text-decoration: none; border-radius: 0.25rem; transition: 0.2s;"><i class="fa-solid fa-lock" style="width: 20px;"></i> Admin</a>
                </div>
            </div>
            <script>
                document.querySelector('.dropdown-toggle').addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                });
                document.addEventListener('click', function() {
                    const menu = document.querySelector('.dropdown-menu');
                    if (menu) menu.style.display = 'none';
                });
            </script>
        <?php endif; ?>
        
        <button class="nav-hamburger" id="navHamburger" aria-label="Buka menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<!-- Mobile Drawer -->
<nav class="nav-drawer" id="navDrawer">
    <a href="index.php"    class="<?= ($active_page=='home')      ? 'active':'' ?>"><i class="fa-solid fa-house"       style="width:20px;margin-right:4px;"></i> Beranda</a>
    <a href="structure.php" class="<?= ($active_page=='structure') ? 'active':'' ?>"><i class="fa-solid fa-sitemap"    style="width:20px;margin-right:4px;"></i> Struktur</a>
    <a href="students.php"  class="<?= ($active_page=='students')  ? 'active':'' ?>"><i class="fa-solid fa-users"      style="width:20px;margin-right:4px;"></i> Siswa</a>
    <a href="gallery.php"   class="<?= ($active_page=='gallery')   ? 'active':'' ?>"><i class="fa-regular fa-images"   style="width:20px;margin-right:4px;"></i> Gallery</a>
    <a href="projects.php"  class="<?= ($active_page=='projects')  ? 'active':'' ?>"><i class="fa-solid fa-laptop-code" style="width:20px;margin-right:4px;"></i> Project</a>
    <a href="contact.php"   class="<?= ($active_page=='contact')   ? 'active':'' ?>"><i class="fa-solid fa-envelope"   style="width:20px;margin-right:4px;"></i> Contact</a>
    
    <div style="margin-top: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
        <?php if (isset($_SESSION['visitor_logged_in']) && $_SESSION['visitor_logged_in'] === true): ?>
            <a href="logout.php" class="btn btn-primary" style="margin-top:0.25rem;width:100%;justify-content:center;background-color: #ef4444;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout (<?= htmlspecialchars($_SESSION['visitor_name']) ?>)
            </a>
        <?php elseif (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true): ?>
            <a href="siswa/dashboard.php" class="btn btn-primary" style="margin-top:0.25rem;width:100%;justify-content:center;">
                <i class="fa-solid fa-user"></i> Profil Siswa
            </a>
        <?php elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
            <a href="admin/index.php" class="btn btn-primary" style="margin-top:0.25rem;width:100%;justify-content:center;background: var(--dark);">
                <i class="fa-solid fa-gauge"></i> Admin Panel
            </a>
            <a href="admin/logout.php" class="btn btn-outline" style="margin-top:0.5rem;width:100%;justify-content:center;border-color: #ef4444; color: #ef4444;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout Admin
            </a>
        <?php else: ?>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.5rem;">Login sebagai:</p>
            <a href="siswa/login.php" class="btn" style="margin-top:0.25rem;width:100%;justify-content:center; background: rgba(255,255,255,0.1); color: var(--text-main);">
                <i class="fa-solid fa-user-graduate"></i> Portal Siswa
            </a>
            <a href="pengunjung_login.php" class="btn" style="margin-top:0.5rem;width:100%;justify-content:center; background: rgba(255,255,255,0.1); color: var(--text-main);">
                <i class="fa-solid fa-users-viewfinder"></i> Portal Pengunjung
            </a>
            <a href="admin/login.php" class="btn btn-primary" style="margin-top:0.5rem;width:100%;justify-content:center;">
                <i class="fa-solid fa-lock"></i> Portal Admin
            </a>
        <?php endif; ?>
    </div>
</nav>

