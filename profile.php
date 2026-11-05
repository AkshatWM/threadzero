<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Redirect to login if user is not signed in
if (!isLoggedIn()) {
    header("Location: auth.php");
    exit;
}

$error = '';
$success = '';
$current_user_id = (int)$_SESSION['user_id'];

// Pick up a one-shot flash message after a redirect
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle Profile POST Actions (Update Bio or Remove Bookmark)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token. Please refresh and try again.';
    } else {
        $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

        // --- ACTION A: UPDATE USER BIOGRAPHY ---
        if ($action === 'update_bio') {
            if (!is_string($_POST['bio'] ?? null)) {
                $error = 'Invalid biography input.';
            } else {
                $new_bio = trim($_POST['bio']);

                if (mb_strlen($new_bio) > 255) {
                    $error = 'Biography cannot exceed 255 characters.';
                } else {
                    // [SQL UPDATE] Update user bio
                    $bio_sql = "UPDATE users SET bio = ? WHERE id = ?";
                    $bio_stmt = $pdo->prepare($bio_sql);
                    $bio_stmt->execute([$new_bio, $current_user_id]);

                    $_SESSION['flash_success'] = 'Profile bio updated successfully!';
                    header("Location: profile.php");
                    exit;
                }
            }
        }

        // --- ACTION B: REMOVE A SAVED BOOKMARK ---
        elseif ($action === 'remove_bookmark') {
            $post_id = is_string($_POST['post_id'] ?? null) ? (int)$_POST['post_id'] : 0;

            // [SQL DELETE] Delete bookmark record
            $rem_sql = "DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?";
            $rem_stmt = $pdo->prepare($rem_sql);
            $rem_stmt->execute([$current_user_id, $post_id]);

            $_SESSION['flash_success'] = $rem_stmt->rowCount() > 0
                ? 'Bookmark removed.'
                : 'That bookmark was already removed.';
            header("Location: profile.php");
            exit;
        }
    }
}

// [SQL SELECT] Fetch current user details
$user_sql = "SELECT username, bio, role, created_at FROM users WHERE id = ? LIMIT 1";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$current_user_id]);
$user = $user_stmt->fetch();

// [SQL SELECT] Fetch all posts/threads created by this user
$posts_sql = "SELECT p.*, b.slug AS board_slug, b.title AS board_title
              FROM posts p
              JOIN boards b ON p.board_id = b.id
              WHERE p.user_id = ?
              ORDER BY p.created_at DESC";
$posts_stmt = $pdo->prepare($posts_sql);
$posts_stmt->execute([$current_user_id]);
$user_posts = $posts_stmt->fetchAll();

// [SQL SELECT] Fetch all bookmarked threads
$bookmarks_sql = "SELECT p.*, b.slug AS board_slug, b.title AS board_title
                  FROM bookmarks bm
                  JOIN posts p ON bm.post_id = p.id
                  JOIN boards b ON p.board_id = b.id
                  WHERE bm.user_id = ?
                  ORDER BY bm.created_at DESC";
$bookmarks_stmt = $pdo->prepare($bookmarks_sql);
$bookmarks_stmt->execute([$current_user_id]);
$user_bookmarks = $bookmarks_stmt->fetchAll();

$page_title = 'Profile - ' . $user['username'] . ' - ThreadZero';
$body_class = 'profile-page';
include 'includes/header.php';
?>

<main class="profile-box">
    <h1>User Profile</h1>

    <?php if ($error !== ''): ?>
        <div class="form-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="form-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- User Information Card -->
    <div class="user-info">
        <h2>@<?= htmlspecialchars($user['username']) ?></h2>
        <p>Role: <strong><?= strtoupper(htmlspecialchars($user['role'])) ?></strong></p>
        <p>Joined: <?= htmlspecialchars($user['created_at']) ?></p>
    </div>

    <hr>

    <!-- Editable About Me Section -->
    <section class="about-section">
        <h2>About Me</h2>
        <form method="POST" action="profile.php">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_bio">

            <label for="bio">Update your biography:</label>
            <textarea
                id="bio"
                name="bio"
                rows="4"
                maxlength="255"
                placeholder="Write something about yourself..."
            ><?= htmlspecialchars($user['bio']) ?></textarea>

            <button type="submit" class="profile-button">Update Bio</button>
        </form>
    </section>

    <hr>

    <!-- My Posts Section -->
    <section class="my-posts">
        <h2>My Post History (<?= count($user_posts) ?>)</h2>

        <?php if (empty($user_posts)): ?>
            <p>You haven't posted any threads or replies yet.</p>
        <?php else: ?>
            <?php foreach ($user_posts as $post): ?>
                <div class="profile-post" id="p<?= (int)$post['id'] ?>">
                    <h3>
                        <a href="thread.php?id=<?= (int)($post['parent_id'] ?? $post['id']) ?>#p<?= (int)$post['id'] ?>">
                            <?= htmlspecialchars($post['title'] ?? 'Reply #' . $post['id']) ?>
                        </a>
                    </h3>
                    <p>Board: /<?= htmlspecialchars($post['board_slug']) ?>/ | Posted on: <?= htmlspecialchars($post['created_at']) ?></p>
                    <p><?= formatPostBody(mb_strimwidth($post['content'], 0, 140, '...')) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <hr>

    <!-- Saved Bookmarks Section -->
    <section class="saved-bookmarks">
        <h2>My Saved Bookmarks (<?= count($user_bookmarks) ?>)</h2>

        <?php if (empty($user_bookmarks)): ?>
            <p>No bookmarked threads saved yet.</p>
        <?php else: ?>
            <?php foreach ($user_bookmarks as $bm): ?>
                <div class="profile-post">
                    <h3>
                        <a href="thread.php?id=<?= (int)$bm['id'] ?>">
                            <?= htmlspecialchars($bm['title']) ?>
                        </a>
                    </h3>
                    <p>Board: /<?= htmlspecialchars($bm['board_slug']) ?>/</p>

                    <form method="POST" action="profile.php" style="margin-top: 10px;">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove_bookmark">
                        <input type="hidden" name="post_id" value="<?= (int)$bm['id'] ?>">
                        <button type="submit" class="profile-button" style="background-color: #b91c1c;">Remove Bookmark</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

</main>

<?php include 'includes/footer.php'; ?>