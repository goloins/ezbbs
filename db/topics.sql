CREATE TABLE topics (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT NOT NULL,

    poster_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,

    created_at INT UNSIGNED NOT NULL,
    last_bump INT UNSIGNED NOT NULL,

    replies_count INT UNSIGNED NOT NULL DEFAULT 0,
    visits_count INT UNSIGNED NOT NULL DEFAULT 0,

    media JSON NOT NULL,
    attached_links JSON NOT NULL,

    isPinned TINYINT(1) NOT NULL DEFAULT 0,
    isHidden TINYINT(1) NOT NULL DEFAULT 0,
    isParty TINYINT(1) NOT NULL DEFAULT 0,
    isArchived TINYINT(1) NOT NULL DEFAULT 0,
    isLocked TINYINT(1) NOT NULL DEFAULT 0,
    isLemoned TINYINT(1) NOT NULL DEFAULT 0,
    isChanlike TINYINT(1) NOT NULL DEFAULT 0,

    hasFlairs JSON NOT NULL,
    isShitpost TINYINT(1) NOT NULL DEFAULT 0,
    hasPoll INT UNSIGNED NOT NULL DEFAULT 0,
    tags JSON NOT NULL,

    PRIMARY KEY (id),
    KEY idx_topics_last_bump (last_bump),
    KEY idx_topics_category_last_bump (category_id, last_bump),
    KEY idx_topics_poster_id (poster_id),
    KEY idx_topics_hasPoll (hasPoll)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;