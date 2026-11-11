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
    <script>
        // Apply the saved theme before first paint so dark mode does not flash white
        try {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {
            // Storage can be blocked; fall back to the light theme
        }
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
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
                <?php $notification_count = countUnreadNotifications($pdo, $_SESSION['user_id']); ?>
                <a href="notifications.php">
                    Notifications<?php if ($notification_count > 0): ?> (<?= $notification_count ?>)<?php endif; ?>
                </a>
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
            <button type="button" id="theme-toggle" class="theme-toggle-btn" onclick="toggleTheme()" aria-pressed="false">
                Dark Mode
            </button>
        </nav>
    </header>
