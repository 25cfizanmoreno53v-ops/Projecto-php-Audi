<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$db = getDB();
$pageTitle = 'Gestió d\'Usuaris — Admin';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$msg = '';
$error = '';

// ── Processar accions POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'create') {
        $nom   = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $rol   = $_POST['rol'] ?? 'usuari';

        if (empty($nom) || empty($email) || empty($pass)) {
            $error = 'Tots els camps són obligatoris.';
            $action = 'create';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuaris (nom, email, contrasenya, rol) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$nom, $email, $hash, $rol]);
                $msg = 'Usuari creat correctament.';
            } catch (PDOException $e) {
                $error = 'Error: aquest email ja existeix.';
                $action = 'create';
            }
        }
    } elseif ($postAction === 'update') {
        $id    = (int)$_POST['id'];
        $nom   = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $rol   = $_POST['rol'] ?? 'usuari';
        $pass  = $_POST['password'] ?? '';

        if (empty($nom) || empty($email)) {
            $error = 'Nom i email són obligatoris.';
            $action = 'edit';
        } else {
            if (!empty($pass)) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuaris SET nom = ?, email = ?, contrasenya = ?, rol = ? WHERE id = ?");
                $stmt->execute([$nom, $email, $hash, $rol, $id]);
            } else {
                $stmt = $db->prepare("UPDATE usuaris SET nom = ?, email = ?, rol = ? WHERE id = ?");
                $stmt->execute([$nom, $email, $rol, $id]);
            }
            $msg = 'Usuari actualitzat correctament.';
        }
    } elseif ($postAction === 'delete') {
        $id = (int)$_POST['id'];
        // No permetre eliminar-se a si mateix
        if ($id == $_SESSION['user_id']) {
            $error = 'No pots eliminar el teu propi compte.';
        } else {
            $stmt = $db->prepare("DELETE FROM usuaris WHERE id = ?");
            $stmt->execute([$id]);
            $msg = 'Usuari eliminat correctament.';
        }
    } elseif ($postAction === 'toggle_role') {
        $id = (int)$_POST['id'];
        $newRol = $_POST['new_rol'] ?? 'usuari';
        $stmt = $db->prepare("UPDATE usuaris SET rol = ? WHERE id = ?");
        $stmt->execute([$newRol, $id]);
        $msg = 'Rol actualitzat correctament.';
    }
}

// ── Obtenir dades ──
$usuaris = $db->query("SELECT * FROM usuaris ORDER BY data_registre DESC")->fetchAll();
$editUser = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM usuaris WHERE id = ?");
    $stmt->execute([$id]);
    $editUser = $stmt->fetch();
    if (!$editUser) { $action = 'list'; }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($action === 'create' || $action === 'edit'): ?>
            <!-- ── Formulari Crear/Editar ── -->
            <div class="page-header">
                <h1><?= $action === 'edit' ? 'Editar Usuari' : 'Nou Usuari' ?></h1>
                <a href="?action=list" class="btn btn-sm btn-outline-dark">← Tornar</a>
            </div>

            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $action === 'edit' ? 'update' : 'create' ?>">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required
                               value="<?= htmlspecialchars($editUser['nom'] ?? $_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($editUser['email'] ?? $_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Contrasenya <?= $action === 'edit' ? '(deixa en blanc per no canviar)' : '' ?></label>
                        <input type="password" id="password" name="password" <?= $action === 'create' ? 'required' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" name="rol">
                            <option value="usuari" <?= ($editUser['rol'] ?? '') === 'usuari' ? 'selected' : '' ?>>Usuari</option>
                            <option value="admin" <?= ($editUser['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-red"><?= $action === 'edit' ? 'Actualitzar' : 'Crear Usuari' ?></button>
                        <a href="?action=list" class="btn btn-outline-dark">Cancel·lar</a>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <!-- ── Llistat ── -->
            <div class="page-header">
                <h1>👥 Gestió d'Usuaris</h1>
                <a href="?action=create" class="btn btn-sm btn-red">+ Nou Usuari</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Registre</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuaris as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="role-badge <?= $u['rol'] ?>"><?= $u['rol'] ?></span></td>
                        <td><?= date('d/m/Y', strtotime($u['data_registre'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <!-- Canviar rol -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_role">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <input type="hidden" name="new_rol" value="<?= $u['rol'] === 'admin' ? 'usuari' : 'admin' ?>">
                                    <button type="submit" class="btn btn-xs <?= $u['rol'] === 'admin' ? 'btn-outline-dark' : 'btn-green' ?>"
                                            title="<?= $u['rol'] === 'admin' ? 'Treure admin' : 'Fer admin' ?>">
                                        <?= $u['rol'] === 'admin' ? '↓ Usuari' : '↑ Admin' ?>
                                    </button>
                                </form>
                                <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-xs btn-black">Editar</a>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Estàs segur?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-danger">Eliminar</button>
                                </form>
                                <?php endif; ?>
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
