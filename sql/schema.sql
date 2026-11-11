-- ThreadZero schema (4 normalized tables)
-- Re-runnable: drops the four tables in dependency order, then recreates them.

CREATE DATABASE IF NOT EXISTS threadzero
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE threadzero;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS boards;
DROP TABLE IF EXISTS users;

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
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. BOARDS (Discussion Categories)
CREATE TABLE boards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(20) UNIQUE NOT NULL,              -- e.g., 'tech', 'science', 'gaming', 'general'
    title VARCHAR(100) NOT NULL,
    description VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT = 1001;

-- 4. BOOKMARKS (Many-to-Many Junction Table: Users <-> Saved Threads)
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_bookmark (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- SEED DEFAULT BOARDS (safe to re-run)
INSERT IGNORE INTO boards (slug, title, description) VALUES
('tech', 'Technology & Systems', 'Software engineering, AI architectures, Linux, and hardware.'),
('science', 'Science & Research', 'Physics, mathematics, space exploration, and breakthroughs.'),
('gaming', 'Interactive Media & Gaming', 'PC/console gaming, game design, emulation, and anime.'),
('general', 'General Discussion', 'Open discussions, Q&A, productivity, and creative projects.');

-- 5. NOTIFICATIONS (User reply notifications)
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    actor_id INT DEFAULT NULL,
    post_id INT NOT NULL,
    thread_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'reply',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (recipient_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (actor_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    FOREIGN KEY (thread_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    INDEX idx_recipient_read (recipient_id, is_read),
    INDEX idx_recipient_created (recipient_id, created_at)

) ENGINE=InnoDB
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
