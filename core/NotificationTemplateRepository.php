<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/NotificationTemplateRepository.php
 * PG-AI Pink Glove AI — read/write for `ll_notification_templates`
 * (sql/008_create_ll_notification_templates.sql). Edited from
 * pg_ai_config.php (owner + super_admin) — the internal "new lead
 * captured" alert content (Email/WhatsApp), not the guest-facing chatbot
 * quote templates (those are core/pgai_templates.php).
 *
 * Fixed set of rows (template_key + channel) — no create/delete from the
 * UI, only editing existing rows' subject/body. Keeps this a content
 * editor, not a place to invent new, unwired notification types.
 */
final class NotificationTemplateRepository
{
    /** @return list<array<string, mixed>> */
    public static function listAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id, template_key, channel, subject_en, subject_es, body_en, body_es, updated_at
             FROM ll_notification_templates
             ORDER BY template_key ASC, channel ASC'
        );
        return $stmt->fetchAll();
    }

    /** Updates subject/body only — template_key/channel are immutable identifiers, never editable from the client. */
    public static function update(PDO $pdo, int $id, array $data): void
    {
        $fields = [];
        foreach (['subject_en', 'subject_es', 'body_en', 'body_es'] as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }
        if ($fields === []) {
            return;
        }

        $assignments = array_map(static fn (string $c): string => "{$c} = :{$c}", array_keys($fields));

        $stmt = $pdo->prepare(
            'UPDATE ll_notification_templates SET ' . implode(', ', $assignments) . ' WHERE id = :id'
        );
        $stmt->execute($fields + ['id' => $id]);
    }
}
