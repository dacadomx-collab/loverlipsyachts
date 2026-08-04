-- =============================================================================
-- LOVER LIPS YACHTS — sql/007_add_role_to_lly_users.sql
-- Adds a `role` column so pg_ai_hub.php Section C (AURA/WhatsApp/OpenAI
-- fallback connection settings — technical, not business-facing) can be
-- restricted to a 'super_admin' operator and hidden from the 'owner'
-- account (Lester). Every existing row defaults to 'owner' — nobody's
-- access silently escalates from running this migration; a human must
-- explicitly UPDATE a specific account to 'super_admin' afterward.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-006).
-- =============================================================================

ALTER TABLE `lly_users`
  ADD COLUMN IF NOT EXISTS `role` ENUM('owner', 'super_admin') NOT NULL DEFAULT 'owner'
    COMMENT 'owner = business-facing (Lester) — Section C hidden. super_admin = technical operator — full pg_ai_hub.php access.'
    AFTER `email`;
