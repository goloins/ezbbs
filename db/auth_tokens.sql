CREATE TABLE auth_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    selector VARCHAR(24) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_auth_selector (selector),
    KEY idx_auth_user_id (user_id),
    KEY idx_auth_expires_at (expires_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;