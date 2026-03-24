<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$pageTitle = 'Dashboard — Admin';

$totalVehicles   = $db->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
$vehiclesDisp    = $db->query("SELECT COUNT(*) FROM vehicles WHERE disponible = 1")->fetchColumn();
$totalClients    = $db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$totalVendes     = $db->query("SELECT COUNT(*) FROM vendes")->fetchColumn();
$totalCites      = $db->query("SELECT COUNT(*) FROM cites")->fetchColumn();
$citesPendents   = $db->query("SELECT COUNT(*) FROM cites WHERE estat = 'pendent'")->fetchColumn();
$totalUsuaris    = $db->query("SELECT COUNT(*) FROM usuaris")->fetchColumn();

// Últimes vendes
$ultimesVendes = $db->query("
    SELECT v.id, ve.model, c.nom, c.cognoms, v.preu_final, v.data_venda
    FROM vendes v
    JOIN vehicles ve ON v.vehicle_id = ve.id
    JOIN clients c ON v.client_id = c.id
    ORDER BY v.data_venda DESC LIMIT 5
")->fetchAll();

// Properes cites
$properesCites = $db->query("
    SELECT ci.id, c.nom, c.cognoms, ve.model, ci.data_cita, ci.tipus, ci.estat
    FROM cites ci
    JOIN clients c ON ci.client_id = c.id
    JOIN vehicles ve ON ci.vehicle_id = ve.id
    WHERE ci.estat IN ('pendent', 'confirmada')
    ORDER BY ci.data_cita ASC LIMIT 5
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <h1>Dashboard</h1>
        <p class="subtitle">Resum general del concessionari</p>

        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?= $totalVehicles ?></div>
                <div class="stat-label">Vehicles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $vehiclesDisp ?></div>
                <div class="stat-label">Disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalClients ?></div>
                <div class="stat-label">Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalVendes ?></div>
                <div class="stat-label">Vendes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $citesPendents ?></div>
                <div class="stat-label">Cites Pendents</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalUsuaris ?></div>
                <div class="stat-label">Usuaris</div>
            </div>
        </div>

        <!-- Últimes vendes -->
        <h3 style="margin: 32px 0 16px; color: #000;">Últimes Vendes</h3>
        <?php if (count($ultimesVendes) > 0): ?>
        <table class="data-table">
            <thead>
                <tr><th>Vehicle</th><th>Client</th><th>Preu</th><th>Data</th></tr>
            </thead>
            <tbody>
                <?php foreach ($ultimesVendes as $v): ?>
                <tr>
                    <td><?= htmlspecialchars($v['model']) ?></td>
                    <td><?= htmlspecialchars($v['nom'] . ' ' . $v['cognoms']) ?></td>
                    <td><strong><?= number_format($v['preu_final'], 2, ',', '.') ?> €</strong></td>
                    <td><?= date('d/m/Y', strtotime($v['data_venda'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state"><p>No hi ha vendes registrades.</p></div>
        <?php endif; ?>

        <!-- Properes cites -->
        <h3 style="margin: 32px 0 16px; color: #000;">Properes Cites</h3>
        <?php if (count($properesCites) > 0): ?>
        <table class="data-table">
            <thead>
                <tr><th>Client</th><th>Vehicle</th><th>Data</th><th>Tipus</th><th>Estat</th></tr>
            </thead>
            <tbody>
                <?php foreach ($properesCites as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nom'] . ' ' . $c['cognoms']) ?></td>
                    <td><?= htmlspecialchars($c['model']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($c['data_cita'])) ?></td>
                    <td><?= htmlspecialchars($c['tipus']) ?></td>
                    <td><span class="estat-badge <?= str_replace('·', '-', $c['estat']) ?>"><?= htmlspecialchars($c['estat']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state"><p>No hi ha cites properes.</p></div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
