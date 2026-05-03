CREATE TABLE private_messages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    from_user_id INT UNSIGNED NOT NULL,
    to_user_id INT UNSIGNED NOT NULL,
    thread_id INT UNSIGNED NOT NULL DEFAULT 0,
    subject VARCHAR(255) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    created_at INT UNSIGNED NOT NULL DEFAULT UNIX_TIMESTAMP(),
    is_read TINYINT(1) NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    KEY idx_pm_to_user_id (to_user_id, is_read, created_at),
    KEY idx_pm_from_user_id (from_user_id, created_at),
    KEY idx_pm_thread_id (thread_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
