<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get') {
    $project_id = intval($_GET['project_id'] ?? 0);
    if (!$project_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid project ID']);
        exit;
    }

    // Fetch all visible comments for this project, ordered by oldest first to show thread properly
    $stmt = $pdo->prepare("SELECT * FROM project_comments WHERE project_id = ? AND is_visible = 1 ORDER BY created_at ASC");
    $stmt->execute([$project_id]);
    $comments = $stmt->fetchAll();

    // Fetch global logo for Admin
    $adminLogo = '';
    try {
        $adminLogo = $pdo->query("SELECT logo FROM contact LIMIT 1")->fetchColumn();
    } catch(Exception $e) {}
    
    // Fetch all students to map photos
    $students = [];
    try {
        $students = $pdo->query("SELECT name, photo FROM students WHERE photo IS NOT NULL AND photo != ''")->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch(Exception $e) {}

    // Organize into threads
    $threads = [];
    $replies = [];
    
    foreach ($comments as $c) {
        $name = $c['user_name'];
        if ($name === 'Admin PPLG 3') {
            $c['avatar'] = $adminLogo ? 'assets/uploads/logo/' . $adminLogo : 'A';
            $c['avatar_type'] = $adminLogo ? 'image' : 'text';
        } elseif (array_key_exists($name, $students)) {
            $c['avatar'] = 'assets/uploads/students/' . $students[$name];
            $c['avatar_type'] = 'image';
        } else {
            $words = explode(' ', trim($name));
            $initials = '';
            foreach ($words as $w) {
                if (!empty($w)) $initials .= strtoupper(substr($w, 0, 1));
            }
            $c['avatar'] = substr($initials, 0, 2) ?: '?';
            $c['avatar_type'] = 'text';
        }

        if ($c['parent_id']) {
            $replies[$c['parent_id']][] = $c;
        } else {
            $threads[] = $c;
        }
    }

    // Format output
    $output = [];
    foreach ($threads as $t) {
        $t['replies'] = $replies[$t['id']] ?? [];
        $output[] = $t;
    }

    echo json_encode(['status' => 'success', 'data' => $output]);
    exit;
}

if ($action === 'post') {
    $project_id = intval($_POST['project_id'] ?? 0);
    $user_name = trim($_POST['user_name'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

    if (!$project_id || empty($user_name) || empty($comment)) {
        echo json_encode(['status' => 'error', 'message' => 'Semua field harus diisi']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO project_comments (project_id, user_name, comment, parent_id, is_visible) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$project_id, $user_name, $comment, $parent_id]);
        echo json_encode(['status' => 'success', 'message' => 'Komentar berhasil dikirim']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan komentar: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
exit;
