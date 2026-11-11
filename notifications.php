<?php
require_once 'config/db.php';
require_once 'config/functions.php';

if (!isLoggedIn()) {
    header('Location: auth.php');
    exit;
}

$current_user_id = (int) $_SESSION['user_id'];
$success = '';

// Pick up a one-shot flash message after a redirect
if (!empty($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

    if ($action === 'mark_read') {

        $notification_id = (int) ($_POST['notification_id'] ?? 0);

        // [SQL UPDATE] Mark one notification as read for its recipient
        $stmt = $pdo->prepare(
            "UPDATE notifications
             SET is_read = 1
             WHERE id = ? AND recipient_id = ?"
        );

        $stmt->execute([$notification_id, $current_user_id]);
        $_SESSION['flash_success'] = 'Notification marked as read.';

    } elseif ($action === 'mark_all_read') {

        // [SQL UPDATE] Mark all unread notifications as read for the logged-in user
        $stmt = $pdo->prepare(
            "UPDATE notifications
             SET is_read = 1
             WHERE recipient_id = ? AND is_read = 0"
        );

        $stmt->execute([$current_user_id]);
        $_SESSION['flash_success'] = 'All notifications marked as read.';

    } elseif ($action === 'delete') {

        $notification_id = (int) ($_POST['notification_id'] ?? 0);

        // [SQL DELETE] Delete a notification belonging to the logged-in user
        $stmt = $pdo->prepare(
            "DELETE FROM notifications
             WHERE id = ? AND recipient_id = ?"
        );

        $stmt->execute([$notification_id, $current_user_id]);
        $_SESSION['flash_success'] = 'Notification deleted.';
    }

    header('Location: notifications.php');
    exit;
}

// [SQL SELECT] Fetch notifications belonging to the logged-in user
$sql = "SELECT
            n.id,
            n.post_id,
            n.thread_id,
            n.is_read,
            n.created_at,
            u.username AS actor_username,
            p.content AS reply_content,
            t.title AS thread_title
        FROM notifications n
        LEFT JOIN users u ON n.actor_id = u.id
        INNER JOIN posts p ON n.post_id = p.id
        INNER JOIN posts t ON n.thread_id = t.id
        WHERE n.recipient_id = ?
        ORDER BY n.created_at DESC
        LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute([$current_user_id]);
$notifications = $stmt->fetchAll();

$unread_count = countUnreadNotifications($pdo, $current_user_id);

$page_title = 'Notifications - ThreadZero';
$body_class = 'notifications-page';

require_once 'includes/header.php';
?>

<main class="home-content">

    <section class="profile-box notification-heading">

        <h1>Notifications</h1>

        <?php if ($success !== ''): ?>
            <div class="form-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <p>
            You have <?= $unread_count ?> unread
            <?= $unread_count === 1 ? 'notification' : 'notifications' ?>.
        </p>

        <?php if ($unread_count > 0): ?>
            <form method="POST" action="notifications.php">
                <?= csrfField() ?>

                <input type="hidden"
                       name="action"
                       value="mark_all_read">

                <button type="submit" class="profile-button">
                    Mark All as Read
                </button>
            </form>
        <?php endif; ?>

    </section>

    <section class="recent-box notification-list">

        <?php if (empty($notifications)): ?>

            <p>No notifications yet.</p>

        <?php else: ?>

            <?php foreach ($notifications as $notification): ?>

                <article class="notification-card <?= (int)$notification['is_read'] === 0 ? 'notification-unread' : '' ?>">

                    <p>
                        <strong>
                            @<?= htmlspecialchars($notification['actor_username'] ?? 'Deleted User') ?>
                        </strong>
                        replied to your thread.
                    </p>

                    <p class="notification-thread-title">
                        <?= htmlspecialchars($notification['thread_title'] ?? 'Untitled Thread') ?>
                    </p>

                    <p class="notification-content">
                        <?= htmlspecialchars(
                            mb_strimwidth(
                                $notification['reply_content'] ?? '',
                                0,
                                150,
                                '...'
                            )
                        ) ?>
                    </p>

                    <small class="notification-date">
                        <?= htmlspecialchars($notification['created_at']) ?>
                    </small>

                    <div class="notification-actions">

                        <a href="thread.php?id=<?= (int)$notification['thread_id'] ?>#p<?= (int)$notification['post_id'] ?>">
                            View Reply
                        </a>

                        <?php if ((int)$notification['is_read'] === 0): ?>

                            <form method="POST" action="notifications.php">
                                <?= csrfField() ?>

                                <input type="hidden"
                                       name="action"
                                       value="mark_read">

                                <input type="hidden"
                                       name="notification_id"
                                       value="<?= (int)$notification['id'] ?>">

                                <button type="submit">
                                    Mark as Read
                                </button>
                            </form>

                        <?php endif; ?>

                        <form method="POST" action="notifications.php" onsubmit="return confirm('Delete this notification?');">
                            <?= csrfField() ?>

                            <input type="hidden"
                                   name="action"
                                   value="delete">

                            <input type="hidden"
                                   name="notification_id"
                                   value="<?= (int)$notification['id'] ?>">

                            <button type="submit">
                                Delete
                            </button>
                        </form>

                    </div>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </section>

</main>

<?php require_once 'includes/footer.php'; ?>
