<?php
/**
 * Funcions d'autenticació i control d'accés
 */

session_start();

require_once __DIR__ . '/../config/db.php';

/**
 * Comprova si l'usuari ha iniciat sessió
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Comprova si l'usuari és administrador
 */
function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['user_rol'] ?? '') === 'admin';
}

/**
 * Retorna les dades de l'usuari actual
 */
function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id'  => $_SESSION['user_id'],
        'nom' => $_SESSION['user_nom'],
        'email' => $_SESSION['user_email'],
        'rol' => $_SESSION['user_rol'],
    ];
}

/**
 * Requereix autenticació - redirigeix al login si no està autenticat
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /concessionari-audi/login.php');
        exit;
    }
}

/**
 * Requereix rol admin - redirigeix si no és admin
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /concessionari-audi/index.php');
        exit;
    }
}

/**
 * Inicia sessió per a un usuari
 */
function loginUser(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuaris WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['contrasenya'])) {
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_nom']   = $user['nom'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_rol']   = $user['rol'];
        return true;
    }
    return false;
}

/**
 * Tanca la sessió
 */
function logoutUser(): void {
    session_destroy();
    header('Location: /concessionari-audi/login.php');
    exit;
}
