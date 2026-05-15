<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/logger.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: index.php");
    exit;
}

// Log visit if it's not admin
if (!isset($_SESSION['admin_logged_in'])) {
    $visitorName = 'Unknown';
    $role = 'visitor';
    if (isset($_SESSION['student_logged_in'])) {
        $visitorName = $_SESSION['student_name'];
        $role = 'student';
    } elseif (isset($_SESSION['visitor_logged_in'])) {
        $visitorName = $_SESSION['visitor_name'];
    }
    logActivity($pdo, $role, $visitorName, "Membuka detail project: " . $project['title']);
}

$page_title = htmlspecialchars($project['title']) . " - PPLG 3";
include 'includes/header.php';

// Determine default username for comments
$comment_username = '';
if (isset($_SESSION['student_logged_in'])) {
    $comment_username = $_SESSION['student_name'];
} elseif (isset($_SESSION['visitor_logged_in'])) {
    $comment_username = $_SESSION['visitor_name'];
} elseif (isset($_SESSION['admin_logged_in'])) {
    $comment_username = 'Admin PPLG 3';
} else {
    // Not logged in
    $comment_username = '';
}
?>

<style>
    /* Premium Light Theme Redesign */
    .project-container {
        max-width: 900px;
        margin: 2rem auto 4rem;
        padding: 0 5%;
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Hero Image */
    .project-cover {
        width: 100%;
        max-height: 500px;
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        background: linear-gradient(135deg, var(--primary-light), #e0e7ff);
        border: 1px solid var(--border);
        animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) backwards;
    }
    .project-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .project-cover:hover img {
        transform: scale(1.05);
    }
    
    /* Main Content Card */
    .project-main-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 3rem;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        margin-bottom: 3rem;
        animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.15s backwards;
        transition: box-shadow 0.4s ease;
    }
    .project-main-card:hover {
        box-shadow: var(--shadow-lg);
    }

    @media (max-width: 768px) {
        .project-main-card { padding: 2rem 1.5rem; }
    }

    .project-header-info {
        text-align: center;
        margin-bottom: 2.5rem;
        padding-bottom: 2.5rem;
        border-bottom: 1px solid var(--border-light);
    }

    .project-header-info h1 {
        font-size: 2.5rem;
        font-weight: 900;
        color: var(--dark);
        margin-bottom: 1rem;
        line-height: 1.2;
        letter-spacing: -0.03em;
    }
    .project-makers {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-light);
        color: var(--primary);
        padding: 0.5rem 1.25rem;
        border-radius: 2rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: transform 0.3s ease;
    }
    .project-makers:hover {
        transform: translateY(-2px);
    }

    .project-desc {
        color: var(--text);
        line-height: 1.8;
        font-size: 1.1rem;
        margin-bottom: 2.5rem;
    }
    
    /* Action Section */
    .project-action {
        background: var(--light-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 2.5rem;
        text-align: center;
        transition: background 0.4s ease;
    }
    .project-action:hover {
        background: var(--white);
        border-color: var(--primary-light);
    }
    .project-action i.icon-rocket {
        font-size: 2.5rem;
        color: var(--primary);
        margin-bottom: 1rem;
        display: inline-block;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .project-action:hover i.icon-rocket {
        transform: translateY(-5px) rotate(10deg);
    }
    .project-action h3 {
        color: var(--dark);
        margin-bottom: 1.5rem;
        font-size: 1.25rem;
        font-weight: 800;
    }
    .btn-visit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem 2rem;
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: #fff;
        border-radius: 2rem;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.05rem;
        transition: var(--transition);
        box-shadow: var(--shadow-blue);
    }
    .btn-visit:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 15px 30px -5px rgba(37,99,235,0.5);
        color: #fff;
    }

    /* Comments Section */
    .comments-wrapper {
        margin: 0 auto;
        animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.3s backwards;
    }
    .comments-header {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border);
    }
    .comment-box {
        background: var(--white);
        border-radius: var(--radius);
        padding: 1.5rem;
        margin-bottom: 1rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }
    .comment-box:hover { box-shadow: var(--shadow-md); }
    .comment-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 0.85rem;
    }
    .comment-meta .author { font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 0.5rem; }
    .comment-meta .author i { color: var(--primary); }
    .comment-meta .date { color: var(--text-light); }
    .comment-text { color: var(--text); line-height: 1.6; font-size: 0.95rem; }
    
    .comment-form { margin-top: 3rem; background: var(--white); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
    .comment-form textarea {
        width: 100%;
        background: var(--light-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 1rem;
        color: var(--dark);
        font-family: var(--font-sans);
        font-size: 0.95rem;
        resize: vertical;
        min-height: 120px;
        margin-bottom: 1rem;
        transition: var(--transition);
    }
    .comment-form textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px var(--primary-light);
    }
</style>

<main class="project-container">

    <div class="project-cover">
        <?php if ($project['image']): ?>
            <img src="assets/uploads/projects/<?= htmlspecialchars($project['image']) ?>" alt="Cover">
        <?php else: ?>
            <div style="width:100%; height:300px; display:flex; align-items:center; justify-content:center; color:rgba(37,99,235,0.2); font-size:6rem;">
                <i class="fa-solid fa-image"></i>
            </div>
        <?php endif; ?>
    </div>

    <div class="project-main-card">
        <div class="project-header-info">
            <h1><?= htmlspecialchars($project['title']) ?></h1>
            <?php if (!empty($project['makers'])): ?>
            <div class="project-makers">
                <i class="fa-solid fa-users"></i> <?= htmlspecialchars($project['makers']) ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="project-desc">
            <?= nl2br(htmlspecialchars($project['description'])) ?>
        </div>
        
        <div class="project-action">
            <i class="fa-solid fa-rocket icon-rocket"></i>
            <h3>Tertarik melihat hasil akhirnya?</h3>
            <?php if (!empty($project['link'])): ?>
                <a href="<?= htmlspecialchars($project['link']) ?>" target="_blank" class="btn-visit">
                    Kunjungi Website <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php else: ?>
                <p style="color:var(--text-light); font-size:1rem;">Link belum tersedia.</p>
            <?php endif; ?>
        </div>
    </div>

<div class="comments-section" style="margin-top:2rem;">
    <h3 class="comments-header"><i class="fa-regular fa-comments" style="color:var(--primary);"></i> Diskusi & Komentar</h3>
    
    <div id="commentsContainer">
        <!-- Comments injected via JS -->
    </div>

    <div class="comment-form" id="commentFormSection">
        <?php if (!empty($comment_username)): ?>
            <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:0.5rem;">
                <h4 id="formTitle" style="color:var(--dark); font-weight:800; margin:0;">Tulis Komentar</h4>
                <button type="button" id="cancelReplyBtn" class="btn btn-outline btn-sm" style="display:none;" onclick="cancelReply()">Batal Balas</button>
            </div>
            <p style="color:var(--text); font-size:0.85rem; margin-bottom:1rem;">Sebagai: <strong style="color:var(--primary);"><?= htmlspecialchars($comment_username) ?></strong></p>
            <form id="formComment">
                <input type="hidden" id="projectId" value="<?= $project['id'] ?>">
                <input type="hidden" id="userName" value="<?= htmlspecialchars($comment_username) ?>">
                <input type="hidden" id="parentId" value="">
                <textarea id="commentText" placeholder="Bagikan pendapat atau saran Anda tentang project ini..." required></textarea>
                <button type="submit" class="btn btn-primary">Kirim Komentar <i class="fa-solid fa-paper-plane" style="margin-left:0.25rem;"></i></button>
            </form>
        <?php else: ?>
            <div style="text-align:center; padding: 2rem 1rem;">
                <i class="fa-solid fa-lock" style="font-size:2rem; color:var(--border); margin-bottom:1rem; display:block;"></i>
                <p style="color:var(--text); margin-bottom:1rem; font-weight:500;">Silakan login terlebih dahulu untuk ikut berdiskusi.</p>
                <div style="display:flex; gap:1rem; justify-content:center;">
                    <a href="pengunjung_login.php" class="btn btn-outline">Login Pengunjung</a>
                    <a href="siswa/login.php" class="btn btn-primary">Login Siswa</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</main>

<script>
const projectId = <?= $project['id'] ?>;
const isLoggedIn = <?= !empty($comment_username) ? 'true' : 'false' ?>;

function loadComments() {
    fetch(`ajax_comments.php?action=get&project_id=${projectId}`)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                renderComments(data.data);
            }
        });
}

function renderAvatar(c) {
    if (c.avatar_type === 'image' && c.avatar) {
        return `<img src="${c.avatar}" alt="${escapeHTML(c.user_name)}" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">`;
    } else {
        return `<div style="width:28px;height:28px;border-radius:50%;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:800;">${escapeHTML(c.avatar)}</div>`;
    }
}

function renderComments(threads) {
    const container = document.getElementById('commentsContainer');
    container.innerHTML = '';
    
    if (threads.length === 0) {
        container.innerHTML = '<div style="text-align:center; padding:3rem 1rem; color:var(--text-light); background:var(--white); border-radius:var(--radius); border:1px dashed var(--border); margin-bottom:2rem;"><i class="fa-regular fa-comment-dots" style="font-size:3rem; margin-bottom:1rem; color:var(--border);"></i><p>Belum ada komentar. Jadilah yang pertama!</p></div>';
        return;
    }

    threads.forEach(t => {
        // Parent Comment
        let html = `
        <div class="comment-box" id="comment-${t.id}">
            <div class="comment-meta">
                <span class="author">${renderAvatar(t)} ${escapeHTML(t.user_name)}</span>
                <span class="date">${formatDate(t.created_at)}</span>
            </div>
            <div class="comment-text">${escapeHTML(t.comment).replace(/\n/g, '<br>')}</div>
            ${isLoggedIn ? `<button class="btn btn-outline btn-sm" style="margin-top:1rem; padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="replyTo(${t.id}, '${escapeHTML(t.user_name)}')"><i class="fa-solid fa-reply"></i> Balas</button>` : ''}
        </div>
        `;
        
        // Replies (if any)
        if (t.replies && t.replies.length > 0) {
            html += `<div style="padding-left: 2rem; border-left: 2px solid var(--border-light); margin-left: 1rem;">`;
            t.replies.forEach(r => {
                html += `
                <div class="comment-box" style="margin-top:0.5rem; background: var(--light-bg); box-shadow: none;">
                    <div class="comment-meta">
                        <span class="author">
                            ${renderAvatar(r)} ${escapeHTML(r.user_name)}
                            <span style="color:var(--text-light); font-size:0.75rem; font-weight:500; margin-left:0.5rem; display:inline-flex; align-items:center; gap:0.25rem;">
                                <i class="fa-solid fa-caret-right"></i> membalas ${escapeHTML(t.user_name)}
                            </span>
                        </span>
                        <span class="date">${formatDate(r.created_at)}</span>
                    </div>
                    <div class="comment-text">${escapeHTML(r.comment).replace(/\n/g, '<br>')}</div>
                </div>
                `;
            });
            html += `</div>`;
        }
        
        container.innerHTML += html;
    });
}

function replyTo(commentId, userName) {
    document.getElementById('parentId').value = commentId;
    document.getElementById('formTitle').innerHTML = `Membalas komentar <span style="color:var(--primary);">${userName}</span>`;
    document.getElementById('cancelReplyBtn').style.display = 'inline-block';
    
    // Scroll to form and focus
    const formSection = document.getElementById('commentFormSection');
    formSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        document.getElementById('commentText').focus();
    }, 500);
}

function cancelReply() {
    document.getElementById('parentId').value = '';
    document.getElementById('formTitle').innerHTML = 'Tulis Komentar';
    document.getElementById('cancelReplyBtn').style.display = 'none';
    document.getElementById('commentText').value = '';
}

function escapeHTML(str) {
    return str.replace(/[&<>'"]/g, tag => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    }[tag] || tag));
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) + ' ' + 
           d.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
}

const form = document.getElementById('formComment');
if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const userName = document.getElementById('userName').value;
        const text = document.getElementById('commentText').value;
        const parentId = document.getElementById('parentId').value;
        const btn = this.querySelector('button[type="submit"]');
        
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
        btn.disabled = true;
        
        const fd = new FormData();
        fd.append('project_id', projectId);
        fd.append('user_name', userName);
        fd.append('comment', text);
        if (parentId) fd.append('parent_id', parentId);

        fetch('ajax_comments.php?action=post', {
            method: 'POST',
            body: fd
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('commentText').value = '';
                cancelReply(); // Reset form state
                loadComments();
            } else {
                alert(data.message);
            }
        })
        .finally(() => {
            btn.innerHTML = 'Kirim Komentar <i class="fa-solid fa-paper-plane" style="margin-left:0.25rem;"></i>';
            btn.disabled = false;
        });
    });
}

window.addEventListener('DOMContentLoaded', loadComments);
</script>

<?php include 'includes/footer.php'; ?>
