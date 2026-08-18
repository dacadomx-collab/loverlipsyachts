-- =============================================================================
-- LOVER LIPS YACHTS — sql/011_create_booking_calendar_schema.sql
-- Agenda / Booking Calendar module (agenda.php, api/bookings.php).
--
-- yacht_bookings holds only formal/confirmed reservations — the "interested"
-- tier of the calendar (🟡, chatbot leads that mentioned a route + date but
-- never became a real booking) is read live from omnichannel_sessions by
-- api/bookings.php, never duplicated into this table. session_id links a
-- booking back to the chat thread it originated from, when it did.
--
-- Run manually once via phpMyAdmin / cPanel on u713871298_lly_db.
-- Not executed automatically — no migration runner exists in this project
-- (same convention as sql/001-010).
-- =============================================================================

CREATE TABLE IF NOT EXISTS `yacht_bookings` (
  `id`                 BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id`         BIGINT UNSIGNED NULL COMMENT 'omnichannel_sessions.id this booking originated from, if any — never required, a booking can be entered by hand',
  `yacht_name`         VARCHAR(190)    NOT NULL,
  `guest_name`         VARCHAR(190)    NULL,
  `guest_phone`        VARCHAR(60)     NULL,
  `guest_email`        VARCHAR(190)    NULL,
  `charter_date`       DATE            NOT NULL,
  `charter_time_slot`  ENUM('morning','sunset','full_day') NOT NULL DEFAULT 'full_day',
  `pax_count`          SMALLINT UNSIGNED NULL,
  `route_destination`  VARCHAR(190)    NULL,
  `status`             ENUM('interested','quote_sent','confirmed','completed','cancelled') NOT NULL DEFAULT 'interested',
  `total_price`        DECIMAL(10,2)   NULL,
  `deposit_paid`       DECIMAL(10,2)   NULL,
  `payment_status`     ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `created_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_charter_date` (`charter_date`),
  INDEX `idx_booking_status` (`status`),
  INDEX `idx_booking_yacht` (`yacht_name`),
  CONSTRAINT `fk_booking_session` FOREIGN KEY (`session_id`)
    REFERENCES `omnichannel_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
