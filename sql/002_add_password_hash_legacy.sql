-- =============================================================================
-- LOVER LIPS YACHTS — sql/002_add_password_hash_legacy.sql
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db (same as
-- 001 — loverlipsyachts.com production, Hostinger). Not executed
-- automatically — no migration runner exists in this project.
--
-- Adds an optional secondary bcrypt hash so the Owner account can log in
-- with either the current password or a previous one during a password
-- rotation, without needing a second user row (email stays UNIQUE).
-- =============================================================================

ALTER TABLE `lly_users`
  ADD COLUMN `password_hash_legacy` VARCHAR(255) NULL AFTER `password_hash`;
