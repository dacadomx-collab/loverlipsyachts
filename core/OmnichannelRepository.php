<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/OmnichannelRepository.php
 * Part of PG-AI Pink Glove AI (Chatbot IA + Omnichannel Handshake +
 * Ephemeral Quotes). Persists OCMC-normalized events into the
 * omnichannel_* tables (Fase 2 of modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md)
 * so a visitor gets the same conversation history whether they write via
 * the WhatsApp Business channel or the WordPress/site widget — same
 * tenant, same session thread.
 *
 * Schema: sql/003_create_omnichannel_schema.sql.
 * Consumers: api/public/ai_widget_gateway.php, api/public/whatsapp_webhook.php.
 */
final class OmnichannelRepository
{
    /**
     * Upserts channel -> contact -> open session for this OCMC message and
     * records the inbound row (idempotent on channel_message_id). Returns
     * the internal `omnichannel_sessions.id` so the caller can persist the
     * matching outbound reply against the same thread.
     */
    public static function persistInbound(PDO $pdo, string $tenantId, array $ocmc): int
    {
        $channelType = (string) ($ocmc['channel'] ?? 'web_widget');
        $externalId  = (string) ($ocmc['contact']['external_id'] ?? '');
        $displayName = $ocmc['contact']['display_name'] ?? null;
        $sessionUuid = (string) ($ocmc['session_id'] ?? '');

        if ($externalId === '' || $sessionUuid === '') {
            throw new InvalidArgumentException('OCMC message missing contact.external_id or session_id.');
        }

        $channelId = self::upsertChannel($pdo, $tenantId, $channelType);
        $contactId = self::upsertContact($pdo, $tenantId, $channelId, $externalId, $displayName);
        $sessionId = self::upsertOpenSession($pdo, $tenantId, $channelId, $contactId, $sessionUuid);

        self::insertMessage(
            $pdo,
            $tenantId,
            $sessionId,
            (string) ($ocmc['channel_message_id'] ?? bin2hex(random_bytes(12))),
            'inbound',
            (string) ($ocmc['message']['type'] ?? 'text'),
            $ocmc['message']['text'] ?? null,
            $ocmc,
            'delivered',
        );

        return $sessionId;
    }

    public static function persistOutbound(PDO $pdo, string $tenantId, int $sessionId, string $replyText): void
    {
        self::insertMessage(
            $pdo,
            $tenantId,
            $sessionId,
            bin2hex(random_bytes(12)),
            'outbound',
            'text',
            $replyText,
            null,
            'delivered',
        );
    }

    /**
     * Oldest-first messages for one session_uuid — backs both chat-lab.php /
     * the public widget's "reload and keep talking" history view and
     * core/ProxyBridge.php's conversation-context injection. Read-only,
     * no tenant filter needed beyond the UUID itself (session_uuid is a
     * random v4 UUID — unguessable, same trust model as an ephemeral link
     * token — see core/EphemeralLinkManager.php). Returns [] (never
     * throws) if the session doesn't exist yet, so a brand-new visitor's
     * first request degrades to "no history" instead of an error.
     */
    public static function getMessagesBySessionUuid(PDO $pdo, string $sessionUuid, int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        $stmt = $pdo->prepare(
            "SELECT m.direction, m.content, m.created_at
             FROM omnichannel_messages m
             JOIN omnichannel_sessions s ON s.id = m.session_id
             WHERE s.session_uuid = :uuid
             ORDER BY m.created_at DESC, m.id DESC
             LIMIT {$limit}"
        );
        $stmt->execute(['uuid' => $sessionUuid]);
        $rows = $stmt->fetchAll();

        return array_reverse($rows);
    }

    /**
     * Flags a contact as VIP (White-Glove Escalation, see
     * core/prompts/pg_ai_lester_master.md section 4) — surfaced in
     * pg_ai_hub.php's Live Leads table. Heuristic marking only; never
     * unset automatically once true (a human clears it, if ever).
     */
    public static function markVip(PDO $pdo, string $channelType, string $externalId): void
    {
        $stmt = $pdo->prepare(
            "UPDATE omnichannel_contacts c
             JOIN omnichannel_channels ch ON ch.id = c.channel_id
             SET c.is_vip = 1
             WHERE ch.channel_type = :type AND c.external_id = :external"
        );
        $stmt->execute(['type' => $channelType, 'external' => $externalId]);
    }

    private static function upsertChannel(PDO $pdo, string $tenantId, string $channelType): int
    {
        $stmt = $pdo->prepare(
            'SELECT id FROM omnichannel_channels WHERE tenant_id = :tenant AND channel_type = :type LIMIT 1'
        );
        $stmt->execute(['tenant' => $tenantId, 'type' => $channelType]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $insert = $pdo->prepare(
            "INSERT INTO omnichannel_channels (tenant_id, channel_type, channel_label, status)
             VALUES (:tenant, :type, :label, 'active')"
        );
        $insert->execute(['tenant' => $tenantId, 'type' => $channelType, 'label' => ucfirst(str_replace('_', ' ', $channelType))]);

        return (int) $pdo->lastInsertId();
    }

    private static function upsertContact(PDO $pdo, string $tenantId, int $channelId, string $externalId, ?string $displayName): int
    {
        $stmt = $pdo->prepare(
            'SELECT id FROM omnichannel_contacts WHERE channel_id = :channel AND external_id = :external LIMIT 1'
        );
        $stmt->execute(['channel' => $channelId, 'external' => $externalId]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            if ($displayName !== null) {
                $update = $pdo->prepare('UPDATE omnichannel_contacts SET display_name = :name WHERE id = :id');
                $update->execute(['name' => $displayName, 'id' => $id]);
            }
            return (int) $id;
        }

        $insert = $pdo->prepare(
            'INSERT INTO omnichannel_contacts (tenant_id, channel_id, external_id, display_name)
             VALUES (:tenant, :channel, :external, :name)'
        );
        $insert->execute([
            'tenant'   => $tenantId,
            'channel'  => $channelId,
            'external' => $externalId,
            'name'     => $displayName,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /** Reuses the contact's open session if the OCMC session_id matches; otherwise opens a fresh one. */
    private static function upsertOpenSession(PDO $pdo, string $tenantId, int $channelId, int $contactId, string $sessionUuid): int
    {
        $stmt = $pdo->prepare('SELECT id FROM omnichannel_sessions WHERE session_uuid = :uuid LIMIT 1');
        $stmt->execute(['uuid' => $sessionUuid]);
        $id = $stmt->fetchColumn();

        if ($id !== false) {
            $touch = $pdo->prepare('UPDATE omnichannel_sessions SET last_activity_at = NOW() WHERE id = :id');
            $touch->execute(['id' => $id]);
            return (int) $id;
        }

        $insert = $pdo->prepare(
            "INSERT INTO omnichannel_sessions (tenant_id, channel_id, contact_id, session_uuid, status)
             VALUES (:tenant, :channel, :contact, :uuid, 'open')"
        );
        $insert->execute([
            'tenant'  => $tenantId,
            'channel' => $channelId,
            'contact' => $contactId,
            'uuid'    => $sessionUuid,
        ]);

        return (int) $pdo->lastInsertId();
    }

    private static function insertMessage(
        PDO $pdo,
        string $tenantId,
        int $sessionId,
        string $channelMessageId,
        string $direction,
        string $messageType,
        ?string $content,
        ?array $ocmcPayload,
        string $processingStatus,
    ): void {
        // ON DUPLICATE KEY UPDATE id=id is a no-op write that keeps this
        // idempotent against provider retries without throwing on the
        // uq_channel_message unique key.
        $stmt = $pdo->prepare(
            'INSERT INTO omnichannel_messages
                (tenant_id, session_id, channel_message_id, direction, message_type, content, ocmc_payload, processing_status)
             VALUES (:tenant, :session, :channel_message_id, :direction, :type, :content, :payload, :status)
             ON DUPLICATE KEY UPDATE id = id'
        );
        $stmt->execute([
            'tenant'             => $tenantId,
            'session'            => $sessionId,
            'channel_message_id' => $channelMessageId,
            'direction'          => $direction,
            'type'               => $messageType,
            'content'            => $content,
            'payload'            => $ocmcPayload !== null ? json_encode($ocmcPayload, JSON_UNESCAPED_UNICODE) : null,
            'status'             => $processingStatus,
        ]);
    }
}
