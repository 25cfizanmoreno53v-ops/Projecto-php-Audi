<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: /concessionari-audi/index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (empty($nom) || empty($email) || empty($password)) {
        $error = 'Si us plau, omple tots els camps.';
    } elseif ($password !== $confirm) {
        $error = 'Les contrasenyes no coincideixen.';
    } elseif (strlen($password) < 6) {
        $error = 'La contrasenya ha de tenir almenys 6 caràcters.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuaris WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Ja existeix un usuari amb aquest email.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO usuaris (nom, email, contrasenya, rol) VALUES (?, ?, ?, 'usuari')");
            $stmt->execute([$nom, $email, $hash]);
            $success = 'Compte creat correctament! Ara pots iniciar sessió.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre — Audi</title>
    <link rel="stylesheet" href="/concessionari-audi/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-text">AUDI</div>
            <p>Crea el teu compte</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" placeholder="El teu nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Correu electrònic</label>
                <input type="email" id="email" name="email" placeholder="exemple@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input type="password" id="password" name="password" placeholder="Mínim 6 caràcters" required>
            </div>
            <div class="form-group">
                <label for="confirm">Confirmar contrasenya</label>
                <input type="password" id="confirm" name="confirm" placeholder="Repeteix la contrasenya" required>
            </div>
            <button type="submit" class="btn btn-red">Crear Compte</button>
        </form>

        <div class="login-footer">
            Ja tens compte? <a href="/concessionari-audi/login.php">Inicia sessió</a>
        </div>
    </div>
</body>
</html>
