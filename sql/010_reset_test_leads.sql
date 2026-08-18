-- =============================================================================
-- LOVER LIPS YACHTS — sql/010_reset_test_leads.sql
-- Maintenance script: clears every dev/test conversation captured while
-- building PG-AI (chat-lab.php smoke tests, curl-based fire tests, etc.)
-- so the Live Leads Cockpit starts clean before real guest traffic.
--
-- Reviewed before writing this file: as of 2026-08-18 every row in
-- omnichannel_sessions has a session_uuid that is clearly dev/test traffic
-- (smoketest-*, testsession*, livefiretest*, pruebafastpath*, debugfiesta*,
-- regressioncheck*, robertogarza* — the fire-test lead) — none look like a
-- real guest. Re-check `SELECT session_uuid, last_activity_at FROM
-- omnichannel_sessions ORDER BY last_activity_at DESC;` yourself before
-- running this if any time has passed since that review.
--
-- Scope: only the three tables explicitly named in the request —
-- omnichannel_channels (whatsapp/web_widget registry) is infrastructure
-- config, not test data, and is deliberately left untouched.
-- omnichannel_message_attachments cascades automatically when its parent
-- messages are deleted (ON DELETE CASCADE, sql/003).
--
-- Deletion order matters — FK constraints are ON DELETE RESTRICT
-- throughout (sql/003): messages before sessions, sessions before contacts.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-009). NOT run by Claude — review the row
-- counts below first, this is irreversible.
-- =============================================================================

-- Sanity check — run this first and confirm the count looks like what you expect:
-- SELECT
--   (SELECT COUNT(*) FROM omnichannel_messages)  AS messages,
--   (SELECT COUNT(*) FROM omnichannel_sessions)  AS sessions,
--   (SELECT COUNT(*) FROM omnichannel_contacts)  AS contacts;

DELETE FROM omnichannel_messages;
DELETE FROM omnichannel_sessions;
DELETE FROM omnichannel_contacts;

-- Cosmetic only (safe to skip) — resets auto_increment so the next real
-- lead starts at id=1 instead of continuing from the old test data's ids.
ALTER TABLE omnichannel_messages AUTO_INCREMENT = 1;
ALTER TABLE omnichannel_sessions AUTO_INCREMENT = 1;
ALTER TABLE omnichannel_contacts AUTO_INCREMENT = 1;
