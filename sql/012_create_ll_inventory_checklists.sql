-- =============================================================================
-- LOVER LIPS YACHTS — sql/012_create_ll_inventory_checklists.sql
-- Catamaran Inventory Checklist (checklist.php) — persistent "bitácora" of
-- every pre/post-charter inspection, one row per submitted checklist.
--
-- payload_json holds the full item-level state (status/count/notes per row,
-- keyed by the stable data-field-id the front-end assigns at render time —
-- see assets/js/main.js §9c). Item labels are NOT duplicated into the DB;
-- checklist.php re-derives them from its own client-side section data when
-- rendering a historial record, same principle as keeping the item catalog
-- defined in exactly one place.
--
-- search_blob is a server-side concatenation (vessel/captain/checked_by/
-- missing_report/required_actions/every non-empty item note) built by
-- api/inventory_checklists.php at save time, so the Historial keyword search
-- can hit "all fields" via one FULLTEXT index instead of per-field filters.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-011).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `ll_inventory_checklists` (
  `id`                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `vessel_name`        VARCHAR(120) NOT NULL DEFAULT 'NOMADA',
  `charter_date`       DATE NULL,
  `inspection_mode`    ENUM('before','after') NOT NULL DEFAULT 'before',
  `guests_count`       SMALLINT UNSIGNED NULL,
  `captain_name`       VARCHAR(120) NULL,
  `checked_by`         VARCHAR(120) NULL,
  `good_count`         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `damaged_count`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `missing_count`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `replace_count`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `missing_report`     TEXT NULL,
  `required_actions`   TEXT NULL,
  `captain_signature`  VARCHAR(160) NULL,
  `signed_at`          DATETIME NULL,
  `payload_json`       LONGTEXT NOT NULL,
  `search_blob`        TEXT NULL,
  `created_by_user_id` INT UNSIGNED NULL COMMENT 'lly_users.id of whoever was logged in when this was saved',
  `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_vessel` (`vessel_name`),
  INDEX `idx_charter_date` (`charter_date`),
  FULLTEXT INDEX `idx_search_blob` (`search_blob`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
