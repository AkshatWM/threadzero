<?php
// Harden the session cookie before starting the session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
    session_start();
}

// Generate a CSRF token for this session if one does not exist yet
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Server-side secret key (pepper) used for HMAC-SHA256 token lookups.
// Set THREADZERO_APP_SECRET in the environment for any non-local deployment.
$app_secret = getenv('THREADZERO_APP_SECRET');
define('APP_SECRET', ($app_secret !== false && $app_secret !== '')
    ? $app_secret
    : 'threadzero-dev-secret-change-me');

// Database credentials (default Laragon/XAMPP settings)
$host    = 'localhost';
$dbname  = 'threadzero';
$db_user = 'root';
$db_pass = '';

try {
    // Connect to MySQL using PDO with prepared statements enabled
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Log the real error for the developer, but never expose it to the browser
    error_log('ThreadZero database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('ThreadZero is temporarily unavailable. Please try again later.');
}
