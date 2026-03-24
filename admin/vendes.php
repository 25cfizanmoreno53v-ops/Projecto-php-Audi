<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$pageTitle = 'Gestió de Vendes — Admin';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$error = '';

// Dades per als selects
$vehiclesList = $db->query("SELECT id, model, preu FROM vehicles ORDER BY model")->fetchAll();
$clientsList   = $db->query("SELECT id, nom, cognoms FROM clients ORDER BY nom")->fetchAll();
$venedorsList  = $db->query("SELECT id, nom FROM usuaris WHERE rol = 'admin' ORDER BY nom")->fetchAll();

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $data = [
        'vehicle_id'      => (int)($_POST['vehicle_id'] ?? 0),
        'client_id'       => (int)($_POST['client_id'] ?? 0),
        'venedor_id'      => (int)($_POST['venedor_id'] ?? 0),
        'preu_final'      => (float)($_POST['preu_final'] ?? 0),
        'data_venda'      => $_POST['data_venda'] ?? date('Y-m-d'),
        'metode_pagament' => $_POST['metode_pagament'] ?? 'efectiu',
        'observacions'    => trim($_POST['observacions'] ?? ''),
    ];

    if ($postAction === 'create') {
        if ($data['vehicle_id'] === 0 || $data['client_id'] === 0 || $data['venedor_id'] === 0) {
            $error = 'Vehicle, client i venedor són obligatoris.';
            $action = 'create';
        } else {
            $stmt = $db->prepare("INSERT INTO vendes (vehicle_id, client_id, venedor_id, preu_final, data_venda, metode_pagament, observacions) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute(array_values($data));
            // Marcar vehicle com no disponible
            $db->prepare("UPDATE vehicles SET disponible = 0 WHERE id = ?")->execute([$data['vehicle_id']]);
            $msg = 'Venda registrada correctament.';
        }
    } elseif ($postAction === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE vendes SET vehicle_id=?, client_id=?, venedor_id=?, preu_final=?, data_venda=?, metode_pagament=?, observacions=? WHERE id=?");
        $vals = array_values($data);
        $vals[] = $id;
        $stmt->execute($vals);
        $msg = 'Venda actualitzada correctament.';
    } elseif ($postAction === 'delete') {
        $id = (int)$_POST['id'];
        // Tornar vehicle a disponible
        $vId = $db->prepare("SELECT vehicle_id FROM vendes WHERE id = ?");
        $vId->execute([$id]);
        $vehicleId = $vId->fetchColumn();
        if ($vehicleId) {
            $db->prepare("UPDATE vehicles SET disponible = 1 WHERE id = ?")->execute([$vehicleId]);
        }
        $db->prepare("DELETE FROM vendes WHERE id = ?")->execute([$id]);
        $msg = 'Venda eliminada correctament.';
    }
}

$vendes = $db->query("
    SELECT v.*, ve.model, c.nom AS client_nom, c.cognoms AS client_cognoms, u.nom AS venedor_nom
    FROM vendes v
    JOIN vehicles ve ON v.vehicle_id = ve.id
    JOIN clients c ON v.client_id = c.id
    JOIN usuaris u ON v.venedor_id = u.id
    ORDER BY v.data_venda DESC
")->fetchAll();

$editItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM vendes WHERE id = ?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
    if (!$editItem) $action = 'list';
}

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($action === 'create' || $action === 'edit'):
            $v = $editItem ?? $_POST ?? [];
        ?>
            <div class="page-header">
                <h1><?= $action === 'edit' ? 'Editar Venda' : 'Nova Venda' ?></h1>
                <a href="?action=list" class="btn btn-sm btn-outline-dark">← Tornar</a>
            </div>

            <div class="form-card wide">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Vehicle</label>
                            <select name="vehicle_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($vehiclesList as $ve): ?>
                                <option value="<?= $ve['id'] ?>" <?= ($v['vehicle_id'] ?? '') == $ve['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ve['model']) ?> (<?= number_format($ve['preu'], 0, ',', '.') ?> €)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Client</label>
                            <select name="client_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($clientsList as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($v['client_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['nom'] . ' ' . $cl['cognoms']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Venedor</label>
                            <select name="venedor_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($venedorsList as $vn): ?>
                                <option value="<?= $vn['id'] ?>" <?= ($v['venedor_id'] ?? '') == $vn['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($vn['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Preu Final (€)</label>
                            <input type="number" name="preu_final" step="0.01" min="0" required value="<?= $v['preu_final'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data de Venda</label>
                            <input type="date" name="data_venda" required value="<?= $v['data_venda'] ?? date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label>Mètode de Pagament</label>
                            <select name="metode_pagament">
                                <?php foreach (['efectiu','finançament','transferència'] as $m): ?>
                                <option value="<?= $m ?>" <?= ($v['metode_pagament'] ?? '') === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Observacions</label>
                        <textarea name="observacions" rows="3"><?= htmlspecialchars($v['observacions'] ?? '') ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-red"><?= $action === 'edit' ? 'Actualitzar' : 'Registrar Venda' ?></button>
                        <a href="?action=list" class="btn btn-outline-dark">Cancel·lar</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="page-header">
                <h1>💰 Gestió de Vendes</h1>
                <a href="?action=create" class="btn btn-sm btn-red">+ Nova Venda</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Vehicle</th><th>Client</th><th>Venedor</th><th>Preu</th><th>Data</th><th>Pagament</th><th>Accions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($vendes as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td><strong><?= htmlspecialchars($v['model']) ?></strong></td>
                        <td><?= htmlspecialchars($v['client_nom'] . ' ' . $v['client_cognoms']) ?></td>
                        <td><?= htmlspecialchars($v['venedor_nom']) ?></td>
                        <td><strong><?= number_format($v['preu_final'], 2, ',', '.') ?> €</strong></td>
                        <td><?= date('d/m/Y', strtotime($v['data_venda'])) ?></td>
                        <td><?= htmlspecialchars($v['metode_pagament']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="?action=edit&id=<?= $v['id'] ?>" class="btn btn-xs btn-black">Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar aquesta venda?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
