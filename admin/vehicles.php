<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$pageTitle = 'Gestió de Vehicles — Admin';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$error = '';

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $data = [
        'model'          => trim($_POST['model'] ?? ''),
        'any_fabricacio'  => (int)($_POST['any_fabricacio'] ?? date('Y')),
        'preu'           => (float)($_POST['preu'] ?? 0),
        'quilometres'    => (int)($_POST['quilometres'] ?? 0),
        'combustible'    => $_POST['combustible'] ?? 'gasolina',
        'color'          => trim($_POST['color'] ?? ''),
        'descripcio'     => trim($_POST['descripcio'] ?? ''),
        'imatge'         => trim($_POST['imatge'] ?? ''),
        'disponible'     => isset($_POST['disponible']) ? 1 : 0,
    ];

    if ($postAction === 'create') {
        if (empty($data['model']) || empty($data['color'])) {
            $error = 'Model i color són obligatoris.';
            $action = 'create';
        } else {
            $stmt = $db->prepare("INSERT INTO vehicles (model, any_fabricacio, preu, quilometres, combustible, color, descripcio, imatge, disponible) VALUES (?,?,?,?,?,?,?,?,?)");
            $stmt->execute(array_values($data));
            $msg = 'Vehicle creat correctament.';
        }
    } elseif ($postAction === 'update') {
        $id = (int)$_POST['id'];
        if (empty($data['model']) || empty($data['color'])) {
            $error = 'Model i color són obligatoris.';
            $action = 'edit';
        } else {
            $stmt = $db->prepare("UPDATE vehicles SET model=?, any_fabricacio=?, preu=?, quilometres=?, combustible=?, color=?, descripcio=?, imatge=?, disponible=? WHERE id=?");
            $vals = array_values($data);
            $vals[] = $id;
            $stmt->execute($vals);
            $msg = 'Vehicle actualitzat correctament.';
        }
    } elseif ($postAction === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Vehicle eliminat correctament.';
    }
}

$vehicles = $db->query("SELECT * FROM vehicles ORDER BY id DESC")->fetchAll();
$editItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM vehicles WHERE id = ?");
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
                <h1><?= $action === 'edit' ? 'Editar Vehicle' : 'Nou Vehicle' ?></h1>
                <a href="?action=list" class="btn btn-sm btn-outline-dark">← Tornar</a>
            </div>

            <div class="form-card wide">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Model</label>
                            <input type="text" name="model" required value="<?= htmlspecialchars($v['model'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Any de fabricació</label>
                            <input type="number" name="any_fabricacio" min="2000" max="2030" value="<?= $v['any_fabricacio'] ?? date('Y') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Preu (€)</label>
                            <input type="number" name="preu" step="0.01" min="0" value="<?= $v['preu'] ?? 0 ?>">
                        </div>
                        <div class="form-group">
                            <label>Quilòmetres</label>
                            <input type="number" name="quilometres" min="0" value="<?= $v['quilometres'] ?? 0 ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Combustible</label>
                            <select name="combustible">
                                <?php foreach (['gasolina','dièsel','elèctric','híbrid'] as $c): ?>
                                <option value="<?= $c ?>" <?= ($v['combustible'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" name="color" required value="<?= htmlspecialchars($v['color'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripció</label>
                        <textarea name="descripcio" rows="3"><?= htmlspecialchars($v['descripcio'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>URL Imatge (opcional)</label>
                        <input type="text" name="imatge" value="<?= htmlspecialchars($v['imatge'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="disponible" value="1" <?= ($v['disponible'] ?? 1) ? 'checked' : '' ?>>
                            Disponible
                        </label>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-red"><?= $action === 'edit' ? 'Actualitzar' : 'Crear Vehicle' ?></button>
                        <a href="?action=list" class="btn btn-outline-dark">Cancel·lar</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="page-header">
                <h1>🚗 Gestió de Vehicles</h1>
                <a href="?action=create" class="btn btn-sm btn-red">+ Nou Vehicle</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Model</th><th>Any</th><th>Preu</th><th>Km</th><th>Combustible</th><th>Color</th><th>Disp.</th><th>Accions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): ?>
                    <tr>
                        <td><?= $v['id'] ?></td>
                        <td><strong><?= htmlspecialchars($v['model']) ?></strong></td>
                        <td><?= $v['any_fabricacio'] ?></td>
                        <td><?= number_format($v['preu'], 2, ',', '.') ?> €</td>
                        <td><?= number_format($v['quilometres'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($v['combustible']) ?></td>
                        <td><?= htmlspecialchars($v['color']) ?></td>
                        <td><span class="badge-disponible <?= $v['disponible'] ? 'si' : 'no' ?>"><?= $v['disponible'] ? 'Sí' : 'No' ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a href="?action=edit&id=<?= $v['id'] ?>" class="btn btn-xs btn-black">Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar aquest vehicle?')">
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
