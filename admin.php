<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// Restrict access to Moderators only
if (!isLoggedIn() || !isMod()) {
    http_response_code(403);
    $page_title = 'Access Denied - ThreadZero';
    $body_class = 'home-page';
    include 'includes/header.php';
    ?>
    <main class="home-content">
        <div class="recent-box" style="text-align: center; padding: 50px;">
            <h1 style="color: #b91c1c;">403 Access Denied</h1>
            <p>You do not have moderator privileges to view this control panel.</p>
            <a href="index.php" class="home-button">Return Home</a>
        </div>
    </main>
    <?php
    include 'includes/footer.php';
    exit;
}

$error = '';
$success = '';

// Pick up a one-shot flash message after a redirect
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle Moderator Actions (Resolve Report, Toggle Pin, Toggle Lock, Delete Post)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token. Please refresh and try again.';
    } else {
        $action  = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
        $post_id = is_string($_POST['post_id'] ?? null) ? (int)$_POST['post_id'] : 0;

        // [SQL SELECT] Confirm the post exists and resolve its thread (pin/lock apply to the parent thread)
        $target_sql = "SELECT id, parent_id, COALESCE(parent_id, id) AS thread_id
                       FROM posts
                       WHERE id = ?
                       LIMIT 1";
        $target_stmt = $pdo->prepare($target_sql);
        $target_stmt->execute([$post_id]);
        $target = $target_stmt->fetch();

        if (!$target) {
            $error = 'That post no longer exists.';
        } else {
            $thread_id = (int)$target['thread_id'];

            // --- ACTION A: UNFLAG / RESOLVE REPORT ---
            if ($action === 'unflag') {
                // [SQL UPDATE] Clear the reported flag
                $sql = "UPDATE posts SET is_reported = 0 WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$post_id]);
                $_SESSION['flash_success'] = 'Report for Post #' . $post_id . ' marked as resolved.';
            }

            // --- ACTION B: TOGGLE PIN STATUS ON THE THREAD ---
            elseif ($action === 'toggle_pin') {
                // [SQL UPDATE] Toggle the pin flag on the parent thread
                $sql = "UPDATE posts SET is_pinned = NOT is_pinned WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$thread_id]);
                $_SESSION['flash_success'] = 'Pin status updated for Thread #' . $thread_id . '.';
            }

            // --- ACTION C: TOGGLE LOCK STATUS ON THE THREAD ---
            elseif ($action === 'toggle_lock') {
                // [SQL UPDATE] Toggle the lock flag on the parent thread
                $sql = "UPDATE posts SET is_locked = NOT is_locked WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$thread_id]);
                $_SESSION['flash_success'] = 'Lock status updated for Thread #' . $thread_id . '.';
            }

            // --- ACTION D: DELETE POST / THREAD ---
            elseif ($action === 'delete_post') {
                // [SQL DELETE] Remove the reported post; deleting a thread cascades to its replies
                $sql = "DELETE FROM posts WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$post_id]);
                $_SESSION['flash_success'] = 'Post #' . $post_id . ' deleted successfully.';
            }

            else {
                $error = 'Unknown moderation action.';
            }

            if ($error === '') {
                header("Location: admin.php");
                exit;
            }
        }
    }
}

// [SQL SELECT] Fetch all reported posts and threads awaiting review
$reported_sql = "SELECT p.*, u.username, b.slug AS board_slug
                 FROM posts p
                 JOIN users u ON p.user_id = u.id
                 JOIN boards b ON p.board_id = b.id
                 WHERE p.is_reported = 1
                 ORDER BY p.created_at DESC";
$reported_stmt = $pdo->prepare($reported_sql);
$reported_stmt->execute();
$reported_posts = $reported_stmt->fetchAll();

$page_title = 'Moderator Panel - ThreadZero';
$body_class = 'home-page';
include 'includes/header.php';
?>

<main class="home-content">
    <h1>Moderator Control Panel</h1>
    <p>Manage reported content, pin vital discussions, and lock threads.</p>

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

    <h2>Flagged Posts Queue (<?= count($reported_posts) ?>)</h2>

    <?php if (empty($reported_posts)): ?>
        <div class="recent-box">
            <p>No reported posts in the queue. Everything looks clean!</p>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; background: white; border-collapse: collapse; border-radius: 8px; overflow: hidden; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #183d30; color: white; text-align: left;">
                        <th style="padding: 12px;">ID</th>
                        <th style="padding: 12px;">Board</th>
                        <th style="padding: 12px;">Author</th>
                        <th style="padding: 12px;">Content Snippet</th>
                        <th style="padding: 12px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reported_posts as $post): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px; font-weight: bold;">#<?= (int)$post['id'] ?></td>
                            <td style="padding: 12px;">/<?= htmlspecialchars($post['board_slug']) ?>/</td>
                            <td style="padding: 12px;"><?= renderAuthorBadge($post) ?></td>
                            <td style="padding: 12px; max-width: 300px; word-break: break-word;">
                                <strong><?= htmlspecialchars($post['title'] ?? '') ?></strong>
                                <?= htmlspecialchars(mb_strimwidth($post['content'], 0, 100, '...')) ?>
                                <br>
                                <a href="thread.php?id=<?= (int)($post['parent_id'] ?? $post['id']) ?>#p<?= (int)$post['id'] ?>" target="_blank" style="font-size: 13px;">View Full Context &rarr;</a>
                            </td>
                            <td style="padding: 12px; text-align: center; white-space: nowrap;">
                                <!-- Resolve Report -->
                                <form method="POST" action="admin.php" style="display: inline;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="unflag">
                                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                    <button type="submit" style="background: #166534; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Approve</button>
                                </form>

                                <!-- Toggle Pin -->
                                <form method="POST" action="admin.php" style="display: inline; margin-left: 4px;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_pin">
                                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                    <button type="submit" style="background: #2563eb; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                        <?= (int)$post['is_pinned'] === 1 ? 'Unpin' : 'Pin' ?>
                                    </button>
                                </form>

                                <!-- Toggle Lock -->
                                <form method="POST" action="admin.php" style="display: inline; margin-left: 4px;">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_lock">
                                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                    <button type="submit" style="background: #d97706; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                        <?= (int)$post['is_locked'] === 1 ? 'Unlock' : 'Lock' ?>
                                    </button>
                                </form>

                                <!-- Delete Post -->
                                <form method="POST" action="admin.php" style="display: inline; margin-left: 4px;" onsubmit="return confirm('Permanently delete this reported post?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete_post">
                                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                    <button type="submit" style="background: #b91c1c; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>