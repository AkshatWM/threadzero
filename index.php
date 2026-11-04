<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// [SQL SELECT] Load the seeded board list for the landing page
$sql = "SELECT slug, title, description FROM boards ORDER BY id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$boards = $stmt->fetchAll();

$page_title = 'ThreadZero - Home';
$body_class = 'home-page';
include 'includes/header.php';
?>

<section class="welcome-box">
    <h1>Welcome to ThreadZero</h1>
    <p>Share ideas. Stay anonymous.</p>

    <?php if (isLoggedIn()): ?>
        <p>Signed in as <strong>@<?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        <a href="profile.php" class="home-button">Your Profile</a>
    <?php else: ?>
        <a href="auth.php?mode=register" class="home-button">Join the Community</a>
    <?php endif; ?>
</section>

<main class="home-content">
    <h2>Explore Boards</h2>

    <div class="board-list">
        <?php foreach ($boards as $board): ?>
            <div class="board-box">
                <h3><?= htmlspecialchars($board['title']) ?></h3>
                <p><?= htmlspecialchars($board['description']) ?></p>
                <span class="board-soon">/<?= htmlspecialchars($board['slug']) ?>/ feed coming in Module 2</span>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!isLoggedIn()): ?>
        <p class="register-text">
            New here?
            <a href="auth.php?mode=register">Create an account</a> or
            <a href="auth.php">log in</a>.
        </p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
