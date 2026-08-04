-- =============================================================================
-- LOVER LIPS YACHTS — sql/005_create_ll_fleet_catalog.sql
-- PG-AI Pink Glove AI — Fleet Catalog. Single source of truth for the vessel
-- facts both propuestas.php (Accordion 1, private) and the AI system prompt
-- (core/prompts/pg_ai_lester_master.md §2, via core/FleetCatalogRepository.php
-- + core/ProxyBridge.php) render — previously duplicated as hand-written HTML
-- and hand-written markdown that could silently drift apart.
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-004).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `ll_fleet_catalog` (
  `id`                  INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
  `vessel_name`         VARCHAR(120)    NOT NULL,
  `vessel_slug`         VARCHAR(160)    NULL COMMENT 'WordPress path, e.g. /maranatha-120/ — NULL for vessels with no live page yet',
  `role_label_en`       VARCHAR(60)     NOT NULL COMMENT 'Short tag: Flagship / Signature / Available / Pending',
  `role_label_es`       VARCHAR(60)     NOT NULL,
  `max_pax`             SMALLINT UNSIGNED NULL COMMENT 'NULL = capacity not yet verified — never render as 0 or a guess',
  `rate_note_en`        VARCHAR(120)    NOT NULL DEFAULT '$TBC — Pending Review',
  `rate_note_es`        VARCHAR(120)    NOT NULL DEFAULT '$POR DEFINIR — En revisión',
  `status_pill`         VARCHAR(20)     NOT NULL DEFAULT 'pill-green' COMMENT 'Reuses existing CSS pill vocabulary: pill-pink/pill-gold/pill-green — no new pill classes',
  `verification_status` ENUM('verified','pending') NOT NULL DEFAULT 'pending' COMMENT 'verified = safe for the AI to cite as fact (Mandamiento 4 — Anti-Alucinación); pending = capture-lead-only, never cite specs',
  `display_order`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_fleet_vessel_name` (`vessel_name`),
  INDEX `idx_fleet_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed: the 3 vessels already verified today (propuestas.php Accordion 1 /
-- core/prompts/pg_ai_lester_master.md §2, both pre-existing, transcribed
-- verbatim — no new facts invented). The remaining 39 vessels are tracked
-- only as a count (42 total fleet size, a fixed business fact) until each
-- one clears the same verification bar.
INSERT INTO `ll_fleet_catalog`
  (`vessel_name`, `vessel_slug`, `role_label_en`, `role_label_es`, `max_pax`, `rate_note_en`, `rate_note_es`, `status_pill`, `verification_status`, `display_order`)
VALUES
  ('CNR Maranatha 120', '/maranatha-120/', 'Flagship', 'Insignia', 50, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-pink', 'verified', 1),
  ('Pink Lips', '/pink-lips/', 'Signature', 'Estrella', 20, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-gold', 'verified', 2),
  ('Most Affordable Luxury', '/most-affordable-luxury-yacht-5/', 'Available', 'Disponible', 13, '$TBC — Pending Review', '$POR DEFINIR — En revisión', 'pill-green', 'verified', 3)
ON DUPLICATE KEY UPDATE `vessel_name` = `vessel_name`;
