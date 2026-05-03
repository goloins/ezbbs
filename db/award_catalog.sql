-- Award metadata catalog (source of truth for award name/description/image)
CREATE TABLE award_catalog (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(128) NOT NULL,
    description VARCHAR(255) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_positive TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_award_name (name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO award_catalog (id, name, description, image_url, is_positive) VALUES
(1, 'First Post', 'Awarded for making your first post.', 'assets/png/awards/first_post.png', 1),
(2, 'Kudos', 'Awarded for receiving 15 kudos from other users.', 'assets/png/awards/kudos.png', 1),
(3, 'Veteran', 'Awarded for being a member for 1 year.', 'assets/png/awards/veteran.png', 1),
(4, 'Out on Good Behavior', 'Awarded for being unbanned after a tempban', 'assets/png/awards/out_on_good_behavior.png', 1),
(5, 'Crass Clown', 'Has been dunced by a moderator at least twice.', 'assets/png/awards/crass_clown.png', 0),
(6, 'Moneybags', 'Donated to the website, all you get is the monopoly guy.', 'assets/png/awards/moneybags.png', 1)
ON DUPLICATE KEY UPDATE
name = VALUES(name),
description = VALUES(description),
image_url = VALUES(image_url),
is_positive = VALUES(is_positive);
