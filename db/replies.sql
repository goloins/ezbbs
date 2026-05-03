-- Create replies table for storing thread replies
CREATE TABLE replies (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_id INT UNSIGNED NOT NULL,
    poster_id INT UNSIGNED NOT NULL,
    content MEDIUMTEXT NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    
    media JSON NOT NULL,
    attached_links JSON NOT NULL,
    
    poll_id INT UNSIGNED NOT NULL DEFAULT 0,
    isShitpost TINYINT(1) NOT NULL DEFAULT 0,
    isHidden TINYINT(1) NOT NULL DEFAULT 0,
    isModerator TINYINT(1) NOT NULL DEFAULT 0,
    contentSpoilered TINYINT(1) NOT NULL DEFAULT 0,
    causedBan TINYINT(1) NOT NULL DEFAULT 0,
    is_edited TINYINT(1) NOT NULL DEFAULT 0,
    edited_at INT UNSIGNED NOT NULL DEFAULT 0,
    
    PRIMARY KEY (id),
    KEY idx_replies_thread_id (thread_id),
    KEY idx_replies_poster_id (poster_id),
    KEY idx_replies_created_at (created_at),
    KEY idx_replies_poll_id (poll_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
