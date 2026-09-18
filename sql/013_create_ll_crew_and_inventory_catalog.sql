-- =============================================================================
-- LOVER LIPS YACHTS — sql/013_create_ll_crew_and_inventory_catalog.sql
-- Inventory Checklist module (checklist.php) — two new admin-managed
-- catalogs, both scoped per vessel via `vessel_name` (matching the same
-- free-text convention already used by ll_inventory_checklists.vessel_name
-- — no FK to ll_fleet_catalog, since only 3/42 vessels exist there today
-- and this module must work for any vessel Lester types in):
--
--   1. Crew roster — ll_crew_roles (global position catalog: Captain,
--      Chef, etc. — add/edit/delete a position type) + ll_crew_members
--      (the actual people assigned to a vessel, each pointing at one role).
--   2. Kitchen utensils / equipment — ll_inventory_catalog (flat per-vessel
--      catalog, no separate lookup table: each row IS one utensil for one
--      vessel, e.g. "Toaster x1 on NOMADA" — add/edit/delete freely).
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-012).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `ll_crew_roles` (
  `id`             INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
  `label_en`       VARCHAR(80)     NOT NULL,
  `label_es`       VARCHAR(80)     NOT NULL,
  `display_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_crew_role_label_en` (`label_en`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ll_crew_members` (
  `id`             INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
  `vessel_name`    VARCHAR(120)    NOT NULL,
  `role_id`        INT UNSIGNED    NOT NULL,
  `full_name`      VARCHAR(150)    NOT NULL,
  `phone`          VARCHAR(30)     NULL,
  `whatsapp`       VARCHAR(30)     NULL,
  `email`          VARCHAR(190)    NULL,
  `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `note`           TEXT            NULL,
  `display_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_crew_members_vessel` (`vessel_name`),
  CONSTRAINT `fk_crew_members_role` FOREIGN KEY (`role_id`) REFERENCES `ll_crew_roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ll_inventory_catalog` (
  `id`               INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
  `vessel_name`      VARCHAR(120)    NOT NULL,
  `category`         VARCHAR(40)     NOT NULL DEFAULT 'kitchen' COMMENT 'kitchen = only category exposed in v1 UI; reserved for future equipment groups (deck, safety, etc.)',
  `name_en`          VARCHAR(120)    NOT NULL,
  `name_es`          VARCHAR(120)    NOT NULL,
  `quantity`         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `condition_status` ENUM('good','fair','damaged','missing') NOT NULL DEFAULT 'good',
  `note`             TEXT            NULL,
  `display_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_inventory_catalog_vessel_category` (`vessel_name`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: the 7 positions named explicitly by the Architect. Editable/deletable
-- from the UI afterwards like any other row — this is a starting point, not
-- a hardcoded list.
INSERT INTO `ll_crew_roles` (`label_en`, `label_es`, `display_order`) VALUES
  ('Captain',      'Capitán',       1),
  ('Deckhand',     'Marinero',      2),
  ('Crew Member',  'Tripulación',   3),
  ('Chef',         'Chef',          4),
  ('Hostess 1',    'Hostess 1',     5),
  ('Hostess 2',    'Hostess 2',     6),
  ('Engineer',     'Ingeniero',     7)
ON DUPLICATE KEY UPDATE `label_en` = `label_en`;

-- Seed: the kitchen utensils named explicitly by the Architect, split into
-- individual rows (not bundled sets) so each can be counted/edited on its
-- own. Scoped to NOMADA — the only vessel checklist.php currently defaults
-- to; add rows for other vessels from the UI as needed.
INSERT INTO `ll_inventory_catalog` (`vessel_name`, `category`, `name_en`, `name_es`, `quantity`, `display_order`) VALUES
  ('NOMADA', 'kitchen', 'Toaster',       'Tostadora',        1,  1),
  ('NOMADA', 'kitchen', 'Coffee Maker',  'Cafetera',         1,  2),
  ('NOMADA', 'kitchen', 'Blender',       'Licuadora',        1,  3),
  ('NOMADA', 'kitchen', 'Spoons',        'Cucharas',        12,  4),
  ('NOMADA', 'kitchen', 'Table Knives',  'Cuchillos de mesa',12,  5),
  ('NOMADA', 'kitchen', 'Forks',         'Tenedores',       12,  6)
ON DUPLICATE KEY UPDATE `name_en` = `name_en`;
