-- =============================================================================
-- LOVER LIPS YACHTS — sql/014_extend_ll_fleet_catalog_operational_specs.sql
-- Extends the EXISTING `ll_fleet_catalog` (sql/005) with operational specs
-- needed by the Inventory Checklist module (checklist.php) — cabins,
-- bathrooms, crew capacity, engines, fuel/water capacity, home marina,
-- registration number. Deliberately extends the same table instead of a
-- parallel "fleet" table (Mandamiento 10 — one system, not two): the
-- marketing-facing columns (rate_note, status_pill, verification_status)
-- and these operational columns describe the same real-world object (one
-- vessel), just consumed by two different actors (AI chatbot/marketing
-- vs. checklist.php operations). No existing consumer breaks — every
-- current SELECT lists its columns explicitly, none use SELECT *.
--
-- All new columns are NULLable and default NULL — "unknown, not yet
-- verified" (same convention as the original max_pax/length_ft columns'
-- own comment: "NULL = capacity not yet verified — never render as 0 or
-- a guess"). No operational spec is invented here for any vessel
-- (Mandamiento 4 — Anti-Alucinación) — a human fills them in via the
-- Fleet Catalog Editor (pg_ai_config.php) as each fact is confirmed.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-013).
-- =============================================================================

ALTER TABLE `ll_fleet_catalog`
  ADD COLUMN `cabins_count`         SMALLINT UNSIGNED NULL COMMENT 'Bedrooms/cabins — feeds checklist.php Cabins section unit count' AFTER `length_ft`,
  ADD COLUMN `bathrooms_count`      SMALLINT UNSIGNED NULL COMMENT 'Heads — feeds checklist.php Bathrooms section unit count' AFTER `cabins_count`,
  ADD COLUMN `crew_capacity`        SMALLINT UNSIGNED NULL COMMENT 'Normal crew complement — cross-reference with ll_crew_members headcount' AFTER `bathrooms_count`,
  ADD COLUMN `beam_ft`              SMALLINT UNSIGNED NULL COMMENT 'Width in feet, complements length_ft' AFTER `crew_capacity`,
  ADD COLUMN `year_built`           SMALLINT UNSIGNED NULL AFTER `beam_ft`,
  ADD COLUMN `engine_notes`         VARCHAR(255) NULL COMMENT 'Free text: make/model/count/HP — no separate engine table, not worth the join for a descriptive fact' AFTER `year_built`,
  ADD COLUMN `fuel_capacity_gal`    SMALLINT UNSIGNED NULL AFTER `engine_notes`,
  ADD COLUMN `water_capacity_gal`   SMALLINT UNSIGNED NULL AFTER `fuel_capacity_gal`,
  ADD COLUMN `home_marina`          VARCHAR(120) NULL COMMENT 'Berth/marina location' AFTER `water_capacity_gal`,
  ADD COLUMN `registration_number`  VARCHAR(60) NULL COMMENT 'Hull ID / official registration — legal & insurance reference' AFTER `home_marina`;

-- Seed: NOMADA, the vessel already operated by the Inventory Checklist
-- module (checklist.php default, 6 kitchen utensils already catalogued in
-- ll_inventory_catalog) but never previously a row in ll_fleet_catalog —
-- that table only had the 3 marketing-verified vessels. All operational
-- spec columns start NULL (unknown) — nothing invented. Kept 'pending'
-- (default) so it's never citable by the AI chatbot as a marketing fact
-- until a human explicitly verifies it (Mandamiento 4).
INSERT INTO `ll_fleet_catalog`
  (`vessel_name`, `role_label_en`, `role_label_es`, `status_pill`, `verification_status`, `display_order`)
VALUES
  ('NOMADA', 'Operational', 'Operativo', 'pill-orange', 'pending', 4)
ON DUPLICATE KEY UPDATE `vessel_name` = `vessel_name`;
