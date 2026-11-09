
CREATE TABLE IF NOT EXISTS notifications (
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
