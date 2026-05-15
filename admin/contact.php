<?php
require_once 'includes/auth.php';
require_once '../includes/db.php';

$page_title  = 'Kelola Contact & Logo';
$active_menu = 'contact';
$msg = '';

$uploadDir = '../assets/uploads/logo/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // 1. UPDATE CONTACT
    if ($_POST['action'] === 'contact') {
        $ig    = trim($_POST['instagram']);
        $wa    = preg_replace('/\D/', '', trim($_POST['whatsapp']));
        $email = trim($_POST['email']);
        $count = $pdo->query("SELECT COUNT(*) FROM contact")->fetchColumn();
        if ($count > 0) {
            $pdo->prepare("UPDATE contact SET instagram=?,whatsapp=?,email=? WHERE id=1")
                ->execute([$ig, $wa, $email]);
        } else {
            $pdo->prepare("INSERT INTO contact (instagram,whatsapp,email) VALUES (?,?,?)")
                ->execute([$ig, $wa, $email]);
        }
        $msg = ['type'=>'success','text'=>'Informasi kontak berhasil diperbarui!'];
    }
    // 2. UPDATE LOGO
    elseif ($_POST['action'] === 'logo') {
        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg'])) {
                // Hapus logo lama
                $oldLogo = $pdo->query("SELECT logo FROM contact WHERE id=1")->fetchColumn();
                if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                    unlink($uploadDir . $oldLogo);
                }

                $filename = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                    $count = $pdo->query("SELECT COUNT(*) FROM contact")->fetchColumn();
                    if ($count > 0) {
                        $pdo->prepare("UPDATE contact SET logo=? WHERE id=1")->execute([$filename]);
                    } else {
                        $pdo->prepare("INSERT INTO contact (logo) VALUES (?)")->execute([$filename]);
                    }
                    $msg = ['type'=>'success','text'=>'Logo website berhasil diperbarui!'];
                }
            } else {
                $msg = ['type'=>'danger','text'=>'Format file tidak didukung! Gunakan PNG/JPG/SVG.'];
            }
        }
    }
    // 3. HAPUS LOGO
    elseif ($_POST['action'] === 'delete_logo') {
        $oldLogo = $pdo->query("SELECT logo FROM contact WHERE id=1")->fetchColumn();
        if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
            unlink($uploadDir . $oldLogo);
        }
        $pdo->prepare("UPDATE contact SET logo='' WHERE id=1")->execute();
        $msg = ['type'=>'success','text'=>'Logo website berhasil dihapus, kembali ke logo teks bawaan.'];
    }
}

$contact = $pdo->query("SELECT * FROM contact LIMIT 1")->fetch();
if (!$contact) $contact = ['instagram'=>'','whatsapp'=>'','email'=>'','logo'=>''];

include 'includes/header.php';
?>

<?php if ($msg): ?>
<div class="alert alert-<?= $msg['type'] ?>"><i class="fa-solid fa-circle-info"></i> <?= $msg['text'] ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;margin-bottom:1.5rem;">

    <!-- Contact Info Form -->
    <div class="card">
        <div class="card-header"><h3><i class="fa-solid fa-address-book"></i> Informasi Kontak Publik</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="contact">
                <div class="form-group">
                    <label><i class="fa-brands fa-instagram" style="color:#e1306c;"></i> Instagram</label>
                    <input type="text" name="instagram" class="form-control" value="<?= htmlspecialchars($contact['instagram']) ?>" placeholder="@username">
                </div>
                <div class="form-group">
                    <label><i class="fa-brands fa-whatsapp" style="color:#25d366;"></i> WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($contact['whatsapp']) ?>" placeholder="628123456789">
                    <p style="font-size:0.78rem;color:#94a3b8;margin-top:0.35rem;">Format: 628xxxxxxxxxx (kode negara tanpa +)</p>
                </div>
                <div class="form-group">
                    <label><i class="fa-solid fa-envelope" style="color:#2563eb;"></i> Gmail</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($contact['email']) ?>" placeholder="hello@example.com">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">
                    <i class="fa-solid fa-save"></i> Simpan Kontak
                </button>
            </form>
        </div>
    </div>

    <!-- Logo Settings -->
    <div class="card">
        <div class="card-header"><h3><i class="fa-solid fa-image"></i> Logo Website</h3></div>
        <div class="card-body">
            <div style="text-align:center;padding:1.5rem;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:0.875rem;margin-bottom:1.25rem;">
                <?php if (!empty($contact['logo'])): ?>
                    <img src="../assets/uploads/logo/<?= htmlspecialchars($contact['logo']) ?>"
                         alt="Logo Website"
                         style="max-width:180px;max-height:80px;object-fit:contain;margin-bottom:1rem;">
                    <div>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Kembali ke logo teks/icon bawaan?');">
                            <input type="hidden" name="action" value="delete_logo">
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Hapus Logo</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="font-size:3rem;color:#94a3b8;margin-bottom:0.5rem;"><i class="fa-solid fa-code"></i></div>
                    <p style="color:#64748b;font-size:0.9rem;font-weight:600;">Menggunakan Logo Teks Bawaan</p>
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="logo">
                <div class="form-group">
                    <label>Upload Logo Baru <span style="font-weight:400;color:#94a3b8;">(PNG/SVG transparan disarankan)</span></label>
                    <input type="file" name="logo" class="form-control" accept="image/png, image/jpeg, image/webp, image/svg+xml" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">
                    <i class="fa-solid fa-upload"></i> Upload Logo
                </button>
            </form>
        </div>
    </div>

</div>

<!-- Credentials Info — Read Only -->
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header"><h3><i class="fa-solid fa-shield-halved"></i> Informasi Login Admin</h3></div>
    <div class="card-body">
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.65rem;padding:1.25rem;margin-bottom:1rem;max-width:500px;">
            <p style="font-size:0.8rem;font-weight:600;color:#1e40af;margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.05em;">
                <i class="fa-solid fa-lock"></i> Kredensial Admin (Terkunci)
            </p>
            <div style="display:flex;flex-direction:column;gap:0.65rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border-radius:0.5rem;padding:0.65rem 1rem;">
                    <span style="font-size:0.85rem;color:#64748b;font-weight:500;">Username</span>
                    <code style="font-size:0.9rem;font-weight:700;color:#0f172a;">adminPPLG3</code>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border-radius:0.5rem;padding:0.65rem 1rem;">
                    <span style="font-size:0.85rem;color:#64748b;font-weight:500;">Password</span>
                    <code style="font-size:0.9rem;font-weight:700;color:#0f172a;">admin123</code>
                </div>
            </div>
        </div>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.65rem;padding:1rem;font-size:0.85rem;color:#166534;">
            <i class="fa-solid fa-circle-check"></i>
            Kredensial di atas sudah dikunci secara permanen. Hanya kombinasi username dan password tersebut yang bisa mengakses dashboard admin ini.
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
