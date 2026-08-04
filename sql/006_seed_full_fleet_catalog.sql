-- =============================================================================
-- LOVER LIPS YACHTS — sql/006_seed_full_fleet_catalog.sql
-- Adds `length_ft` (eslora) to ll_fleet_catalog and seeds 4 real vessels
-- confirmed live on loverlipsyachts.com (WordPress) on 2026-08-03:
-- Falcon 86, Lagoon 46, Sea Ray 50, Ferretti 72 (real names/slugs, verified
-- by logging into wp-admin and reading both the public page and the
-- authenticated REST API content — same source, no hidden custom fields).
--
-- IMPORTANT — no capacity/PAX or eslora data exists anywhere in WordPress
-- for these 4 vessels (marketing copy only, no spec sheet). Per Mandamiento
-- 4 (Anti-Alucinación), `max_pax`/`length_ft` are seeded NULL and
-- `verification_status` stays 'pending' — FleetCatalogRepository already
-- excludes 'pending' rows from what the AI cites as fact. These 4 rows are
-- meant to be completed by Lester himself via the new Fleet Catalog editor
-- in pg_ai_hub.php (api/fleet_catalog.php) — not guessed here.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-005).
-- =============================================================================

ALTER TABLE `ll_fleet_catalog`
  ADD COLUMN IF NOT EXISTS `length_ft` SMALLINT UNSIGNED NULL
    COMMENT 'Eslora en pies (LOA) — NULL hasta confirmación real, nunca inferido del nombre del modelo'
    AFTER `max_pax`;

INSERT INTO `ll_fleet_catalog`
  (`vessel_name`, `vessel_slug`, `role_label_en`, `role_label_es`, `max_pax`, `length_ft`, `rate_note_en`, `rate_note_es`, `status_pill`, `verification_status`, `display_order`)
VALUES
  ('Falcon 86', '/falcon-86/', 'Pending', 'Pendiente', NULL, NULL, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-orange', 'pending', 4),
  ('Lagoon 46', '/lagoon-46/', 'Pending', 'Pendiente', NULL, NULL, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-orange', 'pending', 5),
  ('Sea Ray 50', '/sea-ray-50/', 'Pending', 'Pendiente', NULL, NULL, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-orange', 'pending', 6),
  ('Ferretti 72', '/ferretti-72/', 'Pending', 'Pendiente', NULL, NULL, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-orange', 'pending', 7)
ON DUPLICATE KEY UPDATE
  `vessel_slug` = VALUES(`vessel_slug`),
  `display_order` = VALUES(`display_order`);
