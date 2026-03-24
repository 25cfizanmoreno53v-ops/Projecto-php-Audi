<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$pageTitle = 'Gestió de Cites — Admin';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$error = '';

$vehiclesList = $db->query("SELECT id, model FROM vehicles WHERE disponible = 1 ORDER BY model")->fetchAll();
$clientsList  = $db->query("SELECT id, nom, cognoms FROM clients ORDER BY nom")->fetchAll();

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $data = [
        'client_id'  => (int)($_POST['client_id'] ?? 0),
        'vehicle_id' => (int)($_POST['vehicle_id'] ?? 0),
        'data_cita'  => $_POST['data_cita'] ?? '',
        'tipus'      => $_POST['tipus'] ?? 'consulta',
        'estat'      => $_POST['estat'] ?? 'pendent',
        'notes'      => trim($_POST['notes'] ?? ''),
    ];

    if ($postAction === 'create') {
        if ($data['client_id'] === 0 || $data['vehicle_id'] === 0 || empty($data['data_cita'])) {
            $error = 'Client, vehicle i data són obligatoris.';
            $action = 'create';
        } else {
            $stmt = $db->prepare("INSERT INTO cites (client_id, vehicle_id, data_cita, tipus, estat, notes) VALUES (?,?,?,?,?,?)");
            $stmt->execute(array_values($data));
            $msg = 'Cita creada correctament.';
        }
    } elseif ($postAction === 'update') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("UPDATE cites SET client_id=?, vehicle_id=?, data_cita=?, tipus=?, estat=?, notes=? WHERE id=?");
        $vals = array_values($data);
        $vals[] = $id;
        $stmt->execute($vals);
        $msg = 'Cita actualitzada correctament.';
    } elseif ($postAction === 'delete') {
        $id = (int)$_POST['id'];
        $db->prepare("DELETE FROM cites WHERE id = ?")->execute([$id]);
        $msg = 'Cita eliminada correctament.';
    }
}

$cites = $db->query("
    SELECT ci.*, c.nom AS client_nom, c.cognoms AS client_cognoms, ve.model
    FROM cites ci
    JOIN clients c ON ci.client_id = c.id
    JOIN vehicles ve ON ci.vehicle_id = ve.id
    ORDER BY ci.data_cita DESC
")->fetchAll();

$editItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM cites WHERE id = ?");
    $stmt->execute([$id]);
    $editItem = $stmt->fetch();
    if (!$editItem) $action = 'list';
    // Per editar, afegir tots els vehicles (no només disponibles)
    $vehiclesList = $db->query("SELECT id, model FROM vehicles ORDER BY model")->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($action === 'create' || $action === 'edit'):
            $c = $editItem ?? $_POST ?? [];
        ?>
            <div class="page-header">
                <h1><?= $action === 'edit' ? 'Editar Cita' : 'Nova Cita' ?></h1>
                <a href="?action=list" class="btn btn-sm btn-outline-dark">← Tornar</a>
            </div>

            <div class="form-card wide">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Client</label>
                            <select name="client_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($clientsList as $cl): ?>
                                <option value="<?= $cl['id'] ?>" <?= ($c['client_id'] ?? '') == $cl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cl['nom'] . ' ' . $cl['cognoms']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Vehicle</label>
                            <select name="vehicle_id" required>
                                <option value="">-- Selecciona --</option>
                                <?php foreach ($vehiclesList as $ve): ?>
                                <option value="<?= $ve['id'] ?>" <?= ($c['vehicle_id'] ?? '') == $ve['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ve['model']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Data i Hora</label>
                            <input type="datetime-local" name="data_cita" required
                                   value="<?= !empty($c['data_cita']) ? date('Y-m-d\TH:i', strtotime($c['data_cita'])) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label>Tipus</label>
                            <select name="tipus">
                                <?php foreach (['prova','revisió','consulta'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($c['tipus'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Estat</label>
                        <select name="estat">
                            <?php foreach (['pendent','confirmada','completada','cancel·lada'] as $e): ?>
                            <option value="<?= $e ?>" <?= ($c['estat'] ?? 'pendent') === $e ? 'selected' : '' ?>><?= ucfirst($e) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" rows="3"><?= htmlspecialchars($c['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-red"><?= $action === 'edit' ? 'Actualitzar' : 'Crear Cita' ?></button>
                        <a href="?action=list" class="btn btn-outline-dark">Cancel·lar</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="page-header">
                <h1>📅 Gestió de Cites</h1>
                <a href="?action=create" class="btn btn-sm btn-red">+ Nova Cita</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Client</th><th>Vehicle</th><th>Data</th><th>Tipus</th><th>Estat</th><th>Accions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cites as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><strong><?= htmlspecialchars($c['client_nom'] . ' ' . $c['client_cognoms']) ?></strong></td>
                        <td><?= htmlspecialchars($c['model']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($c['data_cita'])) ?></td>
                        <td><?= htmlspecialchars($c['tipus']) ?></td>
                        <td><span class="estat-badge <?= str_replace('·', '-', $c['estat']) ?>"><?= htmlspecialchars($c['estat']) ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a href="?action=edit&id=<?= $c['id'] ?>" class="btn btn-xs btn-black">Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar aquesta cita?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
