<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/logger.php';

// Redirect if already logged in
if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? ''); // bisa NISN atau Email
    $password = trim($_POST['password'] ?? '');

    if (!empty($identifier) && !empty($password)) {
        // Cek user berdasarkan email atau NISN
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? OR nisn = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $student = $stmt->fetch();

        if ($student && password_verify($password, $student['password'])) {
            $_SESSION['student_logged_in'] = true;
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_nisn'] = $student['nisn'];
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['student_kelas'] = $student['kelas'];
            
            logActivity($pdo, 'student', $student['name'], 'Logged in to Student Portal');

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Email/NISN atau Password salah. Atau akun belum diregistrasi.';
        }
    } else {
        $error = 'Harap isi Email/NISN dan Password.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - PPLG 3</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8b5cf6; /* Purple for students */
            --dark: #0f172a;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #4c1d95 50%, #5b21b6 100%);
            position: relative;
            overflow-x: hidden;
            padding: 2rem 1rem;
        }
        body::before, body::after {
            content: ''; position: fixed; border-radius: 50%;
            filter: blur(80px); opacity: 0.15; animation: float 8s ease-in-out infinite;
        }
        body::before { width: 500px; height: 500px; background: #a78bfa; top: -100px; left: -100px; }
        body::after { width: 400px; height: 400px; background: #8b5cf6; bottom: -100px; right: -100px; animation-delay: -4s; }
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
            font-size: 1.75rem; color: #fff; box-shadow: 0 8px 20px rgba(139,92,246,0.4);
        }
        .login-logo h1 { font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 0.25rem; }
        .login-logo p { font-size: 0.875rem; color: rgba(255,255,255,0.5); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: flex; justify-content: space-between; font-size: 0.825rem; font-weight: 600; color: rgba(255,255,255,0.7); margin-bottom: 0.5rem; }
        .form-group label a { color: var(--primary); text-decoration: none; transition: 0.2s; }
        .form-group label a:hover { color: #a78bfa; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.35); font-size: 0.9rem; }
        .form-group input {
            width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem;
            background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 0.75rem; color: #fff; font-family: 'Inter', sans-serif; font-size: 0.95rem;
            outline: none; transition: all 0.3s;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.25); }
        .form-group input:focus { border-color: var(--primary); background: rgba(139,92,246,0.1); box-shadow: 0 0 0 3px rgba(139,92,246,0.15); }
        .error-msg { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.85rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .btn-login {
            width: 100%; padding: 0.9rem; background: var(--primary); color: #fff; font-size: 1rem; font-weight: 700;
            font-family: 'Inter', sans-serif; border: none; border-radius: 0.75rem; cursor: pointer;
            box-shadow: 0 8px 20px rgba(139,92,246,0.4); transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .btn-login:hover { background: #7c3aed; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(139,92,246,0.5); }
        
        .divider { display: flex; align-items: center; text-align: center; margin: 1.5rem 0; color: rgba(255,255,255,0.3); font-size: 0.875rem; }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .divider::before { margin-right: .5em; } .divider::after { margin-left: .5em; }
        
        .btn-google {
            width: 100%; padding: 0.9rem; background: #fff; color: #333; font-size: 0.95rem; font-weight: 600;
            font-family: 'Inter', sans-serif; border: none; border-radius: 0.75rem; cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .btn-google:hover { background: #f8fafc; transform: translateY(-2px); }
        .btn-google img { width: 20px; }

        .back-link { text-align: center; margin-top: 1.5rem; }
        .back-link a { color: rgba(255,255,255,0.45); font-size: 0.85rem; text-decoration: none; transition: color 0.2s; }
        .back-link a:hover { color: rgba(255,255,255,0.8); }
        .register-link { text-align: center; margin-top: 1rem; font-size: 0.875rem; color: rgba(255,255,255,0.6); }
        .register-link a { color: var(--primary); text-decoration: none; font-weight: 600; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="icon"><i class="fa-solid fa-user-graduate"></i></div>
            <h1>Portal Siswa</h1>
            <p>Akses khusus siswa PPLG 3</p>
        </div>

        <?php if ($error): ?>
        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <!-- Honeypot fields to prevent browser autofill -->
            <input type="text" name="fake_email" style="display:none;" tabindex="-1" autocomplete="username">
            <input type="password" name="fake_pass" style="display:none;" tabindex="-1" autocomplete="current-password">

            <div class="form-group">
                <label for="identifier">Email atau NISN</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="text" id="identifier" name="identifier" placeholder="Masukkan Email / NISN" required autocomplete="off">
                </div>
            </div>
            <div class="form-group">
                <label for="password">
                    <span>Password</span>
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk
            </button>
        </form>

        <div class="register-link" style="margin-top: 1.5rem;">
            Belum punya akun? <a href="register.php">Registrasi Akun Siswa</a>
        </div>

        <div class="back-link">
            <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>

    <script>
        // Force-clear fields to prevent annoying leftover text after failed login
        window.addEventListener('load', function () {
            const u = document.getElementById('identifier');
            const p = document.getElementById('password');
            setTimeout(() => {
                if (u) { u.value = ''; u.setAttribute('readonly', true); }
                if (p) { p.value = ''; p.removeAttribute('readonly'); }
                setTimeout(() => {
                    if (u) u.removeAttribute('readonly');
                }, 100);
            }, 50);
        });
    </script>
</body>
</html>
