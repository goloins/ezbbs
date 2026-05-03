CREATE TABLE watchlist (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    thread_id INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL DEFAULT UNIX_TIMESTAMP(),

    PRIMARY KEY (id),
    UNIQUE KEY uniq_watch_user_thread (user_id, thread_id),
    KEY idx_watch_user_id (user_id),
    KEY idx_watch_thread_id (thread_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
