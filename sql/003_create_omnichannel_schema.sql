-- =============================================================================
-- LOVER LIPS YACHTS — sql/003_create_omnichannel_schema.sql
-- Fase 2 (Base de Datos Omnicanal) de modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001 and sql/002).
--
-- Tenant scoping: this project runs a single tenant, so tenant_id is always
-- the same AI_TENANT_ID value from core/.env — the column exists so the
-- schema stays byte-identical to the agnostic blueprint, not because this
-- project needs multi-tenancy today.
-- =============================================================================

CREATE TABLE IF NOT EXISTS `omnichannel_channels` (
  `id`                    BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id`             VARCHAR(64)     NOT NULL,
  `channel_type`          ENUM('whatsapp','telegram','web_widget') NOT NULL,
  `channel_label`         VARCHAR(120)    NOT NULL,
  `credentials_encrypted` TEXT            NULL COMMENT 'Cifrado AES-256-GCM, nunca texto plano',
  `status`                ENUM('active','inactive') NOT NULL DEFAULT 'inactive',
  `created_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`            DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_tenant_channel` (`tenant_id`, `channel_type`),
  INDEX `idx_channel_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `omnichannel_contacts` (
  `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id`      VARCHAR(64)     NOT NULL,
  `channel_id`     BIGINT UNSIGNED NOT NULL,
  `external_id`    VARCHAR(190)    NOT NULL COMMENT 'ID nativo del canal de origen (wa_id, session_id, etc.)',
  `display_name`   VARCHAR(190)    NULL,
  `is_vip`         TINYINT(1)      NOT NULL DEFAULT 0 COMMENT 'White-Glove Escalation — marcado manual o heurístico',
  `created_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_channel_external` (`channel_id`, `external_id`),
  INDEX `idx_contact_tenant` (`tenant_id`),
  CONSTRAINT `fk_contact_channel` FOREIGN KEY (`channel_id`)
    REFERENCES `omnichannel_channels` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `omnichannel_sessions` (
  `id`                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id`         VARCHAR(64)     NOT NULL,
  `channel_id`        BIGINT UNSIGNED NOT NULL,
  `contact_id`        BIGINT UNSIGNED NOT NULL,
  `session_uuid`      CHAR(36)        NOT NULL,
  `lead_date`         DATE            NULL COMMENT 'NO_PRICE_WITHOUT_LEAD_DATA — Fecha',
  `lead_pax`          SMALLINT UNSIGNED NULL COMMENT 'NO_PRICE_WITHOUT_LEAD_DATA — PAX',
  `lead_route`        VARCHAR(190)    NULL COMMENT 'NO_PRICE_WITHOUT_LEAD_DATA — Ruta',
  `lead_contact`      VARCHAR(190)    NULL COMMENT 'NO_PRICE_WITHOUT_LEAD_DATA — Contacto',
  `status`            ENUM('open','closed') NOT NULL DEFAULT 'open',
  `started_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_session_uuid` (`session_uuid`),
  INDEX `idx_session_contact` (`contact_id`),
  INDEX `idx_session_status` (`status`, `last_activity_at`),
  CONSTRAINT `fk_session_channel` FOREIGN KEY (`channel_id`)
    REFERENCES `omnichannel_channels` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_session_contact` FOREIGN KEY (`contact_id`)
    REFERENCES `omnichannel_contacts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `omnichannel_messages` (
  `id`                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id`           VARCHAR(64)     NOT NULL,
  `session_id`          BIGINT UNSIGNED NOT NULL,
  `channel_message_id`  VARCHAR(190)    NOT NULL COMMENT 'ID nativo del proveedor, para idempotencia',
  `direction`           ENUM('inbound','outbound') NOT NULL,
  `message_type`        ENUM('text','image','audio','document','interactive') NOT NULL DEFAULT 'text',
  `content`             TEXT            NULL,
  `ocmc_payload`        JSON            NULL COMMENT 'Payload OCMC completo, para auditoria',
  `processing_status`   ENUM('queued','processing','delivered','failed') NOT NULL DEFAULT 'queued',
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_channel_message` (`channel_message_id`),
  INDEX `idx_message_session` (`session_id`, `created_at`),
  INDEX `idx_message_status` (`processing_status`),
  CONSTRAINT `fk_message_session` FOREIGN KEY (`session_id`)
    REFERENCES `omnichannel_sessions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `omnichannel_message_attachments` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `message_id`  BIGINT UNSIGNED NOT NULL,
  `media_type`  VARCHAR(60)     NOT NULL COMMENT 'mime type o categoria del proveedor',
  `media_url`   VARCHAR(500)    NULL,
  `media_ref`   VARCHAR(190)    NULL COMMENT 'ID de media nativo del proveedor si aplica',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_attachment_message` (`message_id`),
  CONSTRAINT `fk_attachment_message` FOREIGN KEY (`message_id`)
    REFERENCES `omnichannel_messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `omnichannel_webhooks` (
  `id`               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tenant_id`        VARCHAR(64)     NOT NULL,
  `channel_type`     ENUM('whatsapp','telegram','web_widget') NOT NULL,
  `raw_payload`      JSON            NOT NULL COMMENT 'Payload crudo del proveedor, previo a normalizacion OCMC',
  `signature_valid`  TINYINT(1)      NOT NULL DEFAULT 0,
  `received_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_webhook_tenant` (`tenant_id`, `received_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
