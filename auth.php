<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// 1. Handle Logout (POST + CSRF only, so a third-party page cannot log users out)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION = [];

        // Expire the session cookie as well as the server-side session data
        if (ini_get('session.use_cookies')) {
            $cookie = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $cookie['path'],
                $cookie['domain'],
                $cookie['secure'],
                $cookie['httponly']
            );
        }

        session_destroy();
    }

    header('Location: auth.php');
    exit;
}

// 2. One-time token reveal (GET after the POST/Redirect/GET cycle)
$generated_token  = '';
$generated_handle = '';

if (isset($_GET['show_token']) && !empty($_SESSION['generated_token'])) {
    $generated_token  = $_SESSION['generated_token'];
    $generated_handle = $_SESSION['generated_handle'] ?? '';

    // The token must never be recoverable after this render
    unset($_SESSION['generated_token'], $_SESSION['generated_handle']);

    // Keep the token out of browser caches and history
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// 3. Redirect signed-in users away from the auth page, unless showing a fresh token
if (isLoggedIn() && $generated_token === '') {
    header('Location: index.php');
    exit;
}

$mode = (isset($_GET['mode']) && $_GET['mode'] === 'register') ? 'register' : 'login';
$error = '';

// Track failed login attempts in this session for rate-limiting
if (!isset($_SESSION['failed_logins'])) {
    $_SESSION['failed_logins'] = 0;
}

// 4. Handle Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_action = $_POST['action'] ?? '';

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid session token. Please refresh the page and try again.';
    } else {

        // --- FLOW A: STANDARD USERNAME + PASSWORD LOGIN ---
        if ($form_action === 'login') {
            if ($_SESSION['failed_logins'] >= 5) {
                sleep(1); // Rate-limit delay after 5 failed attempts
            }

            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // [SQL SELECT] Fetch user account by username
            $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                $_SESSION['failed_logins'] = 0;
                session_regenerate_id(true);
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                header('Location: index.php');
                exit;
            }

            $_SESSION['failed_logins']++;
            $error = ($_SESSION['failed_logins'] >= 5)
                ? 'Too many failed attempts. Please wait a moment before trying again.'
                : 'Invalid username or token.';
        }

        // --- FLOW B: 16-DIGIT TOKEN LOGIN ---
        elseif ($form_action === 'token_login') {
            if ($_SESSION['failed_logins'] >= 5) {
                sleep(1); // Rate-limit delay after 5 failed attempts
            }

            // Strip spaces and hyphens to normalize into 16 raw digits
            $raw_input = preg_replace('/[\s\-]+/', '', $_POST['token'] ?? '');

            if (!preg_match('/^\d{16}$/', $raw_input)) {
                $_SESSION['failed_logins']++;
                $error = 'Invalid username or token.';
            } else {
                // Re-format into XXXX-XXXX-XXXX-XXXX to match generation format
                $formatted_token = implode('-', str_split($raw_input, 4));
                $lookup_hash     = hash_hmac('sha256', $formatted_token, APP_SECRET);

                // [SQL SELECT] Fast indexed lookup using HMAC-SHA256 token_lookup
                $sql = "SELECT * FROM users WHERE token_lookup = ? LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$lookup_hash]);
                $user = $stmt->fetch();

                if ($user && !empty($user['password_hash']) && password_verify($formatted_token, $user['password_hash'])) {
                    $_SESSION['failed_logins'] = 0;
                    session_regenerate_id(true);
                    $_SESSION['user_id']  = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role']     = $user['role'];
                    header('Location: index.php');
                    exit;
                }

                $_SESSION['failed_logins']++;
                $error = ($_SESSION['failed_logins'] >= 5)
                    ? 'Too many failed attempts. Please wait a moment before trying again.'
                    : 'Invalid username or token.';
            }
        }

        // --- FLOW C: STANDARD USERNAME + PASSWORD REGISTRATION ---
        elseif ($form_action === 'signup') {
            $mode     = 'register';
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';

            if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
                $error = 'Username must be 3-20 characters using only letters, numbers and underscores.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                // [SQL SELECT] Check if the username is already taken
                $check_sql = "SELECT id FROM users WHERE username = ? LIMIT 1";
                $check_stmt = $pdo->prepare($check_sql);
                $check_stmt->execute([$username]);

                if ($check_stmt->fetch()) {
                    $error = 'That username is already taken.';
                } else {
                    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

                    if ($pass_hash === false) {
                        $error = 'Could not secure your password. Please try again.';
                    } else {
                        // [SQL INSERT] Create new standard user account
                        $insert_sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
                        $insert_stmt = $pdo->prepare($insert_sql);
                        $insert_stmt->execute([$username, $pass_hash]);

                        $_SESSION['failed_logins'] = 0;
                        session_regenerate_id(true);
                        $_SESSION['user_id']  = $pdo->lastInsertId();
                        $_SESSION['username'] = $username;
                        $_SESSION['role']     = 'user';
                        header('Location: index.php');
                        exit;
                    }
                }
            }
        }

        // --- FLOW D: 16-DIGIT TOKEN GENERATION ---
        elseif ($form_action === 'token_signup') {
            $mode = 'register';

            // [SQL INSERT] Retry on a handle or token_lookup collision (SQLSTATE 23000)
            $insert_sql  = "INSERT INTO users (username, password_hash, token_lookup) VALUES (?, ?, ?)";
            $insert_stmt = $pdo->prepare($insert_sql);

            $created = false;
            for ($attempt = 0; $attempt < 5 && !$created; $attempt++) {
                // Generate 16 random digits formatted as XXXX-XXXX-XXXX-XXXX
                $token = sprintf(
                    '%04d-%04d-%04d-%04d',
                    random_int(0, 9999),
                    random_int(0, 9999),
                    random_int(0, 9999),
                    random_int(0, 9999)
                );

                $handle     = 'Vault#' . substr($token, -4);
                $token_hash = password_hash($token, PASSWORD_DEFAULT);

                if ($token_hash === false) {
                    $error = 'Could not create a secure token. Please try again.';
                    break;
                }

                $lookup_hash = hash_hmac('sha256', $token, APP_SECRET);

                try {
                    $insert_stmt->execute([$handle, $token_hash, $lookup_hash]);
                    $created = true;
                } catch (PDOException $e) {
                    // 23000 = duplicate handle or duplicate token_lookup: try a new token
                    if ($e->getCode() !== '23000') {
                        error_log('Token signup failed: ' . $e->getMessage());
                        $error = 'Could not create your account. Please try again.';
                        break;
                    }
                }
            }

            if (!$created && $error === '') {
                $error = 'Could not allocate a unique account token. Please try again.';
            }

            if ($created) {
                $_SESSION['failed_logins'] = 0;
                session_regenerate_id(true);
                $_SESSION['user_id']  = $pdo->lastInsertId();
                $_SESSION['username'] = $handle;
                $_SESSION['role']     = 'user';

                // Stash the token for one GET render, then discard it
                $_SESSION['generated_token']  = $token;
                $_SESSION['generated_handle'] = $handle;

                header('Location: auth.php?mode=register&show_token=1');
                exit;
            }
        }
    }
}

$is_register_view = ($mode === 'register' || $generated_token !== '');

$page_title = $is_register_view ? 'Register - ThreadZero' : 'Login - ThreadZero';
$body_class = $is_register_view ? 'register-page' : 'login-page';
$card_class = $is_register_view ? 'register-box' : 'login-card';
include 'includes/header.php';
?>

<main class="login-container">
    <div class="<?= $card_class ?>">

        <?php if ($error !== ''): ?>
            <div class="form-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($generated_token !== ''): ?>
            <!-- ONE-TIME TOKEN DISPLAY (shown once, then the session copy is deleted) -->
            <h1>Save Your Account Token!</h1>
            <p class="login-subtitle">
                Your anonymous handle is <strong>@<?= htmlspecialchars($generated_handle) ?></strong>.
                Copy this 16-digit token now.
            </p>

            <div class="token-reveal">
                <p class="token-warning">
                    This token is shown <strong>only once</strong> and cannot be recovered.
                </p>
                <div class="token-value" id="generated-token-text"><?= htmlspecialchars($generated_token) ?></div>

                <button
                    type="button"
                    class="login-button"
                    onclick="navigator.clipboard.writeText(document.getElementById('generated-token-text').innerText); this.innerText='Copied to Clipboard!';"
                >
                    Copy 16-Digit Token
                </button>

                <a href="index.php" class="home-button">I Have Saved My Token &rarr; Continue</a>
            </div>

        <?php elseif ($mode === 'login'): ?>
            <!-- ================= LOGIN VIEW ================= -->
            <h1>Welcome Back</h1>
            <p class="login-subtitle">Log in to join the conversation.</p>

            <!-- Standard Username & Password Login -->
            <section class="login-section">
                <h2>Username Login</h2>
                <form method="POST" action="auth.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="login">

                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username"
                        autocomplete="username"
                        maxlength="20"
                        required
                    >

                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button type="submit" class="login-button">Log In</button>
                </form>
            </section>

            <!-- 16-Digit Token Login -->
            <section class="login-section token-section">
                <h2>Token Login</h2>
                <p>Log in using your 16-digit ThreadZero token.</p>

                <form method="POST" action="auth.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="token_login">

                    <label for="token">16-Digit Token</label>
                    <input
                        type="text"
                        id="token"
                        name="token"
                        placeholder="4829-1049-8372-6154"
                        maxlength="19"
                        inputmode="numeric"
                        autocomplete="off"
                        title="Enter your 16-digit token, with or without hyphens"
                        required
                    >

                    <button type="submit" class="login-button">Login with Token</button>
                </form>
            </section>

            <p class="register-text">
                Don't have an account?
                <a href="auth.php?mode=register">Create one</a>
            </p>

        <?php else: ?>
            <!-- ================= REGISTER VIEW ================= -->
            <h1>Create Account</h1>
            <p class="login-subtitle">Join ThreadZero with a username or an instant 16-digit token.</p>

            <!-- Standard Username Registration -->
            <section class="login-section">
                <h2>Username Registration</h2>
                <form method="POST" action="auth.php?mode=register">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="signup">

                    <label for="reg_username">Username</label>
                    <input
                        type="text"
                        id="reg_username"
                        name="username"
                        placeholder="3-20 letters, numbers or underscores"
                        autocomplete="username"
                        pattern="[A-Za-z0-9_]{3,20}"
                        minlength="3"
                        maxlength="20"
                        title="3-20 characters: letters, numbers and underscores only"
                        required
                    >

                    <label for="reg_password">Password</label>
                    <input
                        type="password"
                        id="reg_password"
                        name="password"
                        placeholder="Enter password (min 8 characters)"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Enter password again"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button type="submit" class="login-button">Register</button>
                </form>
            </section>

            <!-- 16-Digit Token Generation -->
            <section class="login-section token-section">
                <h2>16-Digit Token Registration</h2>
                <p>Generate a unique, zero-PII 16-digit token for instant anonymous access.</p>

                <form method="POST" action="auth.php?mode=register">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="token_signup">
                    <button type="submit" class="login-button">Generate 16-Digit Token</button>
                </form>
            </section>

            <p class="register-text">
                Already have an account?
                <a href="auth.php">Login</a>
            </p>
        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>
