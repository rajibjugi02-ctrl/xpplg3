<?php
require_once 'includes/db.php';
$page_title = "Struktur Organisasi - X PPLG 3";
$active_page = "structure";

// Fetch structure from DB, fallback to static data if table is empty
$members = $pdo->query("SELECT * FROM structure ORDER BY order_num ASC")->fetchAll();

// Static fallback
if (empty($members)) {
    $members = [
        ['id'=>1,'role'=>'Wali Kelas','name'=>'Pa Firman Sidik','photo'=>'','order_num'=>1],
        ['id'=>2,'role'=>'Ketua Kelas','name'=>'Bagus Pambudi','photo'=>'','order_num'=>2],
        ['id'=>3,'role'=>'Wakil Ketua','name'=>'Nadine','photo'=>'','order_num'=>3],
        ['id'=>4,'role'=>'Sekretaris 1','name'=>'Salsabilla','photo'=>'','order_num'=>4],
        ['id'=>5,'role'=>'Sekretaris 2','name'=>'Rafli','photo'=>'','order_num'=>5],
        ['id'=>6,'role'=>'Bendahara 1','name'=>'Oktavia','photo'=>'','order_num'=>6],
        ['id'=>7,'role'=>'Bendahara 2','name'=>'Faneezza','photo'=>'','order_num'=>7],
        ['id'=>8,'role'=>'PDD','name'=>'Rajib Zahir','photo'=>'','order_num'=>8],
    ];
}

function getAvatarUrl($name, $role) {
    $colors = [
        'Wali Kelas'   => '2563eb/ffffff',
        'Ketua Kelas'  => '0f172a/ffffff',
        'Wakil Ketua'  => '0f172a/ffffff',
        'Sekretaris 1' => '64748b/ffffff',
        'Sekretaris 2' => '64748b/ffffff',
        'Bendahara 1'  => '64748b/ffffff',
        'Bendahara 2'  => '64748b/ffffff',
        'PDD'          => '64748b/ffffff',
    ];
    $clr = $colors[$role] ?? '94a3b8/ffffff';
    $initials = urlencode($name);
    return "https://ui-avatars.com/api/?name={$initials}&background={$clr}&size=200&bold=true&font-size=0.35";
}

function getByRole($members, $role) {
    foreach ($members as $m) {
        if ($m['role'] === $role) return $m;
    }
    return null;
}
function getByRoles($members, ...$roles) {
    $res = [];
    foreach ($members as $m) {
        if (in_array($m['role'], $roles)) $res[] = $m;
    }
    return $res;
}

$wali      = getByRole($members, 'Wali Kelas');
$ketua     = getByRole($members, 'Ketua Kelas');
$wakil     = getByRole($members, 'Wakil Ketua');
$sekList   = getByRoles($members, 'Sekretaris 1', 'Sekretaris 2');
$benList   = getByRoles($members, 'Bendahara 1', 'Bendahara 2');
$pdd       = getByRole($members, 'PDD');

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/structure.css">

<main>
    <div class="container">
        <div class="page-header reveal">
            <h1 class="page-title">Struktur <span>Organisasi</span></h1>
            <p class="page-subtitle">Class X PPLG 3 &bull; Software Engineering</p>
        </div>

        <div class="org-chart reveal">
            <!-- Level 1: Wali Kelas -->
            <?php if ($wali): ?>
            <div class="org-row">
                <?= renderCard($wali, 'primary') ?>
            </div>
            <div class="org-connector-v"></div>
            <?php endif; ?>

            <!-- Level 2: Ketua & Wakil -->
            <?php if ($ketua || $wakil): ?>
            <div class="org-row-multi">
                <div class="org-horizontal-line"></div>
                <?php if ($ketua) echo renderCard($ketua); ?>
                <?php if ($wakil) echo renderCard($wakil); ?>
            </div>
            <div class="org-connector-v"></div>
            <?php endif; ?>

            <!-- Level 3: Sekretaris & Bendahara -->
            <div class="org-row-multi multi-4">
                <div class="org-horizontal-line"></div>
                <?php foreach ($sekList as $s) echo renderCard($s); ?>
                <?php foreach ($benList as $b) echo renderCard($b); ?>
            </div>

            <!-- Level 4: PDD -->
            <?php if ($pdd): ?>
            <div class="org-connector-v"></div>
            <div class="org-row">
                <?= renderCard($pdd, '', 'Publikasi, Dekorasi, Dokumentasi') ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
function renderCard($member, $variant = '', $desc = '') {
    $photo = !empty($member['photo']) ? 'assets/uploads/structure/' . $member['photo'] : getAvatarUrl($member['name'], $member['role']);
    $borderClass = $variant === 'primary' ? 'org-card-primary' : '';
    $roleClass = $variant === 'primary' ? 'text-primary' : '';
    ob_start();
    ?>
    <div class="org-node-wrapper">
        <div class="org-card <?= $borderClass ?>">
            <img src="<?= $photo ?>" alt="<?= htmlspecialchars($member['name']) ?>" class="org-photo">
            <h4><?= htmlspecialchars($member['name']) ?></h4>
            <p class="org-role <?= $roleClass ?>"><?= htmlspecialchars($member['role']) ?></p>
            <?php if ($desc): ?><p class="org-desc"><?= htmlspecialchars($desc) ?></p><?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

include 'includes/footer.php';
?>
