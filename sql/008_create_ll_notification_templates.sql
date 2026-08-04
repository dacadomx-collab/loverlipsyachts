-- =============================================================================
-- LOVER LIPS YACHTS — sql/008_create_ll_notification_templates.sql
-- PG-AI Pink Glove AI — editable templates for the automated notification
-- sent when a lead is captured (Email / WhatsApp), edited from
-- pg_ai_config.php (owner + super_admin). Not the guest-facing chatbot
-- reply templates (those are core/pgai_templates.php PINK LIPS quotes) —
-- this table is specifically the internal "new lead captured" alert.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-007).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `ll_notification_templates` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `template_key` VARCHAR(60)   NOT NULL COMMENT 'Fixed identifier, e.g. lead_captured — not user-defined, no create/delete from the UI',
  `channel`      ENUM('email', 'whatsapp') NOT NULL,
  `subject_en`   VARCHAR(190)  NULL COMMENT 'Email only — NULL for whatsapp rows',
  `subject_es`   VARCHAR(190)  NULL,
  `body_en`      TEXT          NOT NULL,
  `body_es`      TEXT          NOT NULL,
  `updated_at`   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_template_channel` (`template_key`, `channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ll_notification_templates`
  (`template_key`, `channel`, `subject_en`, `subject_es`, `body_en`, `body_es`)
VALUES
  ('lead_captured', 'email',
   'New lead captured — Lover Lips Yachts', 'Nuevo lead capturado — Lover Lips Yachts',
   'Hi Lester, a new lead just came in through PG-AI. Check pg_ai_hub.php for the full conversation.',
   'Hola Lester, acaba de entrar un nuevo lead a través de PG-AI. Revisa pg_ai_hub.php para ver la conversación completa.'),
  ('lead_captured', 'whatsapp',
   NULL, NULL,
   'New lead captured on Lover Lips Yachts — check the PG-AI Hub for details.',
   'Nuevo lead capturado en Lover Lips Yachts — revisa el PG-AI Hub para más detalles.')
ON DUPLICATE KEY UPDATE `template_key` = `template_key`;
