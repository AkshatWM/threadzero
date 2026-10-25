# ThreadZero — Master Technical Specification & AI Agent Prompt (`PROJECT_SPEC.md`)

## 1. CRITICAL INSTRUCTIONS FOR AI CODING AGENTS

You are generating code for a B.Tech Web Development Lab project that will also be published on GitHub. You MUST obey these strict simplicity and readability rules:

1. **Keep Code Live-Modification Friendly (Viva Safe):**
   - During the college lab evaluation, the professor will ask students to **modify the code on the fly** (e.g., adding a form field, changing a SQL filter, adding a table column, or altering a validation rule).
   - **DO NOT** write over-engineered, deeply abstracted, or clever one-liner code.
   - **DO NOT** use object-oriented class hierarchies, custom routers, autoloaders, complex closures, or cryptic regexes unless defined in this spec.
   - Write **flat, linear, procedural PHP** and **straightforward Vanilla JS** where every variable has an obvious name (`$thread_id`, `$is_anonymous`, `$search_query`) and every SQL query is written directly above where it is executed.
2. **Tech Stack Constraints:**
   - **Frontend:** Semantic HTML5, Modern CSS3 (CSS Variables, Flexbox, Grid — no Tailwind/Bootstrap build steps), Vanilla JavaScript (ES6, standard DOM APIs, optional `fetch()` for simple enhancements).
   - **Backend:** PHP 8.x using `PDO` prepared statements (`?` placeholders) and native `$_SESSION`.
   - **Database:** MySQL / MariaDB (5 normalized tables).
3. **Comment Every Block for Viva Defense:**
   - Add short, clear comments above every SQL query (`// [SQL SELECT] Fetch all replies for this thread`) and key logic block so any teammate reading the file can explain it in 10 seconds.

---

## 2. Project Identity & Core Architecture

**ThreadZero** is a privacy-first hybrid discussion board and imageboard engine combining:

1. **Dual Authentication (`auth.php`):**
   - **Standard Account:** `username` + `password`.
   - **Mullvad-Style Token Account:** Zero-PII 16-digit account key (`4829-1049-8372-6154`) generated via `random_int()`. Auto-assigns handle `Vault#6154`. Uses SHA-256 (`token_lookup`) for fast login lookup + `password_hash()` for verification.
2. **Asymmetric Anonymity (`index.php` & `thread.php`):**
   - **Parent Threads (`parent_id IS NULL`):** Users can check `is_anon = 1`. Displayed publicly as `Anonymous [OP]`. The database still records `user_id` so the author retains `UPDATE` (edit) and `DELETE` permissions.
   - **Replies (`parent_id IS NOT NULL`):** Always accountable (`@username`) to prevent harassment—**except** when `$reply['user_id'] === $parentThread['user_id']` on an anonymous thread, which auto-masks the reply as `Anonymous [OP]`.
3. **Imageboard Mechanics:**
   - **Bump vs. Sage:** Replies update the parent thread's `bumped_at = NOW()` unless the user checks `Sage (Do not bump)`.
   - **Formatting:** Lines starting with `>` render in green (`.greentext`). References like `>>1002` turn into anchor links (`#p1002`) with click-to-quote support in JS.

---

## 3. Modular File Map / Professional GitHub Directory Layout

```text
threadzero/
│
├── assets/                     # FRONTEND TEAM WORKSPACE
│   ├── css/
│   │   └── style.css           # Main stylesheet (Light & Dark CSS variables)
│   ├── js/
│   │   └── app.js              # Theme toggle, >>quote click helper, form checks
│   └── uploads/                # Stores uploaded thread images (add .gitkeep here)
│
├── config/                     # BACKEND CORE
│   ├── db.php                  # PDO connection + session_start()
│   └── functions.php           # Helper functions (renderAuthorBadge, formatPostBody)
│
├── includes/                   # REUSABLE UI PARTIALS (No copy-pasting navbar!)
│   ├── header.php              # Common <head>, top navbar, board links, theme button
│   └── footer.php              # Common footer + <script src="assets/js/app.js">
│
├── sql/                        # DATABASE TEAM WORKSPACE
│   ├── schema.sql              # The 4 CREATE TABLE statements above
│   └── seed.sql                # Sample boards, demo users, and sample threads
│
├── index.php                   # Route 1: Board feed, Search bar, New Thread form
├── thread.php                  # Route 2: Single thread view, Replies, Bump/Sage, Edit/Delete
├── auth.php                    # Route 3: Standard Login/Signup + 16-Digit Token Generator
├── profile.php                 # Route 4: Edit Bio, Personal Post History, Bookmarks
├── admin.php                   # Route 5: Moderator table (Reported posts, Pin/Lock/Delete)
│
├── .gitignore                  # Ignores assets/uploads/* and local config overrides
├── PROJECT_SPEC.md             # Master specification for AI agents & developers
└── README.md                   # GitHub documentation, ER diagram & screenshots
```

## 4. Database schema (updated, may not be the same as mentioned above)

CREATE DATABASE IF NOT EXISTS threadzero;
USE threadzero;

-- 1. USERS (Standard Username/Pass OR 16-Digit Mullvad Token)
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) UNIQUE NOT NULL, -- e.g., 'arjun_dev' or 'Vault#6154'
password_hash VARCHAR(255) DEFAULT NULL, -- Used for Standard Login
account_token VARCHAR(25) UNIQUE DEFAULT NULL,-- e.g., '4829-1049-8372-6154' (Mullvad-style Login)
bio VARCHAR(255) DEFAULT 'Hello, I use ThreadZero.',
role ENUM('user', 'mod') DEFAULT 'user',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. BOARDS (Topic Categories)
CREATE TABLE boards (
id INT AUTO_INCREMENT PRIMARY KEY,
slug VARCHAR(20) UNIQUE NOT NULL, -- e.g., 'tech', 'science', 'gaming'
title VARCHAR(100) NOT NULL,
description VARCHAR(255) NOT NULL
);

-- 3. POSTS (parent_id IS NULL = Thread, parent_id = ID = Reply)
CREATE TABLE posts (
id INT AUTO_INCREMENT PRIMARY KEY,
board_id INT NOT NULL,
user_id INT NOT NULL,
parent_id INT DEFAULT NULL,
title VARCHAR(150) DEFAULT NULL,
content TEXT NOT NULL,
image_path VARCHAR(255) DEFAULT NULL,
is_anon TINYINT(1) DEFAULT 0, -- 1 = Masked as "Anonymous [OP]"
is_pinned TINYINT(1) DEFAULT 0,
is_locked TINYINT(1) DEFAULT 0,
is_reported TINYINT(1) DEFAULT 0, -- 1 = Flagged for Moderator review
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
