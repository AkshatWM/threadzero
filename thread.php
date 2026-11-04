<?php
require_once 'config/db.php';
require_once 'config/functions.php';

$thread_id = is_string($_GET['id'] ?? null) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

// Pick up a one-shot flash message after a redirect (bookmark/report)
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// 1. [SQL SELECT] Load the parent thread with its author and board
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

// 2. Handle Module 3 POST Actions (Reply, Edit, Delete, Bookmark, Report)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        $error = 'You must be logged in to perform this action.';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token. Please refresh the page and try again.';
    } else {
        $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
        $current_user_id = (int)$_SESSION['user_id'];

        // --- ACTION A: POST A REPLY (WITH BUMP / SAGE) ---
        if ($action === 'post_reply') {
            if ((int)$thread['is_locked'] === 1 && !isMod()) {
                $error = 'This thread is locked. No new replies can be posted.';
            } else {
                $content = is_string($_POST['content'] ?? null) ? trim($_POST['content']) : '';
                $is_sage = isset($_POST['sage']) ? 1 : 0;
                $image_path = null;

                if (mb_strlen($content) < 2) {
                    $error = 'Reply content must be at least 2 characters.';
                } elseif (mb_strlen($content) > 5000) {
                    $error = 'Reply content must be 5,000 characters or fewer.';
                } else {
                    // Optional reply image upload (same rules as thread creation)
                    if (!empty($_FILES['image']['name'])) {
                        $allowed_ext  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        $file_name    = $_FILES['image']['name'];
                        $file_tmp     = $_FILES['image']['tmp_name'];
                        $file_size    = $_FILES['image']['size'];
                        $file_err     = $_FILES['image']['error'];

                        // Reject array-style field names and anything that is not a real image
                        if (!is_string($file_name) || !is_string($file_tmp) || !is_int($file_size) || !is_int($file_err)) {
                            $error = 'Invalid image upload.';
                        } else {
                            $ext        = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                            $image_info = @getimagesize($file_tmp);

                            if ($file_err !== UPLOAD_ERR_OK) {
                                $error = 'Error uploading image.';
                            } elseif (!in_array($ext, $allowed_ext, true)) {
                                $error = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
                            } elseif ($image_info === false || !in_array($image_info['mime'], $allowed_mime, true)) {
                                $error = 'The uploaded file is not a valid image.';
                            } elseif ($file_size > 2 * 1024 * 1024) {
                                $error = 'Image size must be under 2 MB.';
                            } else {
                                $new_filename = 'reply_' . bin2hex(random_bytes(8)) . '.' . $ext;
                                $upload_dir = __DIR__ . '/assets/uploads/';
                                if (!is_dir($upload_dir)) {
                                    mkdir($upload_dir, 0755, true);
                                }
                                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                                    $image_path = 'assets/uploads/' . $new_filename;
                                } else {
                                    $error = 'Failed to save uploaded image.';
                                }
                            }
                        }
                    }

                    if ($error === '') {
                        // [SQL INSERT] Insert reply row linked to parent thread via parent_id
                        $insert_sql = "INSERT INTO posts (board_id, user_id, parent_id, title, content, image_path, is_anon)
                                       VALUES (?, ?, ?, NULL, ?, ?, 0)";
                        $insert_stmt = $pdo->prepare($insert_sql);
                        $insert_stmt->execute([
                            $thread['board_id'],
                            $current_user_id,
                            $thread_id,
                            $content,
                            $image_path
                        ]);
                        $new_reply_id = $pdo->lastInsertId();

                        // [SQL UPDATE] Bump parent thread to top of board unless Sage is checked
                        if ($is_sage === 0) {
                            $bump_sql = "UPDATE posts SET bumped_at = NOW() WHERE id = ?";
                            $bump_stmt = $pdo->prepare($bump_sql);
                            $bump_stmt->execute([$thread_id]);
                        }

                        header("Location: thread.php?id=" . $thread_id . "#p" . $new_reply_id);
                        exit;
                    }
                }
            }
        }

        // --- ACTION B: EDIT OWN THREAD OR REPLY ---
        elseif ($action === 'edit_post') {
            $post_id = is_string($_POST['post_id'] ?? null) ? (int)$_POST['post_id'] : 0;
            $new_content = is_string($_POST['content'] ?? null) ? trim($_POST['content']) : '';

            if (mb_strlen($new_content) < 2) {
                $error = 'Post content must be at least 2 characters.';
            } elseif (mb_strlen($new_content) > 5000) {
                $error = 'Post content must be 5,000 characters or fewer.';
            } else {
                // [SQL SELECT] Confirm the post exists in this thread and belongs to the user
                $own_sql = "SELECT id FROM posts
                            WHERE id = ? AND user_id = ? AND (id = ? OR parent_id = ?)
                            LIMIT 1";
                $own_stmt = $pdo->prepare($own_sql);
                $own_stmt->execute([$post_id, $current_user_id, $thread_id, $thread_id]);

                if (!$own_stmt->fetch()) {
                    $error = 'You can only edit your own posts in this thread.';
                } else {
                    // [SQL UPDATE] Update post content only if the logged-in user is the author
                    $edit_sql = "UPDATE posts SET content = ? WHERE id = ? AND user_id = ?";
                    $edit_stmt = $pdo->prepare($edit_sql);
                    $edit_stmt->execute([$new_content, $post_id, $current_user_id]);

                    header("Location: thread.php?id=" . $thread_id . "#p" . $post_id);
                    exit;
                }
            }
        }

        // --- ACTION C: DELETE OWN POST (OR MODERATOR DELETE) ---
        elseif ($action === 'delete_post') {
            $post_id = is_string($_POST['post_id'] ?? null) ? (int)$_POST['post_id'] : 0;

            // [SQL SELECT] Confirm the post belongs to this thread
            $exists_sql = "SELECT id FROM posts WHERE id = ? AND (id = ? OR parent_id = ?) LIMIT 1";
            $exists_stmt = $pdo->prepare($exists_sql);
            $exists_stmt->execute([$post_id, $thread_id, $thread_id]);

            if (!$exists_stmt->fetch()) {
                $error = 'That post does not belong to this thread.';
            } elseif (isMod()) {
                // [SQL DELETE] Moderators can delete any post or thread
                $del_sql = "DELETE FROM posts WHERE id = ?";
                $del_stmt = $pdo->prepare($del_sql);
                $del_stmt->execute([$post_id]);
            } else {
                // [SQL DELETE] Regular users can only delete their own posts
                $del_sql = "DELETE FROM posts WHERE id = ? AND user_id = ?";
                $del_stmt = $pdo->prepare($del_sql);
                $del_stmt->execute([$post_id, $current_user_id]);

                if ($del_stmt->rowCount() === 0) {
                    $error = 'You can only delete your own posts.';
                }
            }

            if ($error === '') {
                // If the main parent thread was deleted, redirect back to the board
                if ($post_id === $thread_id) {
                    header("Location: index.php?board=" . urlencode($thread['board_slug']));
                } else {
                    header("Location: thread.php?id=" . $thread_id);
                }
                exit;
            }
        }

        // --- ACTION D: TOGGLE BOOKMARK ON THIS THREAD ---
        elseif ($action === 'toggle_bookmark') {
            // [SQL SELECT] Check if thread is already bookmarked by this user
            $chk_sql = "SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ? LIMIT 1";
            $chk_stmt = $pdo->prepare($chk_sql);
            $chk_stmt->execute([$current_user_id, $thread_id]);

            if ($chk_stmt->fetch()) {
                // [SQL DELETE] Remove existing bookmark
                $rem_sql = "DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?";
                $rem_stmt = $pdo->prepare($rem_sql);
                $rem_stmt->execute([$current_user_id, $thread_id]);
                $_SESSION['flash_success'] = 'Bookmark removed.';
            } else {
                // [SQL INSERT] Save thread to user bookmarks
                $add_sql = "INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)";
                $add_stmt = $pdo->prepare($add_sql);
                $add_stmt->execute([$current_user_id, $thread_id]);
                $_SESSION['flash_success'] = 'Thread saved to your bookmarks!';
            }

            header("Location: thread.php?id=" . $thread_id);
            exit;
        }

        // --- ACTION E: REPORT A POST OR THREAD FOR MODERATOR REVIEW ---
        elseif ($action === 'report_post') {
            $post_id = is_string($_POST['post_id'] ?? null) ? (int)$_POST['post_id'] : 0;

            // [SQL SELECT] Confirm the post belongs to this thread
            $exists_sql = "SELECT id FROM posts WHERE id = ? AND (id = ? OR parent_id = ?) LIMIT 1";
            $exists_stmt = $pdo->prepare($exists_sql);
            $exists_stmt->execute([$post_id, $thread_id, $thread_id]);

            if (!$exists_stmt->fetch()) {
                $error = 'That post does not belong to this thread.';
            } else {
                // [SQL UPDATE] Flag post as reported (is_reported = 1)
                $rep_sql = "UPDATE posts SET is_reported = 1 WHERE id = ?";
                $rep_stmt = $pdo->prepare($rep_sql);
                $rep_stmt->execute([$post_id]);

                $_SESSION['flash_success'] = 'Post #' . $post_id . ' has been flagged for moderator review.';
                header("Location: thread.php?id=" . $thread_id . "#p" . $post_id);
                exit;
            }
        }
    }
}

// Reload parent thread if it was edited
$thread_stmt->execute([$thread_id]);
$thread = $thread_stmt->fetch();

// 3. [SQL SELECT] Load replies in chronological order
$replies_sql = "SELECT p.*, u.username
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.parent_id = ?
                ORDER BY p.created_at ASC, p.id ASC";
$replies_stmt = $pdo->prepare($replies_sql);
$replies_stmt->execute([$thread_id]);
$replies = $replies_stmt->fetchAll();

// Check if logged-in user has bookmarked this thread
$is_bookmarked = false;
if (isLoggedIn()) {
    $bm_stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND post_id = ? LIMIT 1");
    $bm_stmt->execute([$_SESSION['user_id'], $thread_id]);
    $is_bookmarked = (bool)$bm_stmt->fetch();
}

// Check if user clicked "Edit" on a specific post (?edit=1001)
$edit_post_id = is_string($_GET['edit'] ?? null) ? (int)$_GET['edit'] : 0;

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

    <!-- ================= MAIN PARENT THREAD ================= -->
    <div id="p<?= (int)$thread['id'] ?>" class="thread-post">
        <h1>
            <?php if ((int)$thread['is_pinned'] === 1): ?>
                <span style="color: #183d30;">[PINNED]</span>
            <?php endif; ?>
            <?php if ((int)$thread['is_locked'] === 1): ?>
                <span style="color: #b91c1c;">[LOCKED]</span>
            <?php endif; ?>
            <?= htmlspecialchars($thread['title']) ?>
        </h1>

        <p class="thread-info">
            <?= renderAuthorBadge($thread) ?>
            | /<?= htmlspecialchars($thread['board_slug']) ?>/ &mdash; <?= htmlspecialchars($thread['board_title']) ?>
            | <a href="#reply-form" class="quote-link" onclick="quotePost(<?= (int)$thread['id'] ?>)">Post #<?= (int)$thread['id'] ?></a>
            | <?= htmlspecialchars($thread['created_at']) ?>
        </p>

        <?php if (!empty($thread['image_path'])): ?>
            <p>
                <img
                    src="<?= htmlspecialchars($thread['image_path']) ?>"
                    alt="Thread attachment"
                    style="max-width: 100%; max-height: 400px; border-radius: 6px;"
                >
            </p>
        <?php endif; ?>

        <?php if ($edit_post_id === (int)$thread['id'] && isLoggedIn() && (int)$_SESSION['user_id'] === (int)$thread['user_id']): ?>
            <!-- Inline Edit Form for Main Thread -->
            <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit_post">
                <input type="hidden" name="post_id" value="<?= (int)$thread['id'] ?>">
                <textarea name="content" maxlength="5000" required><?= htmlspecialchars($thread['content']) ?></textarea>
                <div>
                    <button type="submit">Save Changes</button>
                    <a href="thread.php?id=<?= (int)$thread['id'] ?>" style="margin-left: 10px;">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p><?= formatPostBody($thread['content']) ?></p>
        <?php endif; ?>

        <!-- Thread Action Controls -->
        <?php if (isLoggedIn()): ?>
            <div class="thread-actions">
                <a href="#reply-form" class="quote-link" onclick="quotePost(<?= (int)$thread['id'] ?>)">Quote (&gt;&gt;<?= (int)$thread['id'] ?>)</a>

                <?php if ((int)$_SESSION['user_id'] === (int)$thread['user_id']): ?>
                    <a href="thread.php?id=<?= (int)$thread['id'] ?>&amp;edit=<?= (int)$thread['id'] ?>#p<?= (int)$thread['id'] ?>" class="quote-link">Edit</a>
                <?php endif; ?>

                <?php if ((int)$_SESSION['user_id'] === (int)$thread['user_id'] || isMod()): ?>
                    <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>" style="display: inline;" onsubmit="return confirm('Delete this entire thread and all its replies?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="post_id" value="<?= (int)$thread['id'] ?>">
                        <button type="submit" style="background: none; border: none; color: #b91c1c; text-decoration: underline; padding: 0; font-size: 14px; cursor: pointer;">Delete</button>
                    </form>
                <?php endif; ?>

                <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="toggle_bookmark">
                    <button type="submit" style="background: none; border: none; color: #183d30; text-decoration: underline; padding: 0; font-size: 14px; cursor: pointer;">
                        <?= $is_bookmarked ? 'Remove Bookmark' : 'Bookmark Thread' ?>
                    </button>
                </form>

                <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="report_post">
                    <input type="hidden" name="post_id" value="<?= (int)$thread['id'] ?>">
                    <button type="submit" style="background: none; border: none; color: #666; text-decoration: underline; padding: 0; font-size: 14px; cursor: pointer;">
                        <?= (int)$thread['is_reported'] === 1 ? 'Reported' : 'Report' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <hr>

    <!-- ================= REPLIES LIST ================= -->
    <h2>Replies (<?= count($replies) ?>)</h2>

    <?php if (empty($replies)): ?>
        <p>No replies yet. Start the discussion below!</p>
    <?php else: ?>
        <?php foreach ($replies as $reply): ?>
            <div class="reply-box" id="p<?= (int)$reply['id'] ?>">
                <p class="thread-info">
                    <?= renderAuthorBadge($reply, $thread) ?>
                    | <a href="#reply-form" class="quote-link" onclick="quotePost(<?= (int)$reply['id'] ?>)">Post #<?= (int)$reply['id'] ?></a>
                    | <?= htmlspecialchars($reply['created_at']) ?>
                </p>

                <?php if (!empty($reply['image_path'])): ?>
                    <p>
                        <img
                            src="<?= htmlspecialchars($reply['image_path']) ?>"
                            alt="Reply attachment"
                            style="max-width: 280px; max-height: 220px; border-radius: 6px;"
                        >
                    </p>
                <?php endif; ?>

                <?php if ($edit_post_id === (int)$reply['id'] && isLoggedIn() && (int)$_SESSION['user_id'] === (int)$reply['user_id']): ?>
                    <!-- Inline Edit Form for Reply -->
                    <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="edit_post">
                        <input type="hidden" name="post_id" value="<?= (int)$reply['id'] ?>">
                        <textarea name="content" maxlength="5000" required><?= htmlspecialchars($reply['content']) ?></textarea>
                        <div>
                            <button type="submit">Save Changes</button>
                            <a href="thread.php?id=<?= (int)$thread['id'] ?>#p<?= (int)$reply['id'] ?>" style="margin-left: 10px;">Cancel</a>
                        </div>
                    </form>
                <?php else: ?>
                    <p><?= formatPostBody($reply['content']) ?></p>
                <?php endif; ?>

                <?php if (isLoggedIn()): ?>
                    <div class="thread-actions">
                        <a href="#reply-form" class="quote-link" onclick="quotePost(<?= (int)$reply['id'] ?>)">Quote</a>

                        <?php if ((int)$_SESSION['user_id'] === (int)$reply['user_id']): ?>
                            <a href="thread.php?id=<?= (int)$thread['id'] ?>&amp;edit=<?= (int)$reply['id'] ?>#p<?= (int)$reply['id'] ?>" class="quote-link">Edit</a>
                        <?php endif; ?>

                        <?php if ((int)$_SESSION['user_id'] === (int)$reply['user_id'] || isMod()): ?>
                            <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>" style="display: inline;" onsubmit="return confirm('Delete this reply?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_post">
                                <input type="hidden" name="post_id" value="<?= (int)$reply['id'] ?>">
                                <button type="submit" style="background: none; border: none; color: #b91c1c; text-decoration: underline; padding: 0; font-size: 14px; cursor: pointer;">Delete</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>" style="display: inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="report_post">
                            <input type="hidden" name="post_id" value="<?= (int)$reply['id'] ?>">
                            <button type="submit" style="background: none; border: none; color: #666; text-decoration: underline; padding: 0; font-size: 14px; cursor: pointer;">
                                <?= (int)$reply['is_reported'] === 1 ? 'Reported' : 'Report' ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <hr>

    <!-- ================= WRITE A REPLY FORM ================= -->
    <h2 id="reply-form">Write a Reply</h2>

    <?php if ((int)$thread['is_locked'] === 1 && !isMod()): ?>
        <p style="color: #b91c1c; font-weight: bold;">This thread has been locked by a moderator. New replies are disabled.</p>
    <?php elseif (isLoggedIn()): ?>
        <form method="POST" action="thread.php?id=<?= (int)$thread['id'] ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="post_reply">

            <label for="reply-content">Your Reply</label>
            <textarea
                id="reply-content"
                name="content"
                placeholder="Write your reply here... (Use > for greentext or >>1001 to quote a post)"
                minlength="2"
                maxlength="5000"
                required
            ></textarea>

            <label for="reply-image">Attach Image (Optional, max 2MB)</label>
            <input type="file" id="reply-image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">

            <label>
                <input type="checkbox" name="sage" value="1">
                Sage (Do not bump this thread to the top of the board)
            </label>

            <button type="submit">Post Reply</button>
        </form>
    <?php else: ?>
        <p>Please <a href="auth.php">log in</a> or <a href="auth.php?mode=register">create an account</a> to post a reply.</p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>