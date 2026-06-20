-- =============================================================================
-- LOVER LIPS YACHTS — sql/001_create_lly_users.sql
-- Run manually once via phpMyAdmin / cPanel on tourfindycom_lly_db.
-- Not executed automatically — no migration runner exists in this project.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `lly_users` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email`          VARCHAR(191)  NOT NULL UNIQUE,
  `password_hash`  VARCHAR(255)  NOT NULL,
  `remember_token` VARCHAR(64)   NULL,
  `token_expiry`   DATETIME      NULL,
  `created_at`     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
