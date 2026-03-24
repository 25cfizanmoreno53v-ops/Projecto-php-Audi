<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$pageTitle = 'Gestió de Clients — Admin';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$error = '';

// ── POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    $data = [
        'nom'     => trim($_POST['nom'] ?? ''),
        'cognoms' => trim($_POST['cognoms'] ?? ''),
        'telefon' => trim($_POST['telefon'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
        'dni'     => trim($_POST['dni'] ?? ''),
        'adreca'  => trim($_POST['adreca'] ?? ''),
    ];

    if ($postAction === 'create') {
        if (empty($data['nom']) || empty($data['cognoms']) || empty($data['dni'])) {
            $error = 'Nom, cognoms i DNI són obligatoris.';
            $action = 'create';
        } else {
            $stmt = $db->prepare("INSERT INTO clients (nom, cognoms, telefon, email, dni, adreca) VALUES (?,?,?,?,?,?)");
            try {
                $stmt->execute(array_values($data));
                $msg = 'Client creat correctament.';
            } catch (PDOException $e) {
                $error = 'Error: aquest DNI ja existeix.';
                $action = 'create';
            }
        }
    } elseif ($postAction === 'update') {
        $id = (int)$_POST['id'];
        if (empty($data['nom']) || empty($data['cognoms']) || empty($data['dni'])) {
            $error = 'Nom, cognoms i DNI són obligatoris.';
            $action = 'edit';
        } else {
            $stmt = $db->prepare("UPDATE clients SET nom=?, cognoms=?, telefon=?, email=?, dni=?, adreca=? WHERE id=?");
            $vals = array_values($data);
            $vals[] = $id;
            $stmt->execute($vals);
            $msg = 'Client actualitzat correctament.';
        }
    } elseif ($postAction === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        $msg = 'Client eliminat correctament.';
    }
}

$clients = $db->query("SELECT * FROM clients ORDER BY id DESC")->fetchAll();
$editItem = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
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
            $c = $editItem ?? $_POST ?? [];
        ?>
            <div class="page-header">
                <h1><?= $action === 'edit' ? 'Editar Client' : 'Nou Client' ?></h1>
                <a href="?action=list" class="btn btn-sm btn-outline-dark">← Tornar</a>
            </div>

            <div class="form-card wide">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($editItem): ?><input type="hidden" name="id" value="<?= $editItem['id'] ?>"><?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" name="nom" required value="<?= htmlspecialchars($c['nom'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Cognoms</label>
                            <input type="text" name="cognoms" required value="<?= htmlspecialchars($c['cognoms'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>DNI</label>
                            <input type="text" name="dni" required value="<?= htmlspecialchars($c['dni'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Telèfon</label>
                            <input type="text" name="telefon" value="<?= htmlspecialchars($c['telefon'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($c['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Adreça</label>
                        <textarea name="adreca" rows="2"><?= htmlspecialchars($c['adreca'] ?? '') ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-red"><?= $action === 'edit' ? 'Actualitzar' : 'Crear Client' ?></button>
                        <a href="?action=list" class="btn btn-outline-dark">Cancel·lar</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="page-header">
                <h1>👤 Gestió de Clients</h1>
                <a href="?action=create" class="btn btn-sm btn-red">+ Nou Client</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Nom</th><th>Cognoms</th><th>DNI</th><th>Telèfon</th><th>Email</th><th>Accions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><strong><?= htmlspecialchars($c['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($c['cognoms']) ?></td>
                        <td><?= htmlspecialchars($c['dni']) ?></td>
                        <td><?= htmlspecialchars($c['telefon']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="?action=edit&id=<?= $c['id'] ?>" class="btn btn-xs btn-black">Editar</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Eliminar aquest client?')">
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
