<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<aside class="admin-sidebar">
    <h3>Administració</h3>
    <a href="/concessionari-audi/admin/dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="/concessionari-audi/admin/usuaris.php" class="<?= $currentPage === 'usuaris' ? 'active' : '' ?>">👥 Usuaris</a>
    <a href="/concessionari-audi/admin/vehicles.php" class="<?= $currentPage === 'vehicles' ? 'active' : '' ?>">🚗 Vehicles</a>
    <a href="/concessionari-audi/admin/clients.php" class="<?= $currentPage === 'clients' ? 'active' : '' ?>">👤 Clients</a>
    <a href="/concessionari-audi/admin/vendes.php" class="<?= $currentPage === 'vendes' ? 'active' : '' ?>">💰 Vendes</a>
    <a href="/concessionari-audi/admin/cites.php" class="<?= $currentPage === 'cites' ? 'active' : '' ?>">📅 Cites</a>
</aside>
