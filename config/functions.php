<?php
// Check if a user is currently logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if the logged-in user has Moderator privileges
function isMod() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'mod';
}

// Output hidden CSRF token input inside POST forms
function csrfField() {
    $token = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Verify submitted CSRF token on POST requests
function verifyCsrfToken($submittedToken) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$submittedToken);
}

// Asymmetric Anonymity Rule: Decides whether to show "Anonymous [OP]" or "@username"
function renderAuthorBadge($post, $parentThread = null) {
    // 1. Main thread with "Post Anonymously" checked
    if ($post['parent_id'] === null && (int)$post['is_anon'] === 1) {
        return '<span class="badge-anon">Anonymous [OP]</span>';
    }

    // 2. Reply written by the original creator of an anonymous thread
    if ($post['parent_id'] !== null && $parentThread !== null) {
        if ((int)$parentThread['is_anon'] === 1 && (int)$post['user_id'] === (int)$parentThread['user_id']) {
            return '<span class="badge-anon">Anonymous [OP]</span>';
        }
    }

    // 3. Regular accountable post or reply
    $username = htmlspecialchars($post['username'] ?? 'User', ENT_QUOTES, 'UTF-8');
    return '<span class="badge-user">@' . $username . '</span>';
}

// Formats >greentext and >>1001 clickable quote links
function formatPostBody($text) {
    // Escape HTML first to prevent XSS attacks
    $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Turn >>1001 into clickable anchor links that jump to #p1001
    $safe = preg_replace('/&gt;&gt;(\d+)/', '<a href="#p$1" class="quote-link">&gt;&gt;$1</a>', $safe);

    // Turn lines starting with > into green text
    $safe = preg_replace('/^(&gt;.*)$/m', '<span class="greentext">$1</span>', $safe);

    return nl2br($safe);
}
?>