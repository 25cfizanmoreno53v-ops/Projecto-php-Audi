<?php
require_once __DIR__ . '/includes/auth.php';

// Si ja està autenticat, redirigir
if (isLoggedIn()) {
    header('Location: /concessionari-audi/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Si us plau, omple tots els camps.';
    } elseif (loginUser($email, $password)) {
        header('Location: /concessionari-audi/index.php');
        exit;
    } else {
        $error = 'Email o contrasenya incorrectes.';
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sessió — Audi</title>
    <link rel="stylesheet" href="/concessionari-audi/css/style.css">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="logo-text">AUDI</div>
            <p>Accedeix al teu compte</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Correu electrònic</label>
                <input type="email" id="email" name="email" placeholder="exemple@audi.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-red">Iniciar Sessió</button>
        </form>

        <div class="login-footer">
            No tens compte? <a href="/concessionari-audi/registre.php">Registra't</a>
        </div>
    </div>
</body>
</html>
