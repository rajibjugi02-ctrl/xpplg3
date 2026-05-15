<?php
require_once 'includes/db.php';
$page_title = "Contact - X PPLG 3";
$active_page = "contact";

$contact = $pdo->query("SELECT * FROM contact LIMIT 1")->fetch();
if (!$contact) {
    $contact = ['instagram'=>'@pplg3_engineering','whatsapp'=>'6281234567890','email'=>'hello@pplg3.com'];
}
include 'includes/header.php';
?>

<main>
    <div class="container">
        <div class="page-header reveal">
            <h1 class="page-title">Hubungi <span>Kami</span></h1>
            <p class="page-subtitle">Ada pertanyaan atau ingin berkolaborasi? Hubungi kami melalui platform berikut.</p>
        </div>

        <div class="contact-grid reveal-group">
            <a href="https://instagram.com/<?= ltrim(htmlspecialchars($contact['instagram']),'@') ?>" target="_blank" class="contact-card contact-ig">
                <div class="contact-icon">
                    <i class="fa-brands fa-instagram"></i>
                </div>
                <div class="contact-info">
                    <h3>Instagram</h3>
                    <p><?= htmlspecialchars($contact['instagram']) ?></p>
                    <span>Lihat konten kami <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <a href="https://wa.me/<?= htmlspecialchars($contact['whatsapp']) ?>" target="_blank" class="contact-card contact-wa">
                <div class="contact-icon">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div class="contact-info">
                    <h3>WhatsApp</h3>
                    <p>+<?= htmlspecialchars($contact['whatsapp']) ?></p>
                    <span>Chat langsung <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>

            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="contact-card contact-email">
                <div class="contact-icon">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <div class="contact-info">
                    <h3>Gmail</h3>
                    <p><?= htmlspecialchars($contact['email']) ?></p>
                    <span>Kirim email <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
        </div>

        <div class="contact-info-box">
            <i class="fa-solid fa-code"></i>
            <p>Class X PPLG 3 &bull; Software Engineering &bull; SMK Informatika</p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
