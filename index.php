<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Concessionari Audi — Inici';

$db = getDB();
$featured = $db->query("SELECT * FROM vehicles WHERE disponible = 1 ORDER BY id DESC LIMIT 3")->fetchAll();
$totalVehicles = $db->query("SELECT COUNT(*) FROM vehicles WHERE disponible = 1")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero" style="margin-top: -32px;">
    <div class="hero-content">
        <h1>Audi <span>Premium</span></h1>
        <p>El teu concessionari oficial Audi de confiança</p>
        <div class="btn-group">
            <a href="/concessionari-audi/public/cataleg.php" class="btn btn-red">Veure Catàleg</a>
            <?php if (!isLoggedIn()): ?>
                <a href="/concessionari-audi/login.php" class="btn btn-outline">Iniciar Sessió</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Stats -->
<div class="container">
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-number"><?= $totalVehicles ?></div>
            <div class="stat-label">Vehicles Disponibles</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">5</div>
            <div class="stat-label">Anys d'Experiència</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">100%</div>
            <div class="stat-label">Garantia Oficial</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Atenció al Client</div>
        </div>
    </div>
</div>

<!-- Featured Vehicles -->
<div class="container" style="padding-bottom: 48px;">
    <h2 class="section-title">Vehicles Destacats</h2>
    <p class="section-subtitle">Descobreix els nostres últims models disponibles</p>

    <div class="vehicle-grid">
        <?php foreach ($featured as $v): ?>
        <div class="card vehicle-card">
            <div class="vehicle-img">
                🚗
                <span class="price-tag"><?= number_format($v['preu'], 0, ',', '.') ?> €</span>
            </div>
            <div class="card-body">
                <h3><?= htmlspecialchars($v['model']) ?></h3>
                <div class="specs">
                    <span class="spec-badge"><?= $v['any_fabricacio'] ?></span>
                    <span class="spec-badge"><?= htmlspecialchars($v['combustible']) ?></span>
                    <span class="spec-badge"><?= htmlspecialchars($v['color']) ?></span>
                    <span class="spec-badge"><?= number_format($v['quilometres'], 0, ',', '.') ?> km</span>
                </div>
                <p class="vehicle-desc"><?= htmlspecialchars($v['descripcio']) ?></p>
                <a href="/concessionari-audi/public/detall-vehicle.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-black">Veure Detalls</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 32px;">
        <a href="/concessionari-audi/public/cataleg.php" class="btn btn-red">Veure Tot el Catàleg →</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
