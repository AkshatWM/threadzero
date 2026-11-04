<?php
// Ensure db.php and functions.php are loaded before rendering the header
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

$page_title = $page_title ?? 'ThreadZero';
$body_class = $body_class ?? 'home-page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= htmlspecialchars($body_class) ?>">

    <header class="home-header">
        <h2>
            <a href="index.php" style="color: white; text-decoration: none;">ThreadZero</a>
        </h2>

        <nav>
            <a href="index.php">Home</a>
            <?php if (isLoggedIn()): ?>
                <a href="profile.php">Profile (@<?= htmlspecialchars($_SESSION['username']) ?>)</a>
                <?php if (isMod()): ?>
                    <a href="admin.php">Mod Panel</a>
                <?php endif; ?>
                <form method="POST" action="auth.php" class="logout-form">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="nav-link-button">Logout</button>
                </form>
            <?php else: ?>
                <a href="auth.php">Login</a>
                <a href="auth.php?mode=register">Register</a>
            <?php endif; ?>
        </nav>
    </header>