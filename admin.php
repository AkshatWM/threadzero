<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Module 5 will build the full moderation suite; only mods may open this page
if (!isMod()) {
    header('Location: index.php');
    exit;
}

$page_title = 'Mod Panel - ThreadZero';
$body_class = 'profile-page';
include 'includes/header.php';
?>

<main class="home-content">
    <h2>Moderation Panel</h2>

    <div class="profile-box">
        <p>The reported-post queue, pin/lock toggles and delete actions arrive in Module 5.</p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
