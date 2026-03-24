<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_user = currentUser();
$_isAdmin = isAdmin();
$_isLogged = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Concessionari Audi' ?></title>
    <link rel="stylesheet" href="/concessionari-audi/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="/concessionari-audi/index.php" class="logo">
            <span class="logo-rings">&#9679;&#9679;&#9679;&#9679;</span>
            <span class="logo-text">AUDI</span>
        </a>
        <nav class="main-nav">
            <a href="/concessionari-audi/index.php">Inici</a>
            <a href="/concessionari-audi/public/cataleg.php">Catàleg</a>
            <?php if ($_isAdmin): ?>
                <a href="/concessionari-audi/admin/dashboard.php">Panell Admin</a>
            <?php endif; ?>
        </nav>
        <div class="header-actions">
            <?php if ($_isLogged): ?>
                <span class="user-badge <?= $_user['rol'] ?>"><?= htmlspecialchars($_user['nom']) ?> (<?= $_user['rol'] ?>)</span>
                <a href="/concessionari-audi/logout.php" class="btn btn-sm btn-outline">Sortir</a>
            <?php else: ?>
                <a href="/concessionari-audi/login.php" class="btn btn-sm btn-red">Iniciar Sessió</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
