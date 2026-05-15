<?php
require_once 'includes/db.php';
$page_title  = "Beranda - X PPLG 3";
$active_page = "home";

try {
    $total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
} catch (Exception $e) {
    $total_students = 45;
    $total_projects = 0;
}

include 'includes/header.php';
?>

<main>
    <!-- HERO -->
    <section class="hero">
        <!-- Particle Canvas -->
        <canvas id="hero-canvas"></canvas>

        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge-dot"></span>
                <code>&gt; system.status == 'ONLINE'</code>
            </div>
            <h1 class="hero-title">
                X PPLG 3<br>
                <span class="hero-title-accent">Engineering Hub</span>
            </h1>
            <p class="hero-desc">
                Selamat datang di portal resmi kelas X PPLG 3. Tempat di mana logika bertemu kreativitas &mdash; membangun generasi rekayasa perangkat lunak melalui kolaborasi dan inovasi nyata.
            </p>
            <div class="hero-actions">
                <a href="projects.php" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-rocket"></i> Lihat Project
                </a>
                <a href="students.php" class="btn btn-outline btn-lg">
                    <i class="fa-solid fa-users"></i> Direktori Siswa
                </a>
            </div>
        </div>

        <div class="hero-graphic">
            <div class="hero-circle">
                <div class="hero-logo-inner">
                    <div class="hero-icon-box">
                        <i class="fa-solid fa-code"></i>
                    </div>
                    <span class="hero-logo-text">X PPLG 3</span>
                </div>
                <!-- Floating badges -->
                <div class="float-badge float-1"><i class="fa-brands fa-html5"></i></div>
                <div class="float-badge float-2"><i class="fa-brands fa-php"></i></div>
                <div class="float-badge float-3"><i class="fa-brands fa-js"></i></div>
                <div class="float-badge float-4"><i class="fa-solid fa-database"></i></div>
            </div>
        </div>
    </section>

    <!-- STATS -->
    <section class="stats-section">
        <div class="stat-item">
            <span class="stat-num stat-blue"
                  data-target="<?= $total_students ?>">0</span>
            <span class="stat-lbl">Siswa Aktif</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num stat-sky"
                  data-target="2">0</span>
            <span class="stat-lbl">Semester</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num stat-dark"
                  data-target="<?= $total_projects > 0 ? $total_projects : 15 ?>"
                  data-suffix="<?= $total_projects > 0 ? '' : '+' ?>">0</span>
            <span class="stat-lbl">Projects</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat-item">
            <span class="stat-num stat-purple"
                  data-target="100" data-suffix="%">0</span>
            <span class="stat-lbl">Semangat</span>
        </div>
    </section>

    <!-- FEATURES -->
    <section class="features-section">
        <div class="features-header reveal">
            <div class="section-chip"><i class="fa-solid fa-sparkles"></i> Core Values</div>
            <h2>Core Parameters</h2>
            <p>Prinsip yang menggerakkan siklus pengembangan kami.</p>
        </div>
        <div class="features-grid reveal-group">
            <div class="feature-card reveal">
                <div class="feature-icon icon-blue"><i class="fa-solid fa-microchip"></i></div>
                <h3>Technology</h3>
                <p>Memanfaatkan stack modern untuk membangun solusi software yang skalabel dan efisien.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon icon-purple"><i class="fa-regular fa-lightbulb"></i></div>
                <h3>Innovation</h3>
                <p>Berpikir di luar konsol &mdash; memecahkan masalah kompleks dengan algoritma kreatif.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon icon-orange"><i class="fa-solid fa-users-gear"></i></div>
                <h3>Collaboration</h3>
                <p>Kerja tim yang solid dan peer programming untuk mengangkat kualitas codebase bersama.</p>
            </div>
        </div>
    </section>

    <!-- QUICK LINKS -->
    <section class="quicklinks-section reveal-group">
        <a href="structure.php" class="ql-card reveal">
            <i class="fa-solid fa-sitemap"></i>
            <span>Struktur Organisasi</span>
            <i class="fa-solid fa-arrow-right ql-arrow"></i>
        </a>
        <a href="gallery.php" class="ql-card reveal">
            <i class="fa-regular fa-images"></i>
            <span>Gallery Kegiatan</span>
            <i class="fa-solid fa-arrow-right ql-arrow"></i>
        </a>
        <a href="contact.php" class="ql-card reveal">
            <i class="fa-solid fa-envelope"></i>
            <span>Hubungi Kami</span>
            <i class="fa-solid fa-arrow-right ql-arrow"></i>
        </a>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
