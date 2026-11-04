<?php
require_once 'config/db.php';
require_once 'config/functions.php';

if (!isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$page_title = 'Profile - ThreadZero';
$body_class = 'profile-page';
include 'includes/header.php';
?>

<main class="home-content">
    <h2>Your Profile</h2>

    <div class="profile-box">
        <p>
            Signed in as <strong>@<?= htmlspecialchars($_SESSION['username']) ?></strong>
            (<?= htmlspecialchars($_SESSION['role']) ?>).
        </p>
        <p>The bio editor, post history and bookmarks arrive in Module 4.</p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
