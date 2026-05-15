<?php
require_once 'includes/db.php';
$page_title  = "Project - X PPLG 3";
$active_page = "projects";

$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="page-header reveal">
            <h1 class="page-title">Project <span>Kami</span></h1>
            <p class="page-subtitle">Koleksi proyek yang sedang dan telah dikerjakan oleh siswa X PPLG 3.</p>
        </div>

        <?php if (empty($projects)): ?>
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-laptop-code"></i></div>
            <h3>Belum Ada Project</h3>
            <p>Project kelas belum ditambahkan. Silakan tambah melalui panel admin.</p>
        </div>
        <?php else: ?>
        <div class="projects-grid reveal-group">
            <?php foreach ($projects as $p):
                $imgSrc  = !empty($p['image']) ? 'assets/uploads/projects/' . htmlspecialchars($p['image']) : '';
                $hasLink = !empty($p['link']);
            ?>

            <a href="project_detail.php?id=<?= $p['id'] ?>" class="project-card project-card-link">
                <div class="project-img">
                    <?php if ($imgSrc): ?>
                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="project-img-placeholder">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <?php endif; ?>
                    <div class="project-badge">Project</div>
                    <div class="project-hover-overlay">
                        <i class="fa-solid fa-eye"></i>
                        <span>Lihat Detail</span>
                    </div>
                </div>
                <div class="project-body">
                    <h3><?= htmlspecialchars($p['title']) ?></h3>
                    <p><?= mb_substr(htmlspecialchars($p['description']), 0, 100) ?>...</p>
                </div>
            </a>

            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<style>
.project-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    transition: transform 0.3s, box-shadow 0.3s;
}
.project-card-link:hover {
    transform: translateY(-6px);
}
.project-hover-overlay {
    position: absolute;
    inset: 0;
    background: rgba(37,99,235,0.75);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    opacity: 0;
    transition: opacity 0.3s;
    border-radius: inherit;
}
.project-hover-overlay i { font-size: 1.5rem; }
.project-card-link:hover .project-hover-overlay { opacity: 1; }
.project-img-placeholder {
    width: 100%; height: 200px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #93c5fd; font-size: 3rem;
}
</style>

<?php include 'includes/footer.php'; ?>
