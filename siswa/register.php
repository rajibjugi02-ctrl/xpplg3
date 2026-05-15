<?php
session_start();
require_once '../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!empty($nisn) && !empty($email)) {
        
        $stmt = $pdo->prepare("SELECT id, name FROM students WHERE id = ? OR nisn = ? LIMIT 1");
        $stmt->execute([$nisn, $nisn]);
        $student = $stmt->fetch();

        if ($student) {
            $student_id = $student['id'];
            // Cek apakah akun sudah punya email/password (sudah register)
            $checkReg = $pdo->prepare("SELECT email FROM students WHERE id = ? AND email IS NOT NULL AND email != ''");
            $checkReg->execute([$student_id]);
            if ($checkReg->rowCount() > 0) {
                $error = 'Akun ini sudah diregistrasi sebelumnya. Silakan langsung login.';
            } else {
                // Update tabel students dengan email, password, dan set nisn = input jika masih kosong
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE students SET email = ?, password = ?, nisn = ? WHERE id = ?");
                if ($update->execute([$email, $hashedPassword, $nisn, $student_id])) {
                    $success = 'Registrasi berhasil! Silakan login.';
                } else {
                    $error = 'Gagal melakukan registrasi. Coba lagi.';
                }
            }
        } else {
            $error = 'NISN/ID tidak terdaftar di database kelas. Pastikan format benar (contoh: 026).';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Siswa - PPLG 3</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #8b5cf6; --dark: #0f172a; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #4c1d95 50%, #5b21b6 100%); position: relative; overflow-x: hidden;
            padding: 2rem 1rem;
        }
        body::before, body::after { content: ''; position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.15; animation: float 8s ease-in-out infinite; }
        body::before { width: 500px; height: 500px; background: #a78bfa; top: -100px; left: -100px; }
        body::after { width: 400px; height: 400px; background: #8b5cf6; bottom: -100px; right: -100px; animation-delay: -4s; }
        @keyframes float { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(30px, 20px) scale(1.05); } }
        .login-container {
            background: rgba(255,255,255,0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1.5rem; padding: 3rem 2.5rem; width: 100%; max-width: 440px; position: relative; z-index: 10; box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .login-logo { text-align: center; margin-bottom: 2rem; }
        .login-logo .icon { width: 70px; height: 70px; background: var(--primary); border-radius: 1rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 1.75rem; color: #fff; box-shadow: 0 8px 20px rgba(139,92,246,0.4); }
        .login-logo h1 { font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 0.25rem; }
        .login-logo p { font-size: 0.875rem; color: rgba(255,255,255,0.5); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.825rem; font-weight: 600; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.35); font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 0.75rem; color: #fff; font-family: 'Inter', sans-serif; font-size: 0.95rem; outline: none; transition: all 0.3s; }
        .form-group input::placeholder { color: rgba(255,255,255,0.25); }
        .form-group input:focus { border-color: var(--primary); background: rgba(139,92,246,0.1); box-shadow: 0 0 0 3px rgba(139,92,246,0.15); }
        .error-msg { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .success-msg { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #6ee7b7; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .btn-login { width: 100%; padding: 0.9rem; background: var(--primary); color: #fff; font-size: 1rem; font-weight: 700; font-family: 'Inter', sans-serif; border: none; border-radius: 0.75rem; cursor: pointer; box-shadow: 0 8px 20px rgba(139,92,246,0.4); transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .btn-login:hover { background: #7c3aed; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(139,92,246,0.5); }
        .back-link { text-align: center; margin-top: 1.5rem; }
        .back-link a { color: rgba(255,255,255,0.45); font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
        .back-link a:hover { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="icon"><i class="fa-solid fa-user-plus"></i></div>
            <h1>Registrasi Siswa</h1>
            <p>Klaim akun PPLG 3 Anda</p>
        </div>

        <?php if ($error): ?>
        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-msg"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div>
        <div class="back-link" style="margin-top: 1rem;">
            <a href="login.php" class="btn-login" style="text-decoration: none; display:inline-block;"><i class="fa-solid fa-arrow-right"></i> Lanjut Login</a>
        </div>
        <?php else: ?>

        <form method="POST" action="" autocomplete="off">
            <!-- Honeypot fields to prevent browser autofill -->
            <input type="text" name="fake_email" style="display:none;" tabindex="-1" autocomplete="username">
            <input type="password" name="fake_pass" style="display:none;" tabindex="-1" autocomplete="current-password">

            <div class="form-group">
                <label for="nisn">NISN (Harus terdaftar oleh Admin)</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-id-card"></i>
                    <input type="text" id="nisn" name="nisn" placeholder="Masukkan NISN" required autocomplete="off">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="Masukkan Email aktif" required autocomplete="off">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Buat password baru" required autocomplete="new-password">
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-user-check"></i> Daftar Sekarang
            </button>
        </form>
        <?php endif; ?>

        <div class="back-link">
            Sudah punya akun? <a href="login.php" style="color: var(--primary); font-weight:600;">Login di sini</a>
        </div>
    </div>
</body>
</html>
