<?php
if (!isset($page_title)) $page_title = 'Admin - PPLG 3';
if (!isset($active_menu)) $active_menu = '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* SweetAlert2 custom theme */
        .swal2-popup { border-radius: 1rem !important; font-family: 'Inter', sans-serif !important; }
        .swal2-title { font-weight: 700 !important; }
        .swal2-confirm { border-radius: 0.5rem !important; font-weight: 600 !important; }
        .swal2-cancel  { border-radius: 0.5rem !important; font-weight: 600 !important; }
    </style>
</head>
<body>
<?php
// Fetch logo
$adminGlobalLogo = '';
if (isset($pdo)) {
    try {
        $adminGlobalLogo = $pdo->query("SELECT logo FROM contact LIMIT 1")->fetchColumn();
    } catch(Exception $e) {}
}
?>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <?php if (!empty($adminGlobalLogo)): ?>
                    <img src="../assets/uploads/logo/<?= htmlspecialchars($adminGlobalLogo) ?>" alt="Logo" style="height:32px; width:auto; object-fit:contain; border-radius:4px;">
                    <div>
                        <span class="sidebar-title" style="display:none;">PPLG 3</span>
                        <span class="sidebar-sub">Admin Panel</span>
                    </div>
                <?php else: ?>
                    <i class="fa-solid fa-code"></i>
                    <div>
                        <span class="sidebar-title">PPLG 3</span>
                        <span class="sidebar-sub">Admin Panel</span>
                    </div>
                <?php endif; ?>
            </div>
            <button class="sidebar-close" id="sidebarClose"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <nav class="sidebar-nav">
            <p class="nav-label">Main</p>
            <a href="index.php" class="nav-item <?= $active_menu === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
            </a>
            <p class="nav-label">Content</p>
            <a href="structure.php" class="nav-item <?= $active_menu === 'structure' ? 'active' : '' ?>">
                <i class="fa-solid fa-sitemap"></i> <span>Struktur</span>
            </a>
            <a href="students.php" class="nav-item <?= $active_menu === 'students' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i> <span>Siswa</span>
            </a>
            <a href="gallery.php" class="nav-item <?= $active_menu === 'gallery' ? 'active' : '' ?>">
                <i class="fa-solid fa-images"></i> <span>Gallery</span>
            </a>
            <a href="projects.php" class="nav-item <?= $active_menu === 'projects' ? 'active' : '' ?>">
                <i class="fa-solid fa-laptop-code"></i> <span>Project</span>
            </a>
            <a href="comments.php" class="nav-item <?= $active_menu === 'comments' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> <span>Komentar</span>
            </a>
            <p class="nav-label">Settings</p>
            <a href="contact.php" class="nav-item <?= $active_menu === 'contact' ? 'active' : '' ?>">
                <i class="fa-solid fa-address-book"></i> <span>Contact</span>
            </a>
            <a href="activity_log.php" class="nav-item <?= $active_menu === 'activity_log' ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> <span>Activity Log</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../index.php" target="_blank" class="nav-item">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> <span>Lihat Website</span>
            </a>
            <a href="logout.php" class="nav-item nav-logout">
                <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h2 class="topbar-title"><?= $page_title ?></h2>
            </div>
            <div class="topbar-right">
                <div class="admin-user">
                    <div class="admin-avatar"><i class="fa-solid fa-user-shield"></i></div>
                    <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                </div>
            </div>
        </header>
        <!-- Page Content starts after this -->
        <main class="admin-content">
