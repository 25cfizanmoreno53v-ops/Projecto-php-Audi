<?php
require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Catàleg de Vehicles — Audi';

$db = getDB();

// Filtres
$combustible = $_GET['combustible'] ?? '';
$where = "WHERE disponible = 1";
$params = [];
if (!empty($combustible)) {
    $where .= " AND combustible = ?";
    $params[] = $combustible;
}

$stmt = $db->prepare("SELECT * FROM vehicles $where ORDER BY preu ASC");
$stmt->execute($params);
$vehicles = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="page-header" style="margin-top: 8px;">
        <div>
            <h1 class="section-title">Catàleg de Vehicles</h1>
            <p class="section-subtitle" style="margin-bottom:0">Descobreix tots els vehicles disponibles al nostre concessionari</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="toolbar">
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="?combustible=" class="btn btn-sm <?= $combustible === '' ? 'btn-black' : 'btn-outline-dark' ?>">Tots</a>
            <a href="?combustible=gasolina" class="btn btn-sm <?= $combustible === 'gasolina' ? 'btn-black' : 'btn-outline-dark' ?>">Gasolina</a>
            <a href="?combustible=dièsel" class="btn btn-sm <?= $combustible === 'dièsel' ? 'btn-black' : 'btn-outline-dark' ?>">Dièsel</a>
            <a href="?combustible=elèctric" class="btn btn-sm <?= $combustible === 'elèctric' ? 'btn-black' : 'btn-outline-dark' ?>">Elèctric</a>
            <a href="?combustible=híbrid" class="btn btn-sm <?= $combustible === 'híbrid' ? 'btn-black' : 'btn-outline-dark' ?>">Híbrid</a>
        </div>
        <span style="color: #999; font-size: 0.85rem;"><?= count($vehicles) ?> vehicle(s)</span>
    </div>

    <?php if (count($vehicles) > 0): ?>
    <div class="vehicle-grid">
        <?php foreach ($vehicles as $v): ?>
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
    <?php else: ?>
    <div class="empty-state">
        <div class="icon">🚗</div>
        <p>No s'han trobat vehicles amb els filtres seleccionats.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
