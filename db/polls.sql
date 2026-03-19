CREATE TABLE polls (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_id  INT UNSIGNED NOT NULL DEFAULT 0,
    reply_id   INT UNSIGNED NOT NULL DEFAULT 0,

    question   VARCHAR(255) NOT NULL,
    option_1   VARCHAR(255) DEFAULT NULL,
    option_2   VARCHAR(255) DEFAULT NULL,
    option_3   VARCHAR(255) DEFAULT NULL,
    option_4   VARCHAR(255) DEFAULT NULL,
    option_5   VARCHAR(255) DEFAULT NULL,

    votes_1    INT UNSIGNED NOT NULL DEFAULT 0,
    votes_2    INT UNSIGNED NOT NULL DEFAULT 0,
    votes_3    INT UNSIGNED NOT NULL DEFAULT 0,
    votes_4    INT UNSIGNED NOT NULL DEFAULT 0,
    votes_5    INT UNSIGNED NOT NULL DEFAULT 0,

    voted      JSON NOT NULL,

    PRIMARY KEY (id),
    KEY idx_polls_thread_id (thread_id),
    KEY idx_polls_reply_id (reply_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;