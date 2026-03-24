<?php
require_once __DIR__ . '/../includes/auth.php';

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /concessionari-audi/public/cataleg.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: /concessionari-audi/public/cataleg.php');
    exit;
}

$pageTitle = htmlspecialchars($vehicle['model']) . ' — Audi';

// Vehicles similars
$stmt = $db->prepare("SELECT * FROM vehicles WHERE id != ? AND disponible = 1 AND combustible = ? LIMIT 3");
$stmt->execute([$id, $vehicle['combustible']]);
$similars = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 8px;">
    <a href="/concessionari-audi/public/cataleg.php" style="color: #999; font-size: 0.85rem;">← Tornar al catàleg</a>

    <div class="detail-grid" style="margin-top: 20px;">
        <div class="detail-img">🚗</div>
        <div class="detail-info">
            <h1><?= htmlspecialchars($vehicle['model']) ?></h1>
            <div class="price"><?= number_format($vehicle['preu'], 2, ',', '.') ?> €</div>

            <?php if ($vehicle['disponible']): ?>
                <span class="badge-disponible si">Disponible</span>
            <?php else: ?>
                <span class="badge-disponible no">Venut</span>
            <?php endif; ?>

            <div class="detail-specs">
                <div class="detail-spec">
                    <div class="label">Any</div>
                    <div class="value"><?= $vehicle['any_fabricacio'] ?></div>
                </div>
                <div class="detail-spec">
                    <div class="label">Quilòmetres</div>
                    <div class="value"><?= number_format($vehicle['quilometres'], 0, ',', '.') ?> km</div>
                </div>
                <div class="detail-spec">
                    <div class="label">Combustible</div>
                    <div class="value"><?= htmlspecialchars($vehicle['combustible']) ?></div>
                </div>
                <div class="detail-spec">
                    <div class="label">Color</div>
                    <div class="value"><?= htmlspecialchars($vehicle['color']) ?></div>
                </div>
            </div>

            <?php if (!empty($vehicle['descripcio'])): ?>
            <h3 style="margin-top: 20px; font-size: 1rem; color: #000;">Descripció</h3>
            <p style="color: #666; margin-top: 8px;"><?= nl2br(htmlspecialchars($vehicle['descripcio'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vehicles similars -->
    <?php if (count($similars) > 0): ?>
    <div style="margin-top: 48px; padding-bottom: 32px;">
        <h2 class="section-title">Vehicles Similars</h2>
        <div class="vehicle-grid" style="margin-top: 20px;">
            <?php foreach ($similars as $v): ?>
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
                    </div>
                    <a href="?id=<?= $v['id'] ?>" class="btn btn-sm btn-black">Veure Detalls</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
