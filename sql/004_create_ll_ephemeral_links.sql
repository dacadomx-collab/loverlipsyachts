-- =============================================================================
-- LOVER LIPS YACHTS — sql/004_create_ll_ephemeral_links.sql
-- Self-Destruct Link module: private quotes/itineraries shared via a token
-- that expires after a bounded number of views (default 3).
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-003).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `ll_ephemeral_links` (
  `id`              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
  `token`           CHAR(43)        NOT NULL COMMENT 'base64url(random_bytes(32)), URL-safe',
  `title`           VARCHAR(190)    NOT NULL,
  `resource_type`   ENUM('quote','itinerary','custom') NOT NULL DEFAULT 'custom',
  `payload_html`    MEDIUMTEXT      NULL COMMENT 'Self-contained content rendered on view, if not a redirect',
  `target_url`      VARCHAR(500)    NULL COMMENT 'Internal resource to redirect to after the view gate, if not inline payload',
  `max_views`       TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `view_count`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `status`          ENUM('active','expired','revoked') NOT NULL DEFAULT 'active',
  `created_by`      INT UNSIGNED    NOT NULL COMMENT 'lly_users.id',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_viewed_at`  DATETIME        NULL,
  UNIQUE KEY `uq_link_token` (`token`),
  INDEX `idx_link_status` (`status`, `created_at`),
  CONSTRAINT `fk_link_created_by` FOREIGN KEY (`created_by`)
    REFERENCES `lly_users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `chk_link_views` CHECK (`view_count` <= `max_views`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Small generic key/value settings store — used here for the global
-- default_max_views the Owner can change from the dashboard without
-- touching every existing link.
CREATE TABLE IF NOT EXISTS `ll_app_settings` (
  `setting_key`   VARCHAR(80)   NOT NULL PRIMARY KEY,
  `setting_value` VARCHAR(255)  NOT NULL,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ll_app_settings` (`setting_key`, `setting_value`)
VALUES ('ephemeral_link_default_max_views', '3')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;
