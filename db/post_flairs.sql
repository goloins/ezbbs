-- Create post_flairs table for storing user reactions/flairs on posts (replies or topics)
-- Flairs are user-given reactions like "Funny", "Informative", etc.
CREATE TABLE post_flairs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_id INT UNSIGNED NOT NULL,
    flair_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL DEFAULT UNIX_TIMESTAMP(),
    
    PRIMARY KEY (id),
    KEY idx_post_flairs_thread_id (thread_id),
    KEY idx_post_flairs_flair_id (flair_id),
    KEY idx_post_flairs_user_id (user_id),
    UNIQUE KEY unique_user_flair_per_thread (thread_id, flair_id, user_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
