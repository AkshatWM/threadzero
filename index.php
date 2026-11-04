<?php
require_once 'config/db.php';
require_once 'config/functions.php';

$error = '';

// 1. [SQL SELECT] Load all discussion boards
$boards_sql = "SELECT id, slug, title, description FROM boards ORDER BY id ASC";
$boards_stmt = $pdo->prepare($boards_sql);
$boards_stmt->execute();
$boards = $boards_stmt->fetchAll();

// Map boards by slug and ID for easy lookup
$boards_by_slug = [];
$boards_by_id = [];
foreach ($boards as $b) {
    $boards_by_slug[$b['slug']] = $b;
    $boards_by_id[$b['id']] = $b;
}

// Check if a specific board is selected via ?board=slug
$current_board_slug = is_string($_GET['board'] ?? null) ? trim($_GET['board']) : '';
$current_board = $boards_by_slug[$current_board_slug] ?? null;

// 2. Handle "Create New Thread" Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_thread') {
    if (!isLoggedIn()) {
        $error = 'You must be logged in to create a thread.';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token. Please refresh and try again.';
    } else {
        $board_id = (int)($_POST['board_id'] ?? 0);
        $title    = is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '';
        $content  = is_string($_POST['content'] ?? null) ? trim($_POST['content']) : '';
        $is_anon  = isset($_POST['is_anon']) ? 1 : 0;
        $image_path = null;

        if (!isset($boards_by_id[$board_id])) {
            $error = 'Please select a valid discussion board.';
        } elseif (mb_strlen($title) < 3 || mb_strlen($title) > 150) {
            $error = 'Thread title must be between 3 and 150 characters.';
        } elseif (mb_strlen($content) < 1) {
            $error = 'Thread content cannot be empty.';
        } elseif (mb_strlen($content) > 5000) {
            $error = 'Thread content must be 5,000 characters or fewer.';
        } else {
            // Optional Image Upload Handling
            if (!empty($_FILES['image']['name'])) {
                $allowed_ext  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowed_mime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $file_name    = $_FILES['image']['name'];
                $file_tmp     = $_FILES['image']['tmp_name'];
                $file_size    = $_FILES['image']['size'];
                $file_err     = $_FILES['image']['error'];

                // Reject array-style field names and anything that is not a real image
                if (!is_string($file_name) || !is_string($file_tmp)) {
                    $error = 'Invalid image upload.';
                } else {
                    $ext        = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    $image_info = @getimagesize($file_tmp);

                    if ($file_err !== UPLOAD_ERR_OK) {
                        $error = 'Error uploading image file.';
                    } elseif (!in_array($ext, $allowed_ext, true)) {
                        $error = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
                    } elseif ($image_info === false || !in_array($image_info['mime'], $allowed_mime, true)) {
                        $error = 'The uploaded file is not a valid image.';
                    } elseif ($file_size > 2 * 1024 * 1024) {
                        $error = 'Image size must be under 2 MB.';
                    } else {
                        $new_filename = 'post_' . bin2hex(random_bytes(8)) . '.' . $ext;
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

            // Insert new thread if there are no validation/upload errors
            if ($error === '') {
                // [SQL INSERT] Insert new parent thread (parent_id IS NULL)
                $insert_sql = "INSERT INTO posts (board_id, user_id, parent_id, title, content, image_path, is_anon)
                               VALUES (?, ?, NULL, ?, ?, ?, ?)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    $board_id,
                    $_SESSION['user_id'],
                    $title,
                    $content,
                    $image_path,
                    $is_anon
                ]);

                $new_thread_id = $pdo->lastInsertId();
                header("Location: thread.php?id=" . $new_thread_id);
                exit;
            }
        }
    }
}

// 3. Build Thread Feed Query (Filter by Board, Keyword Search, and Sort Order)
$search_query = is_string($_GET['search'] ?? null) ? trim($_GET['search']) : '';
$sort_by      = (is_string($_GET['sort'] ?? null) && $_GET['sort'] === 'newest') ? 'newest' : 'bumped';

$where_clauses = ["p.parent_id IS NULL"];
$params = [];

// Filter by selected board if present
if ($current_board !== null) {
    $where_clauses[] = "p.board_id = ?";
    $params[] = $current_board['id'];
}

// Filter by keyword search in title or content
if ($search_query !== '') {
    $where_clauses[] = "(p.title LIKE ? OR p.content LIKE ?)";
    $like_param = '%' . $search_query . '%';
    $params[] = $like_param;
    $params[] = $like_param;
}

$where_sql = implode(' AND ', $where_clauses);
$order_sql = ($sort_by === 'newest')
    ? "p.is_pinned DESC, p.created_at DESC"
    : "p.is_pinned DESC, p.bumped_at DESC";

// [SQL SELECT] Fetch threads with author username, board info, and dynamic reply count
$feed_sql = "SELECT p.*, u.username, b.slug AS board_slug, b.title AS board_title,
                    (SELECT COUNT(*) FROM posts r WHERE r.parent_id = p.id) AS reply_count
             FROM posts p
             JOIN users u ON p.user_id = u.id
             JOIN boards b ON p.board_id = b.id
             WHERE $where_sql
             ORDER BY $order_sql
             LIMIT 50";
$feed_stmt = $pdo->prepare($feed_sql);
$feed_stmt->execute($params);
$threads = $feed_stmt->fetchAll();

$page_title = $current_board ? ('/' . $current_board['slug'] . '/ - ' . $current_board['title'] . ' - ThreadZero') : 'ThreadZero - Home';
$body_class = 'home-page';
include 'includes/header.php';
?>

<section class="welcome-box">
    <?php if ($current_board): ?>
        <h1>/<?= htmlspecialchars($current_board['slug']) ?>/ &mdash; <?= htmlspecialchars($current_board['title']) ?></h1>
        <p><?= htmlspecialchars($current_board['description']) ?></p>
        <a href="index.php" class="home-button">&larr; View All Boards</a>
    <?php else: ?>
        <h1>Welcome to ThreadZero</h1>
        <p>Share your thoughts, discuss ideas, and connect through privacy-first conversations.</p>
        <?php if (isLoggedIn()): ?>
            <p>Signed in as <strong>@<?= htmlspecialchars($_SESSION['username']) ?></strong></p>
            <a href="profile.php" class="home-button">Your Profile</a>
        <?php else: ?>
            <a href="auth.php?mode=register" class="home-button">Join the Community</a>
        <?php endif; ?>
    <?php endif; ?>
</section>

<main class="home-content">

    <!-- Explore Boards Navigation -->
    <h2>Explore Boards</h2>
    <div class="board-list">
        <?php foreach ($boards as $board): ?>
            <div class="board-box<?= ($current_board && $current_board['id'] === $board['id']) ? ' active' : '' ?>">
                <h3>/<?= htmlspecialchars($board['slug']) ?>/ &mdash; <?= htmlspecialchars($board['title']) ?></h3>
                <p><?= htmlspecialchars($board['description']) ?></p>
                <a href="index.php?board=<?= urlencode($board['slug']) ?>">View Discussions</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Create New Thread Section -->
    <h2>Create New Thread</h2>
    <div class="recent-box">
        <?php if ($error !== ''): ?>
            <div class="form-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
            <form method="POST" action="index.php<?= $current_board ? '?board=' . urlencode($current_board['slug']) : '' ?>" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_thread">

                <label for="board_id">Select Board</label>
                <select id="board_id" name="board_id" style="padding: 10px; border: 1px solid #ccc; border-radius: 5px;" required>
                    <?php foreach ($boards as $board): ?>
                        <option value="<?= (int)$board['id'] ?>" <?= ($current_board && $current_board['id'] === $board['id']) ? 'selected' : '' ?>>
                            /<?= htmlspecialchars($board['slug']) ?>/ - <?= htmlspecialchars($board['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="title">Thread Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Enter thread title"
                    minlength="3"
                    maxlength="150"
                    required
                >

                <label for="content">Thread Content</label>
                <textarea
                    id="content"
                    name="content"
                    placeholder="Write your thoughts... (Use > for greentext)"
                    maxlength="5000"
                    required
                ></textarea>

                <label for="image">Attach Image (Optional, max 2MB)</label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">

                <label>
                    <input type="checkbox" name="is_anon" value="1">
                    Post Thread Anonymously (Mask my username as Anonymous [OP])
                </label>

                <button type="submit">Post Thread</button>
            </form>
        <?php else: ?>
            <p>
                Please <a href="auth.php">log in</a> or <a href="auth.php?mode=register">create an account</a> to start a new thread.
            </p>
        <?php endif; ?>
    </div>

    <!-- Search and Sort Bar -->
    <h2><?= $current_board ? '/' . htmlspecialchars($current_board['slug']) . '/ Discussions' : 'Recent Discussions' ?></h2>

    <div class="recent-box">
        <form method="GET" action="index.php" class="search-sort">
            <?php if ($current_board): ?>
                <input type="hidden" name="board" value="<?= htmlspecialchars($current_board['slug']) ?>">
            <?php endif; ?>

            <label for="search">Search:</label>
            <input
                type="search"
                id="search"
                name="search"
                value="<?= htmlspecialchars($search_query) ?>"
                placeholder="Search discussions..."
            >

            <label for="sort">Sort By:</label>
            <select id="sort" name="sort">
                <option value="bumped" <?= $sort_by === 'bumped' ? 'selected' : '' ?>>Most Bumped</option>
                <option value="newest" <?= $sort_by === 'newest' ? 'selected' : '' ?>>Newest</option>
            </select>

            <button type="submit">Apply</button>
            <?php if ($search_query !== '' || $current_board !== null): ?>
                <a href="index.php" style="margin-left: 8px;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Thread Feed List -->
    <?php if (empty($threads)): ?>
        <div class="recent-box">
            <p>No discussions found. Be the first to start a thread above!</p>
        </div>
    <?php else: ?>
        <?php foreach ($threads as $thread): ?>
            <div class="recent-box" id="p<?= (int)$thread['id'] ?>">
                <h3>
                    <?php if ((int)$thread['is_pinned'] === 1): ?>
                        <span style="color: #183d30;">[PINNED]</span>
                    <?php endif; ?>
                    <?php if ((int)$thread['is_locked'] === 1): ?>
                        <span style="color: #b91c1c;">[LOCKED]</span>
                    <?php endif; ?>
                    <a href="thread.php?id=<?= (int)$thread['id'] ?>" style="text-decoration: none;">
                        <?= htmlspecialchars($thread['title']) ?>
                    </a>
                </h3>

                <p class="thread-info">
                    <?= renderAuthorBadge($thread) ?>
                    | Board: <a href="index.php?board=<?= urlencode($thread['board_slug']) ?>">/<?= htmlspecialchars($thread['board_slug']) ?>/</a>
                    | Post #<?= (int)$thread['id'] ?>
                    | Replies: <?= (int)$thread['reply_count'] ?>
                    | Active: <?= htmlspecialchars($thread['bumped_at']) ?>
                </p>

                <?php if (!empty($thread['image_path'])): ?>
                    <div style="margin: 10px 0;">
                        <img src="<?= htmlspecialchars($thread['image_path']) ?>" alt="Thread attachment" style="max-width: 220px; max-height: 160px; border-radius: 6px; border: 1px solid #ccc;">
                    </div>
                <?php endif; ?>

                <p><?= formatPostBody(mb_strimwidth($thread['content'], 0, 280, '...')) ?></p>

                <a href="thread.php?id=<?= (int)$thread['id'] ?>">Read Discussion (<?= (int)$thread['reply_count'] ?> replies) &rarr;</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>