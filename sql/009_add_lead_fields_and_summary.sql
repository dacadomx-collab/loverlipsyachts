-- =============================================================================
-- LOVER LIPS YACHTS — sql/009_add_lead_fields_and_summary.sql
-- PG-AI Pink Glove AI — deterministic lead capture + executive summary
-- (pg_ai_hub.php Live Leads modal, core/PgAiActionProcessor.php).
--
-- omnichannel_sessions.lead_contact (existing) is a single free-text field
-- and was never actually structured — this splits it into name/phone/email
-- so the Leads table can show real columns instead of one blob. lead_contact
-- itself is left in place (no data loss, other code may still read it) but
-- is superseded by these three for anything new.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-008).
-- =============================================================================

ALTER TABLE `omnichannel_sessions`
  ADD COLUMN `lead_name`  VARCHAR(190) NULL COMMENT 'Deterministically extracted from guest messages — core/PgAiActionProcessor.php' AFTER `lead_contact`,
  ADD COLUMN `lead_phone` VARCHAR(60)  NULL COMMENT 'Deterministically extracted — WhatsApp/phone number' AFTER `lead_name`,
  ADD COLUMN `lead_email` VARCHAR(190) NULL COMMENT 'Deterministically extracted' AFTER `lead_phone`,
  ADD COLUMN `summary`    TEXT         NULL COMMENT 'Template-generated executive summary, regenerated whenever lead data changes or a quote link is created' AFTER `lead_email`;
