<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/logger.php';

// Redirect if already logged in
if (isset($_SESSION['visitor_logged_in']) && $_SESSION['visitor_logged_in'] === true) {
    header('Location: index.php');
    exit;
}
if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    header('Location: siswa/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');

    if (!empty($name) && !empty($kelas)) {
        // Cek apakah pengunjung sudah pernah login sebelumnya
        $stmt = $pdo->prepare("SELECT id FROM visitors WHERE name = ? AND kelas = ? LIMIT 1");
        $stmt->execute([$name, $kelas]);
        $visitor = $stmt->fetch();

        if (!$visitor) {
            // Pengunjung baru
            $insert = $pdo->prepare("INSERT INTO visitors (name, kelas) VALUES (?, ?)");
            $insert->execute([$name, $kelas]);
        } else {
            // Update last_login
            $update = $pdo->prepare("UPDATE visitors SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $update->execute([$visitor['id']]);
        }

        // Set session
        $_SESSION['visitor_logged_in'] = true;
        $_SESSION['visitor_name'] = $name;
        $_SESSION['visitor_kelas'] = $kelas;

        // Log Activity
        logActivity($pdo, 'visitor', "$name ($kelas)", 'Logged in to website');

        header('Location: index.php');
        exit;
    } else {
        $error = 'Nama dan Kelas wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pengunjung - PPLG 3</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981; /* Green for visitor */
            --dark: #0f172a;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #064e3b 50%, #065f46 100%);
            position: relative;
            overflow: hidden;
        }
        body::before, body::after {
            content: ''; position: fixed; border-radius: 50%;
            filter: blur(80px); opacity: 0.15; animation: float 8s ease-in-out infinite;
        }
        body::before { width: 500px; height: 500px; background: #34d399; top: -100px; left: -100px; }
        body::after { width: 400px; height: 400px; background: #10b981; bottom: -100px; right: -100px; animation-delay: -4s; }
        @keyframes float { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(30px, 20px) scale(1.05); } }
        .login-container {
            background: rgba(255,255,255,0.05); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12); border-radius: 1.5rem;
            padding: 3rem 2.5rem; width: 100%; max-width: 440px;
            position: relative; z-index: 10; box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-logo .icon {
            width: 70px; height: 70px; background: var(--primary); border-radius: 1rem;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;
            font-size: 1.75rem; color: #fff; box-shadow: 0 8px 20px rgba(16,185,129,0.4);
        }
        .login-logo h1 { font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 0.25rem; }
        .login-logo p { font-size: 0.875rem; color: rgba(255,255,255,0.5); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.825rem; font-weight: 600; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.35); font-size: 0.9rem; }
        .form-group input {
            width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem;
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 0.75rem; color: #fff; font-family: 'Inter', sans-serif; font-size: 0.95rem;
            outline: none; transition: all 0.3s;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.25); }
        .form-group input:focus { border-color: var(--primary); background: rgba(16,185,129,0.1); box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
        .error-msg { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .btn-login {
            width: 100%; padding: 0.9rem; background: var(--primary); color: #fff; font-size: 1rem; font-weight: 700;
            font-family: 'Inter', sans-serif; border: none; border-radius: 0.75rem; cursor: pointer;
            box-shadow: 0 8px 20px rgba(16,185,129,0.4); transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .btn-login:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(16,185,129,0.5); }
        .back-link { text-align: center; margin-top: 1.5rem; }
        .back-link a { color: rgba(255,255,255,0.45); font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
        .back-link a:hover { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="icon"><i class="fa-solid fa-users-viewfinder"></i></div>
            <h1>Portal Pengunjung</h1>
            <p>Jelajahi PPLG 3 Engineering</p>
        </div>

        <?php if ($error): ?>
        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="name" name="name" placeholder="Masukkan nama Anda" required>
                </div>
            </div>
            <div class="form-group">
                <label for="kelas">Kelas / Instansi</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-school"></i>
                    <input type="text" id="kelas" name="kelas" placeholder="Contoh: X PPLG 1, Guru, dll." required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Sekarang
            </button>
        </form>
        <div class="back-link">
            <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
