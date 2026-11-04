<?php
require_once 'config/db.php';
require_once 'config/functions.php';

$thread_id = is_string($_GET['id'] ?? null) ? (int)$_GET['id'] : 0;

// [SQL SELECT] Load the parent thread with its author and board
$thread_sql = "SELECT p.*, u.username, b.slug AS board_slug, b.title AS board_title
               FROM posts p
               JOIN users u ON p.user_id = u.id
               JOIN boards b ON p.board_id = b.id
               WHERE p.id = ? AND p.parent_id IS NULL
               LIMIT 1";
$thread_stmt = $pdo->prepare($thread_sql);
$thread_stmt->execute([$thread_id]);
$thread = $thread_stmt->fetch();

if (!$thread) {
    http_response_code(404);
    $page_title = 'Thread Not Found - ThreadZero';
    $body_class = 'thread-page';
    include 'includes/header.php';
    ?>
    <main class="thread-box">
        <h1>Thread not found</h1>
        <p>This discussion may have been deleted. <a href="index.php">Back to the board list</a>.</p>
    </main>
    <?php
    include 'includes/footer.php';
    exit;
}

// [SQL SELECT] Load replies in chronological order
$replies_sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.parent_id = ?
                ORDER BY p.created_at ASC, p.id ASC";
$replies_stmt = $pdo->prepare($replies_sql);
$replies_stmt->execute([$thread_id]);
$replies = $replies_stmt->fetchAll();

$page_title = $thread['title'] . ' - ThreadZero';
$body_class = 'thread-page';
include 'includes/header.php';
?>

<main class="thread-box">
    <p>
        <a href="index.php?board=<?= urlencode($thread['board_slug']) ?>">
            &larr; Back to /<?= htmlspecialchars($thread['board_slug']) ?>/
        </a>
    </p>

    <!-- Main thread -->
    <div id="p<?= (int)$thread['id'] ?>" class="thread-post">
        <h1><?= htmlspecialchars($thread['title']) ?></h1>

        <p class="thread-info">
            <?= renderAuthorBadge($thread) ?>
            | /<?= htmlspecialchars($thread['board_slug']) ?>/ &mdash; <?= htmlspecialchars($thread['board_title']) ?>
            | Post #<?= (int)$thread['id'] ?>
            | <?= htmlspecialchars($thread['created_at']) ?>
        </p>

        <?php if (!empty($thread['image_path'])): ?>
            <p>
                <img
                    src="<?= htmlspecialchars($thread['image_path']) ?>"
                    alt="Thread attachment"
                    style="max-width: 100%; border-radius: 6px;"
                >
            </p>
        <?php endif; ?>

        <p><?= formatPostBody($thread['content']) ?></p>
    </div>

    <hr>

    <!-- Replies -->
    <h2>Replies (<?= count($replies) ?>)</h2>

    <?php if (empty($replies)): ?>
        <p>No replies yet.</p>
    <?php else: ?>
        <?php foreach ($replies as $reply): ?>
            <div class="reply-box" id="p<?= (int)$reply['id'] ?>">
                <p class="thread-info">
                    <?= renderAuthorBadge($reply, $thread) ?>
                    | Post #<?= (int)$reply['id'] ?>
                    | <?= htmlspecialchars($reply['created_at']) ?>
                </p>

                <?php if (!empty($reply['image_path'])): ?>
                    <p>
                        <img
                            src="<?= htmlspecialchars($reply['image_path']) ?>"
                            alt="Reply attachment"
                            style="max-width: 100%; border-radius: 6px;"
                        >
                    </p>
                <?php endif; ?>

                <p><?= formatPostBody($reply['content']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="recent-box">
        <p><strong>Module 3 preview:</strong> the reply box with Bump/Sage, edit/delete, bookmark and report will be added here.</p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
