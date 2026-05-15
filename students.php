<?php
require_once 'includes/db.php';
$page_title = "Daftar Siswa - X PPLG 3";
$active_page = "students";

// Fetch from DB sorted alphabetically; fallback to JSON
try {
    $students = $pdo->query("SELECT * FROM students ORDER BY name ASC")->fetchAll();
} catch (Exception $e) {
    $students = [];
}
if (empty($students)) {
    $json = file_get_contents('data/students.json');
    $raw  = json_decode($json, true) ?? [];
    usort($raw, fn($a, $b) => strcmp($a['name'], $b['name']));
    $students = $raw;
}

// No more A-Z grouping, just show a clean grid

include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="page-header reveal">
            <h1 class="page-title">Daftar Siswa <span>X PPLG 3</span></h1>
            <p class="page-subtitle">
                <?= count($students) ?> siswa aktif &bull; Bidang Pengembangan Perangkat Lunak &amp; GIM
            </p>
        </div>

        <!-- Search & Info Bar -->
        <div class="students-toolbar reveal">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Cari nama siswa..."
                    autocomplete="off"
                >
                <span id="searchCount" class="search-count" style="display:none;"></span>
            </div>
        </div>

        <!-- No results message -->
        <div id="noResults" style="display:none;" class="no-results-msg">
            <i class="fa-solid fa-magnifying-glass"></i>
            <p>Tidak ada siswa yang cocok dengan pencarian.</p>
        </div>

        <!-- Students Grid -->
        <div class="students-grid reveal-group" id="studentsGrid">
            <?php $no = 1; foreach ($students as $s): 
                $photoUrl = !empty($s['photo']) ? 'assets/uploads/students/' . htmlspecialchars($s['photo']) : '';
            ?>
            <div class="student-card" 
                 data-name="<?= strtolower(htmlspecialchars($s['name'])) ?>"
                 data-displayname="<?= htmlspecialchars($s['name']) ?>"
                 data-nisn="<?= htmlspecialchars($s['nisn'] ?? $s['id']) ?>"
                 data-kelas="<?= htmlspecialchars($s['kelas'] ?? 'X PPLG 3') ?>"
                 data-photo="<?= htmlspecialchars($photoUrl) ?>"
                 data-portfolio="<?= htmlspecialchars($s['portfolio_link'] ?? '') ?>"
                 data-github="<?= htmlspecialchars($s['github_link'] ?? '') ?>"
                 onclick="openStudentModal(this)">
                 
                <div class="student-card-header">
                    <span class="student-id-badge">ID: <?= htmlspecialchars($s['id']) ?></span>
                </div>
                <div class="student-avatar-lg">
                    <?php if (!empty($photoUrl)): ?>
                    <img src="<?= $photoUrl ?>" alt="<?= htmlspecialchars($s['name']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="avatar-placeholder"><?= strtoupper(substr($s['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                </div>
                <div class="student-info">
                    <h3 class="student-name"><?= htmlspecialchars($s['name']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem;">Klik untuk detail</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<!-- Student Detail Modal -->
<div id="studentModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" onclick="closeStudentModal()"><i class="fa-solid fa-times"></i></button>
        <div class="modal-header-bg"></div>
        <div class="modal-body">
            <div class="modal-avatar" id="modalAvatar"></div>
            <h2 id="modalName" class="modal-name">Nama Siswa</h2>
            <p id="modalKelas" class="modal-kelas">Kelas</p>
            
            <div class="modal-details">
                <div class="detail-item">
                    <i class="fa-solid fa-id-card"></i>
                    <div>
                        <span>NISN</span>
                        <strong id="modalNisn">-</strong>
                    </div>
                </div>
            </div>

            <div class="modal-links" id="modalLinks">
                <!-- Links will be injected here -->
            </div>
        </div>
    </div>
</div>

<style>
/* Modal Styles for Student Details */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);
    display: none; align-items: center; justify-content: center;
    z-index: 1000; opacity: 0; transition: opacity 0.3s ease;
}
.modal-overlay.active { display: flex; opacity: 1; }
.modal-content {
    background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px);
    width: 90%; max-width: 450px; border-radius: 1.5rem; 
    border: 1px solid rgba(255, 255, 255, 0.5);
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
    position: relative; overflow-y: auto; overflow-x: hidden; max-height: 90vh; transform: scale(0.9);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.modal-overlay.active .modal-content { transform: scale(1); }
.modal-close {
    position: absolute; top: 1rem; right: 1rem; width: 32px; height: 32px;
    background: rgba(255,255,255,0.3); color: #1e293b; border: none; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 10; transition: 0.2s; backdrop-filter: blur(5px);
}
.modal-close:hover { background: #ef4444; color: #fff; }
.modal-header-bg { height: 120px; background: linear-gradient(135deg, #6366f1, #a855f7, #ec4899); }
.modal-body { padding: 0 2rem 2rem; text-align: center; margin-top: -50px; }
.modal-avatar {
    width: 100px; height: 100px; border-radius: 50%; background: #f8fafc;
    margin: 0 auto 1rem; border: 4px solid rgba(255,255,255,0.9);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; font-size: 2.5rem; font-weight: bold; color: #94a3b8;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.modal-avatar img { width: 100%; height: 100%; object-fit: cover; }
.modal-name { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem; }
.modal-kelas { color: #64748b; font-size: 0.95rem; font-weight: 500; margin-bottom: 1.5rem; }

.modal-details {
    background: rgba(0, 0, 0, 0.03); border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 1rem; padding: 1rem; margin-bottom: 1.5rem;
    display: flex; flex-direction: column; gap: 0.75rem; text-align: left;
}
.detail-item { display: flex; align-items: center; gap: 1rem; }
.detail-item i { width: 36px; height: 36px; background: #fff; color: #6366f1; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
.detail-item div { display: flex; flex-direction: column; }
.detail-item span { font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.detail-item strong { color: #0f172a; font-size: 0.95rem; font-weight: 700; }

.modal-links { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; }
.social-link {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem;
    border-radius: 2rem; font-size: 0.875rem; font-weight: 600; text-decoration: none;
    transition: 0.2s; flex: 1; min-width: 140px; justify-content: center;
}
.social-link.github { background: #1e293b; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.social-link.github:hover { background: #0f172a; transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }
.social-link.portfolio { background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; box-shadow: 0 4px 6px rgba(99, 102, 241, 0.2); }
.social-link.portfolio:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(99, 102, 241, 0.3); }

/* Make card clickable */
.student-card { cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
.student-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.3); border-color: var(--primary); }

</style>

<script>
const searchInput = document.getElementById('searchInput');
const grid        = document.getElementById('studentsGrid');
const noResults   = document.getElementById('noResults');
const searchCount = document.getElementById('searchCount');
const cards       = document.querySelectorAll('.student-card');
const modal       = document.getElementById('studentModal');

// Search Logic
searchInput.addEventListener('input', function () {
    const q     = this.value.trim().toLowerCase();
    let visible = 0;

    cards.forEach(card => {
        const name = card.dataset.name;
        card.style.display = (!q || name.includes(q)) ? '' : 'none';
        if (!q || name.includes(q)) visible++;
    });

    if (q) {
        searchCount.textContent = visible + ' ditemukan';
        searchCount.style.display = 'inline';
    } else {
        searchCount.style.display = 'none';
    }

    noResults.style.display = visible === 0 ? 'flex' : 'none';
});

// Modal Logic
function openStudentModal(card) {
    const name = card.dataset.displayname;
    const nisn = card.dataset.nisn;
    const kelas = card.dataset.kelas;
    const photo = card.dataset.photo;
    const portfolio = card.dataset.portfolio;
    const github = card.dataset.github;

    document.getElementById('modalName').textContent = name;
    document.getElementById('modalKelas').textContent = kelas;
    document.getElementById('modalNisn').textContent = nisn || '-';

    const avatarContainer = document.getElementById('modalAvatar');
    if (photo) {
        avatarContainer.innerHTML = `<img src="${photo}" alt="${name}">`;
    } else {
        avatarContainer.innerHTML = name.charAt(0).toUpperCase();
    }

    const linksContainer = document.getElementById('modalLinks');
    let linksHtml = '';
    if (github) {
        linksHtml += `<a href="${github}" target="_blank" class="social-link github"><i class="fa-brands fa-github"></i> GitHub</a>`;
    }
    if (portfolio) {
        linksHtml += `<a href="${portfolio}" target="_blank" class="social-link portfolio"><i class="fa-solid fa-globe"></i> Portofolio</a>`;
    }
    
    if (!github && !portfolio) {
        linksHtml = `<p style="color: var(--text-muted); font-size: 0.85rem; width: 100%;">Belum menambahkan link portofolio/github.</p>`;
    }
    
    linksContainer.innerHTML = linksHtml;

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeStudentModal() {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Close on click outside
modal.addEventListener('click', function(e) {
    if (e.target === modal) {
        closeStudentModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>
