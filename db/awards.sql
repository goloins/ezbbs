-- Create awards table for tracking which awards users have earned
CREATE TABLE awards (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    award_id INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL DEFAULT UNIX_TIMESTAMP(),
    
    PRIMARY KEY (id),
    KEY idx_awards_user_id (user_id),
    KEY idx_awards_award_id (award_id),
    UNIQUE KEY unique_user_award (user_id, award_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
