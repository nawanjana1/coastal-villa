<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$basePath = $basePath ?? '';
$adminArea = $adminArea ?? false;
$adminPath = $adminArea ? '' : $basePath . 'admin/';

function navEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<header class="site-header">
    <a href="<?= navEscape($basePath) ?>index.php">Coastal Villa</a>

    <nav>
        <a href="<?= navEscape($basePath) ?>rooms.php">Rooms</a>
        <a href="<?= navEscape($basePath) ?>availability.php">Availability</a>
        <a href="<?= navEscape($basePath) ?>index.php#contact">Contact</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Welcome, <?= navEscape($_SESSION['user_name'] ?? '') ?></span>

            <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                <a href="<?= navEscape($adminPath) ?>bookings.php">Manage Bookings</a>
                <a href="<?= navEscape($adminPath) ?>rooms.php">Manage Rooms</a>
            <?php else: ?>
                <a href="<?= navEscape($basePath) ?>my-bookings.php">My Bookings</a>
            <?php endif; ?>

            <a href="<?= navEscape($basePath) ?>logout.php">Logout</a>
        <?php else: ?>
            <a href="<?= navEscape($basePath) ?>login.php">Login</a>
            <a href="<?= navEscape($basePath) ?>register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>


