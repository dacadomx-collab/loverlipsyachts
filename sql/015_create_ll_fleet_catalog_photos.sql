-- =============================================================================
-- LOVER LIPS YACHTS — sql/015_create_ll_fleet_catalog_photos.sql
-- Photo gallery for the Fleet Catalog Editor (pg_ai_config.php Section 1).
-- ll_fleet_catalog itself has no image column — a vessel can have several
-- photos, so this is a proper child table (one row per photo) rather than
-- another card_N-style fixed-slot column, matching the "add/edit/delete"
-- CRUD Lester asked for per photo, not per vessel.
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-014).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `ll_fleet_catalog_photos` (
  `id`                INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
  `fleet_catalog_id`  INT UNSIGNED    NOT NULL,
  `photo_path`        VARCHAR(255)    NOT NULL COMMENT 'Relative path from site root, e.g. assets/img/fleet/12/1737654321_ab12cd.webp',
  `display_order`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`         DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_fleet_photos_vessel`
    FOREIGN KEY (`fleet_catalog_id`) REFERENCES `ll_fleet_catalog` (`id`)
    ON DELETE CASCADE,
  INDEX `idx_fleet_photos_vessel_order` (`fleet_catalog_id`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
