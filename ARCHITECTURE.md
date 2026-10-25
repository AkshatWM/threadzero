# ThreadZero — System Architecture & Developer Guide (`ARCHITECTURE.md`)

> **How to use this document:**
> - **For Teammates:** Read Sections 1–5 to understand how the project works, how the folders are organized, and what you need to prepare for our lab viva.
> - **For AI Coding Assistants (ChatGPT / Claude / Gemini / Cursor / Copilot):** If you are using AI to write or edit a file, attach or paste this entire document and use the prompt template in **Section 7**.

---

## 1. Coding Rules (For Developers & AI Agents)

Whether a human teammate or an AI agent writes code for this repository, it **MUST** follow these strict simplicity rules so everyone can defend and modify the code live during our lab evaluation:

1. **Keep Code Live-Modification Friendly (Viva Safe):**
   - During the lab evaluation, the examiner may ask any team member to **modify the code on the spot** (e.g., adding a new input field, changing a SQL filter, adding a table column, or altering a character limit).
   - **NO over-engineering:** Do not use PHP classes/OOP hierarchies, custom routing engines, autoloaders, closures, or cryptic one-liners.
   - Write **flat, top-to-bottom procedural PHP** and **plain Vanilla JS** with self-explanatory variable names (`$thread_id`, `$is_anon`, `$search_query`).
   - Write every SQL query string directly above the `$pdo->prepare()` line where it runs.
2. **Strict Tech Stack:**
   - **Frontend:** Semantic HTML5, **Tailwind CSS** (utility classes for layout/spacing; loaded via CDN or compiled into `assets/css/style.css`) plus a small custom stylesheet for theme variables (`.greentext`, badges, post cards). **Vanilla JavaScript** (ES6 DOM manipulation). No React/Vue/Bootstrap.
   - **Backend:** PHP 8.x using `PDO` prepared statements (`?` placeholders) and native `$_SESSION`.
   - **Database:** MySQL / MariaDB (Strictly **4 normalized tables**: `users`, `boards`, `posts`, `bookmarks`).
3. **Viva-Ready Comments:**
   - Add a clear 1-line comment above every SQL query (e.g., `// [SQL UPDATE] Bump parent thread to the top of the board`) and key `if/else` block so any teammate can read and explain the logic in 10 seconds.

---

## 2. Project Overview & Core Features

**ThreadZero** is a privacy-first hybrid discussion board and imageboard engine that combines the community structure of Reddit, the fast minimalist thread mechanics of 4chan/Hacker News, and the zero-knowledge login system of Mullvad VPN.

### A. Dual Authentication (`auth.php`)
Users can sign up and log in using two methods stored in the same `users` table:
1. **Standard Account:** Traditional `username` + `password` (hashed via `password_hash()`).
2. **Mullvad-Style Token Account:** One-click generation of a random 16-digit account token (e.g., `4829-1049-8372-6154`) using `random_int()`. The system auto-assigns a handle using the last 4 digits (e.g., `Vault#6154`). The plaintext token is shown **once** at creation; the database stores only `token_lookup = HMAC-SHA256(token, APP_SECRET)` for indexed lookup and `password_hash = password_hash(token, PASSWORD_DEFAULT)` for verification. No email or personal data required.

### B. Asymmetric Anonymity (`index.php` & `thread.php`)
Designed to allow open discussion while preventing anonymous harassment in the comments:
1. **Anonymous Threads (OP):** When starting a new thread (`parent_id IS NULL`), a user can check `Post Thread Anonymously` (`is_anon = 1`). The UI displays `Anonymous [OP]`, while the database still stores `user_id` internally so the author can **Edit (`UPDATE`)** or **Delete (`DELETE`)** their own thread.
2. **Accountable Replies:** Replies (`parent_id IS NOT NULL`) always display the poster's `@username` or `@Vault#6154` handle—**except** if the original creator of an anonymous thread replies inside their own thread, which automatically masks them as `Anonymous [OP]`.

### C. Imageboard Mechanics (`thread.php` & `assets/js/app.js`)
- **Bump vs. Sage:** Posting a reply updates the parent thread's `bumped_at = NOW()` so active discussions rise to the top—unless the user checks `Sage (Do not bump)`.
- **Text Formatting:** Lines starting with `>` render in green (`.greentext`). Typing `>>1002` creates a clickable link (`#p1002`) that jumps to post `#1002`.

---

## 3. Official 4-Table Database Schema (`sql/schema.sql`)

All team members and AI tools must strictly use this 4-table schema:

```sql
CREATE DATABASE IF NOT EXISTS threadzero;
USE threadzero;

-- 1. USERS (Supports Standard Username/Password OR 16-Digit Mullvad Token)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,          -- e.g., 'arjun_dev' or 'Vault#6154'
    password_hash VARCHAR(255) DEFAULT NULL,       -- bcrypt of the password OR of the 16-digit token
    token_lookup CHAR(64) DEFAULT NULL,            -- HMAC-SHA256(token, APP_SECRET); indexed lookup only
    bio VARCHAR(255) DEFAULT 'Hello, I use ThreadZero.',
    role ENUM('user', 'mod') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_token_lookup (token_lookup)  -- fast token login; NULL for standard accounts
);

-- 2. BOARDS (Discussion Categories)
CREATE TABLE boards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(20) UNIQUE NOT NULL,              -- e.g., 'tech', 'science', 'gaming', 'general'
    title VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL
);

-- 3. POSTS (Self-Referencing: parent_id IS NULL = Thread, parent_id = ID = Reply)
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_id INT NOT NULL,
    user_id INT NOT NULL,
    parent_id INT DEFAULT NULL,
    title VARCHAR(150) DEFAULT NULL,               -- NULL for replies
    content TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    is_anon TINYINT(1) DEFAULT 0,                  -- 1 = Masked as "Anonymous [OP]"
    is_pinned TINYINT(1) DEFAULT 0,
    is_locked TINYINT(1) DEFAULT 0,
    is_reported TINYINT(1) DEFAULT 0,              -- 1 = Flagged for Moderator review
    bumped_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES posts(id) ON DELETE CASCADE
) AUTO_INCREMENT = 1000;

-- 4. BOOKMARKS (Many-to-Many Junction Table: Users <-> Saved Threads)
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- SEED DEFAULT BOARDS
INSERT INTO boards (slug, title, description) VALUES
('tech', 'Technology & Systems', 'Software engineering, AI architectures, Linux, and hardware.'),
('science', 'Science & Research', 'Physics, mathematics, space exploration, and breakthroughs.'),
('gaming', 'Interactive Media & Gaming', 'PC/console gaming, game design, emulation, and anime.'),
('general', 'General Discussion', 'Open discussions, Q&A, productivity, and creative projects.');

```

### 3.1 Privacy & Security Rules (Non-Negotiable)

These rules protect the token-login and anonymity model. Do not skip them "to save time".

1. **Never store secrets in plaintext.** Passwords and 16-digit tokens are stored only as `password_hash()` output. The token itself is shown **once** at signup and cannot be recovered.
2. **Use a keyed lookup hash.** `token_lookup = hash_hmac('sha256', $token, APP_SECRET)` lets login find the row in one indexed query without storing a reversible value. If the database leaks, the attacker still needs `APP_SECRET` (kept in `config/db.php`, never committed to a public repo) to brute-force the lookup.
3. **Verify, don't compare.** After the lookup, run `password_verify($token, $row['password_hash'])` before creating the session.
4. **Generic error messages.** Always show "Invalid username or token" — never reveal whether an account exists.
5. **Harden the session.** Call `session_regenerate_id(true)` immediately after login, and set cookies `HttpOnly`, `SameSite=Lax`, and `Secure` in production.
6. **CSRF protection.** Every `POST` form includes a hidden token compared against `$_SESSION['csrf_token']`.
7. **Escape output.** Every user string is printed through `htmlspecialchars()`. Every query uses PDO `?` placeholders.
8. **Minimise data.** No emails, phone numbers, or IP logging. `users` stores only `username`, `password_hash`, `token_lookup`, `bio`, `role`, `created_at`.
9. **Rate-limit login.** After 5 failed attempts in one session, add a short `sleep()` delay and show a warning.
10. **Be honest about anonymity.** `is_anon = 1` hides the author from other users, but `user_id` is still stored so the author can edit/delete and moderators can act. It is pseudonymity, not untraceability — say this clearly in the viva.

---

## 4. Core Shared Code (`config/db.php` & `config/functions.php`)

Every root page (`index.php`, `thread.php`, `auth.php`, `profile.php`, `admin.php`) includes these two files at the top:

### `config/db.php`

```php
<?php
// Harden the session cookie before starting the session
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

// Server-side pepper for token lookup hashes.
// Change this for any real deployment and never commit the real value publicly.
define('APP_SECRET', 'threadzero-dev-secret-change-me');

$host = 'localhost';$dbname = 'threadzero';
$db_user = 'root';$db_pass = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user,$db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>

```

### `config/functions.php`

```php
<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isMod() {
    return isset($_SESSION['role']) &&$_SESSION['role'] === 'mod';
}

// Decides whether to show "Anonymous [OP]" or "@username"
function renderAuthorBadge($post,$parentThread = null) {
    // 1. Main thread with "Post Anonymously" checked
    if ($post['parent_id'] === null && (int)$post['is_anon'] === 1) {
        return '<span class="badge-anon">Anonymous [OP]</span>';
    }
    // 2. Reply written by the creator of an anonymous thread
    if ($post['parent_id'] !== null &&$parentThread !== null) {
        if ((int)$parentThread['is_anon'] === 1 && (int)$post['user_id'] === (int)$parentThread['user_id']) {
            return '<span class="badge-anon">Anonymous [OP]</span>';
        }
    }
    // 3. Regular accountable post/reply
    return '<span class="badge-user">@' . htmlspecialchars($post['username']) . '</span>';
}

// Formats >greentext and >>1001 quote links
function formatPostBody($text) {$safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');$safe = preg_replace('/&gt;&gt;(\d+)/', '<a href="#p$1" class="quote-link">&gt;&gt;$1</a>',$safe);
    $safe = preg_replace('/^(&gt;.*)$/m', '<span class="greentext">$1</span>',$safe);
    return nl2br($safe);
}
?>
```

---

## 5. Modular Feature Specifications

Any developer can pick up and build one of these self-contained modules:

* **Module 1: `auth.php` (Dual Login & Registration)**
* **Standard Signup:** Inserts `username` and `password_hash($pass, PASSWORD_DEFAULT)` into `users`.
* **Mullvad Token Generator:** Builds `$token = sprintf('%04d-%04d-%04d-%04d', random_int(0,9999), random_int(0,9999), random_int(0,9999), random_int(0,9999));`, then `$lookup = hash_hmac('sha256', $token, APP_SECRET)` and `$token_hash = password_hash($token, PASSWORD_DEFAULT)`. Inserts the handle (`Vault#` + last 4 digits, with a random suffix if taken), `password_hash = $token_hash`, and `token_lookup = $lookup`. The plaintext token is displayed **once** with a copy button and a warning to save it.
* **Token Login:** Normalises the input (strip spaces and dashes), validates it is 16 digits, runs `SELECT * FROM users WHERE token_lookup = ?`, then verifies with `password_verify($token, $row['password_hash'])`. On success: `session_regenerate_id(true)`, set `$_SESSION['user_id']` and `$_SESSION['role']`.
* **Login hardening:** Generic error message for both flows, and a `sleep(1)` delay after repeated failures in the same session.


* **Module 2: `index.php` (Board Feed, Search & New Thread)**
* Displays boards from `boards` table, a keyword search input (`WHERE p.content LIKE ? OR p.title LIKE ?`), and a sort dropdown (`bumped_at DESC`, `created_at DESC`).
* Includes the "New Thread" form (`title`, `content`, optional image upload to `assets/uploads/`, and `is_anon` checkbox).
* Counts replies per thread dynamically or via subquery and lists threads ordered by `is_pinned DESC, bumped_at DESC`.


* **Module 3: `thread.php` (Replies, Bump/Sage, Edit/Delete, Report)**
* Fetches parent thread (`WHERE id = ? AND parent_id IS NULL`) and all replies (`WHERE parent_id = ? ORDER BY created_at ASC`).
* Reply form has a `Sage (Don't bump)` checkbox. If unchecked, runs `UPDATE posts SET bumped_at = NOW() WHERE id = ?`.
* Shows inline **Edit** (`UPDATE posts SET content = ?`) and **Delete** (`DELETE FROM posts WHERE id = ?`) buttons if `$_SESSION['user_id'] == $post['user_id']`.
* Includes a one-click **Bookmark** button (`INSERT INTO bookmarks`) and **Report** button (`UPDATE posts SET is_reported = 1 WHERE id = ?`).


* **Module 4: `profile.php` (User Dashboard)**
* Form to update `bio` (`UPDATE users SET bio = ? WHERE id = ?`).
* Lists all threads/replies created by `$_SESSION['user_id']` (even anonymous ones).
* Lists saved bookmarks (`SELECT ... FROM bookmarks JOIN posts`) with a Remove button (`DELETE FROM bookmarks`).


* **Module 5: `admin.php` (Moderator Control Panel)**
* Accessible when `isMod()` is true.
* Shows an HTML `<table>` of flagged posts (`WHERE is_reported = 1`) and threads, with form buttons to **Approve/Unflag** (`SET is_reported = 0`), **Pin/Unpin** (`SET is_pinned = NOT is_pinned`), **Lock/Unlock** (`SET is_locked = NOT is_locked`), or **Delete** (`DELETE FROM posts WHERE id = ?`).



---

## 6. Team Viva Preparation Checklist (By Role)

### A. Frontend Developers (2 Members — `assets/css/style.css`, `assets/js/app.js`, `includes/header.php`)

* **Know how to explain:** Tailwind utility classes vs. your custom CSS variables (`--bg`, `--card-bg`), how `localStorage.getItem('theme')` switches Dark/Retro mode, HTML form `method="POST"` and `enctype="multipart/form-data"`, and the `quotePost(id)` JS function that inserts `>>1001` into the reply box.
* **Practice live changes:** Changing theme colors, adding a minimum character length check in JS, or adding a `confirm()` dialog on delete buttons.

### B. Backend Developers (2 Members — `auth.php`, `index.php`, `thread.php`, `profile.php`, `admin.php`)

* **Know how to explain:** PHP `$_SESSION` lifecycle and `session_regenerate_id(true)` after login, PDO prepared statements (`?` placeholders) vs. SQL injection, how `random_int()` creates the 16-digit token, why the DB stores `token_lookup` (HMAC-SHA256 with `APP_SECRET`) and a `password_hash()` instead of the raw token, and how `renderAuthorBadge()` checks `$parentThread['is_anon']`.
* **Practice live changes:** Adding a new field to the profile page, restricting an action to moderators only, or changing the `>greentext` regex character.

### C. Database Architect (1 Member — `schema.sql`, `seed.sql`, SQL Queries)

* **Know how to explain:** Primary vs. Foreign Keys across the 4 tables, the self-referencing Foreign Key in `posts` (`parent_id` pointing to `posts.id`), how `ON DELETE CASCADE` cleans up replies and bookmarks automatically, why reports are a boolean flag (`posts.is_reported`) instead of a fifth table, and `JOIN` queries between `posts` and `users`.
* **Practice live changes:** Running `GROUP BY` queries in phpMyAdmin to count posts per user, or adding a `views` column to `posts`.

---

## 7. Copy-Paste Prompt for Teammates Using AI

When you want an AI assistant (ChatGPT, Claude, Gemini, Cursor) to write or modify your assigned file, copy this entire `ARCHITECTURE.md` file into the chat and add this prompt at the bottom:

> "Act as a helpful developer following the `ARCHITECTURE.md` specification above. Write the complete, self-contained code for `[INSERT FILENAME, e.g., profile.php]`. Follow all rules in Section 1: use flat procedural PHP with PDO prepared statements, Tailwind utility classes plus the custom theme CSS, and clear comments above every SQL query (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) so I can easily explain and modify the code during my college viva."