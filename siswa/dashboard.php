<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/logger.php';

// Cek sesi
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$success_msg = '';
$error_msg = '';

// Ambil data siswa terkini
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_biodata'])) {
        $name = trim($_POST['name'] ?? '');
        $nisn = trim($_POST['nisn'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $portfolio_link = trim($_POST['portfolio_link'] ?? '');
        $github_link = trim($_POST['github_link'] ?? '');

        // Upload Foto jika ada
        $photo = $student['photo'];
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/uploads/students/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $new_filename = 'std_' . time() . '.' . $file_ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_filename)) {
                    // Hapus foto lama jika ada dan bukan default
                    if (!empty($student['photo']) && file_exists($upload_dir . $student['photo'])) {
                        unlink($upload_dir . $student['photo']);
                    }
                    $photo = $new_filename;
                }
            } else {
                $error_msg = "Format foto tidak didukung (Gunakan JPG, PNG, WEBP).";
            }
        }

        if (empty($error_msg)) {
            $update = $pdo->prepare("UPDATE students SET name=?, nisn=?, kelas=?, email=?, portfolio_link=?, github_link=?, photo=? WHERE id=?");
            if ($update->execute([$name, $nisn, $kelas, $email, $portfolio_link, $github_link, $photo, $student_id])) {
                $success_msg = "Biodata berhasil diperbarui!";
                logActivity($pdo, 'student', $name, 'Updated profile biodata');
                // Refresh data
                $stmt->execute([$student_id]);
                $student = $stmt->fetch();
                $_SESSION['student_name'] = $name;
            } else {
                $error_msg = "Gagal memperbarui biodata.";
            }
        }
    } elseif (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (password_verify($old_password, $student['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare("UPDATE students SET password=? WHERE id=?");
                    if ($update->execute([$hashed, $student_id])) {
                        $success_msg = "Password berhasil diubah!";
                        logActivity($pdo, 'student', $student['name'], 'Changed password');
                    } else {
                        $error_msg = "Gagal mengubah password.";
                    }
                } else {
                    $error_msg = "Password baru minimal 6 karakter.";
                }
            } else {
                $error_msg = "Konfirmasi password tidak cocok.";
            }
        } else {
            $error_msg = "Password lama salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - PPLG 3</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8b5cf6; --primary-hover: #7c3aed;
            --bg: #0f172a; --card-bg: #1e293b; --border: #334155;
            --text-main: #f8fafc; --text-muted: #94a3b8;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 280px; background: var(--card-bg); border-right: 1px solid var(--border); padding: 2rem 1.5rem; display: flex; flex-direction: column; }
        .sidebar-logo { display: flex; align-items: center; gap: 1rem; font-size: 1.25rem; font-weight: 800; margin-bottom: 3rem; color: #fff; }
        .sidebar-logo i { color: var(--primary); font-size: 1.5rem; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; flex: 1; }
        .nav-item { display: flex; align-items: center; gap: 1rem; padding: 0.875rem 1rem; border-radius: 0.75rem; color: var(--text-muted); text-decoration: none; font-weight: 500; transition: all 0.2s; cursor: pointer; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-item.active { background: var(--primary); color: #fff; }
        .nav-item i { width: 20px; text-align: center; }
        .nav-item.logout { margin-top: auto; color: #fca5a5; }
        .nav-item.logout:hover { background: rgba(239,68,68,0.1); color: #ef4444; }

        /* Main Content */
        .main-content { flex: 1; padding: 3rem; overflow-y: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header h1 { font-size: 1.875rem; font-weight: 700; }
        .header .user-badge { display: flex; align-items: center; gap: 1rem; background: var(--card-bg); padding: 0.5rem 1rem; border-radius: 2rem; border: 1px solid var(--border); }
        .header .user-badge img { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        
        /* Sections */
        .section { display: none; animation: fadeIn 0.3s ease; }
        .section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 1rem; padding: 2rem; margin-bottom: 2rem; }
        .card h2 { margin-bottom: 1.5rem; font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
        .card h2 i { color: var(--primary); }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 500; color: var(--text-muted); margin-bottom: 0.5rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem 1rem; background: var(--bg); border: 1px solid var(--border); border-radius: 0.5rem; color: #fff; font-family: 'Inter', sans-serif; transition: 0.2s; }
        .form-group input:focus { border-color: var(--primary); outline: none; }
        
        .photo-upload { display: flex; align-items: center; gap: 1.5rem; }
        .photo-preview { width: 100px; height: 100px; border-radius: 50%; background: var(--bg); border: 2px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .photo-preview img { width: 100%; height: 100%; object-fit: cover; }
        .photo-preview i { font-size: 2rem; color: var(--border); }
        
        .btn { padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; font-family: inherit; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .alert-success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .alert-danger { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }
        
        .welcome-card { background: linear-gradient(135deg, var(--primary) 0%, #4c1d95 100%); color: white; border-radius: 1rem; padding: 2.5rem; margin-bottom: 2rem; position: relative; overflow: hidden; }
        .welcome-card h2 { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; }
        .welcome-card p { opacity: 0.9; max-width: 600px; line-height: 1.6; }
        .welcome-card i.bg-icon { position: absolute; right: -20px; bottom: -40px; font-size: 15rem; opacity: 0.1; }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; padding: 1rem; flex-direction: row; flex-wrap: wrap; justify-content: space-between; align-items: center; border-right: none; border-bottom: 1px solid var(--border); }
            .sidebar-logo { margin-bottom: 0; }
            .nav-menu { flex-direction: row; width: 100%; overflow-x: auto; margin-top: 1rem; padding-bottom: 0.5rem; }
            .nav-item { white-space: nowrap; }
            .main-content { padding: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <i class="fa-solid fa-graduation-cap"></i> Portal Siswa
        </div>
        <ul class="nav-menu">
            <li><a class="nav-item active" onclick="showSection('dashboard', this)"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
            <li><a class="nav-item" onclick="showSection('biodata', this)"><i class="fa-solid fa-user-pen"></i> Biodata Profil</a></li>
            <li><a class="nav-item" onclick="showSection('settings', this)"><i class="fa-solid fa-gear"></i> Pengaturan</a></li>
            <li><a href="../index.php" class="nav-item"><i class="fa-solid fa-globe"></i> Lihat Website</a></li>
            <li><a href="../logout.php" class="nav-item logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>Halo, <?= htmlspecialchars($student['name']) ?>!</h1>
            <div class="user-badge">
                <span style="font-weight: 600; font-size: 0.875rem;"><?= htmlspecialchars($student['kelas'] ?? 'Siswa') ?></span>
                <?php if(!empty($student['photo'])): ?>
                    <img src="../assets/uploads/students/<?= htmlspecialchars($student['photo']) ?>" alt="Profile">
                <?php else: ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($student['name']) ?>&background=8b5cf6&color=fff" alt="Profile">
                <?php endif; ?>
            </div>
        </div>

        <?php if($success_msg): ?>
            <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= $success_msg ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <?= $error_msg ?></div>
        <?php endif; ?>

        <!-- Dashboard Section -->
        <section id="dashboard" class="section active">
            <div class="welcome-card">
                <i class="fa-solid fa-graduation-cap bg-icon"></i>
                <h2>Selamat Datang di Portal Siswa</h2>
                <p>Di sini kamu bisa mengelola biodata profil yang akan ditampilkan di halaman publik "Siswa". Pastikan data kamu seperti Portofolio dan GitHub selalu up-to-date untuk memamerkan karyamu!</p>
            </div>
            
            <div class="form-grid">
                <div class="card">
                    <h2><i class="fa-solid fa-address-card"></i> Status Profil</h2>
                    <ul style="list-style:none; line-height:2;">
                        <li><strong>NISN:</strong> <?= htmlspecialchars($student['nisn'] ?: 'Belum diisi') ?></li>
                        <li><strong>Kelas:</strong> <?= htmlspecialchars($student['kelas'] ?: 'Belum diisi') ?></li>
                        <li><strong>Portofolio:</strong> <?= $student['portfolio_link'] ? '<span style="color:#34d399">Tersedia</span>' : '<span style="color:#f87171">Belum ada</span>' ?></li>
                    </ul>
                </div>
                <div class="card">
                    <h2><i class="fa-solid fa-lightbulb"></i> Tips</h2>
                    <p style="color:var(--text-muted); font-size:0.9rem; line-height:1.6;">Gunakan foto profil yang jelas dan sopan. Masukkan link GitHub dan Portofolio pribadi agar pengunjung web dapat melihat hasil karya coding/desain yang telah kamu buat.</p>
                </div>
            </div>
        </section>

        <!-- Biodata Section -->
        <section id="biodata" class="section">
            <div class="card">
                <h2><i class="fa-solid fa-user-pen"></i> Edit Biodata & Portofolio</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_biodata" value="1">
                    
                    <div class="form-group full">
                        <label>Foto Profil</label>
                        <div class="photo-upload">
                            <div class="photo-preview">
                                <?php if(!empty($student['photo'])): ?>
                                    <img src="../assets/uploads/students/<?= htmlspecialchars($student['photo']) ?>" alt="Preview">
                                <?php else: ?>
                                    <i class="fa-solid fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <input type="file" name="photo" accept="image/*" style="border:none; padding:0;">
                                <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.5rem;">Format: JPG, PNG. Maks 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>NISN</label>
                            <input type="text" name="nisn" value="<?= htmlspecialchars($student['nisn'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Kelas</label>
                            <input type="text" name="kelas" value="<?= htmlspecialchars($student['kelas'] ?? '') ?>" placeholder="Misal: X PPLG 3">
                        </div>
                        <div class="form-group">
                            <label>Email Utama</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <hr style="border:none; border-top:1px solid var(--border); margin: 2rem 0;">
                    
                    <h3 style="margin-bottom:1rem; font-size:1.1rem; font-weight:600;"><i class="fa-solid fa-link" style="color:var(--primary)"></i> Link Sosial & Karya</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Link Web Portofolio (Opsional)</label>
                            <input type="url" name="portfolio_link" value="<?= htmlspecialchars($student['portfolio_link'] ?? '') ?>" placeholder="https://namamu.github.io">
                        </div>
                        <div class="form-group">
                            <label>Link GitHub Profile (Opsional)</label>
                            <input type="url" name="github_link" value="<?= htmlspecialchars($student['github_link'] ?? '') ?>" placeholder="https://github.com/username">
                        </div>
                    </div>

                    <div class="form-group full" style="margin-top: 1rem;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Settings Section -->
        <section id="settings" class="section">
            <div class="card" style="max-width: 500px;">
                <h2><i class="fa-solid fa-lock"></i> Ubah Password</h2>
                <form method="POST">
                    <input type="hidden" name="update_password" value="1">
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password Baru</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i> Update Password</button>
                </form>
            </div>
        </section>

    </main>

    <script>
        function showSection(id, element) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
            // Remove active from nav
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            
            // Show target
            document.getElementById(id).classList.add('active');
            element.classList.add('active');
        }
    </script>
</body>
</html>
