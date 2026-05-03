CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,              -- recipient user
    type VARCHAR(32) NOT NULL,         -- "mention", "trophy", "ban", etc.
    data TEXT NOT NULL,                -- json payload used by do_HandleNotifs()
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at INT NOT NULL DEFAULT UNIX_TIMESTAMP(),
    INDEX (user_id, is_read)
);