<?php
session_start();
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// =============================================
// CREDENTIALS TERKUNCI — TIDAK BISA DIUBAH
// Username : adminPPLG3
// Password : admin123
// =============================================
define('ADMIN_USERNAME', 'adminPPLG3');
define('ADMIN_PASSWORD', 'admin123');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username']  = ADMIN_USERNAME;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah. Coba lagi.';
        // Tambahkan delay kecil untuk mencegah brute force
        sleep(1);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Admin - X PPLG 3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --text: #475569;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1e40af 100%);
            position: relative;
            overflow-x: hidden;
            padding: 2rem 1rem;
        }
        /* Animated background blobs */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
        }
        body::before {
            width: 500px; height: 500px;
            background: #3b82f6;
            top: -100px; left: -100px;
        }
        body::after {
            width: 400px; height: 400px;
            background: #60a5fa;
            bottom: -100px; right: -100px;
            animation-delay: -4s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0,0) scale(1); }
            50% { transform: translate(30px, 20px) scale(1.05); }
        }
        .login-container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1.5rem;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 10;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-logo .icon {
            width: 70px; height: 70px;
            background: var(--primary);
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem; color: #fff;
            box-shadow: 0 8px 20px rgba(37,99,235,0.4);
        }
        .login-logo h1 {
            font-size: 1.5rem; font-weight: 800; color: #fff;
            margin-bottom: 0.25rem;
        }
        .login-logo p { font-size: 0.875rem; color: rgba(255,255,255,0.5); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.825rem; font-weight: 600;
            color: rgba(255,255,255,0.7);
            margin-bottom: 0.5rem;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
            color: rgba(255,255,255,0.35); font-size: 0.9rem;
        }
        .form-group input {
            width: 100%; padding: 0.875rem 1rem 0.875rem 2.75rem;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 0.75rem;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            outline: none; transition: all 0.3s;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.25); }
        .form-group input:focus {
            border-color: var(--primary);
            background: rgba(37,99,235,0.1);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        }
        .error-msg {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: var(--primary);
            color: #fff;
            font-size: 1rem; font-weight: 700;
            font-family: 'Inter', sans-serif;
            border: none; border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37,99,235,0.4);
            transition: all 0.3s;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .btn-login:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(37,99,235,0.5);
        }
        .back-link {
            text-align: center; margin-top: 1.5rem;
        }
        .back-link a {
            color: rgba(255,255,255,0.45); font-size: 0.85rem;
            text-decoration: none; transition: color 0.2s;
        }
        .back-link a:hover { color: rgba(255,255,255,0.8); }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div class="icon"><i class="fa-solid fa-code"></i></div>
            <h1>Portal Admin</h1>
            <p>X PPLG 3 Engineering Hub</p>
        </div>

        <?php if ($error): ?>
        <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off">
            <!-- Honeypot fields — trick browser autofill to fill these instead -->
            <input type="text"     name="fake_user" style="display:none;" tabindex="-1" autocomplete="username">
            <input type="password" name="fake_pass" style="display:none;" tabindex="-1" autocomplete="current-password">

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" name="username"
                           placeholder="Masukkan username"
                           autocomplete="off"
                           required>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password"
                           autocomplete="new-password"
                           required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Dashboard
            </button>
        </form>
        <div class="back-link">
            <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Website</a>
        </div>
    </div>

    <script>
        // Force-clear fields on every page load to defeat autofill
        window.addEventListener('load', function () {
            const u = document.getElementById('username');
            const p = document.getElementById('password');
            // Slight delay gives browser time to autofill first, then we wipe it
            setTimeout(() => {
                u.value = '';
                p.value = '';
                u.setAttribute('readonly', true);
                setTimeout(() => {
                    u.removeAttribute('readonly');
                    p.removeAttribute('readonly');
                }, 100);
            }, 50);
        });
    </script>
</body>
</html>
