<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/PgAiActionProcessor.php
 * PG-AI Pink Glove AI — resolves the machine-readable sentinels the
 * system prompt (core/prompts/pg_ai_lester_master.md, sections 4 & 5)
 * instructs the AI to emit once its commercial locks are satisfied:
 *
 *   [[PGAI_QUOTE_LINK route="balandra|espiritu_santo" title="..."]]
 *     → replaced with a real, working Self-Destruct Link URL
 *       (core/EphemeralLinkManager.php + core/pgai_templates.php).
 *
 *   [[PGAI_ESCALATE]]
 *     → stripped from the visible reply; flags the contact as VIP
 *       (core/OmnichannelRepository.php::markVip()) so it surfaces in
 *       pg_ai_hub.php's Live Leads table (White-Glove Escalation).
 *
 * Runs server-side only, after ProxyBridge::forward() returns and
 * before the reply is persisted/sent to the visitor — the AI's raw
 * output never reaches the guest verbatim if it contains a sentinel.
 * Best-effort: any failure here degrades to stripping the sentinel and
 * logging, never a 500 back to the channel (same circuit-breaker
 * convention as the rest of PG-AI).
 */
final class PgAiActionProcessor
{
    /** lly_users.id — the single Owner account (core/seed_owner.php). System-generated links are attributed here. */
    private const SYSTEM_CREATED_BY = 1;

    private const QUOTE_LINK_PATTERN = '/\[\[PGAI_QUOTE_LINK\s+route="([a-z_]+)"\s+title="([^"]*)"\]\]/';
    private const ESCALATE_PATTERN   = '/\[\[PGAI_ESCALATE\]\]/';

    /**
     * Processes one AI reply: resolves/strips sentinels, applies their
     * side effects (link creation, VIP flag), and returns the
     * guest-facing text. $channelType/$externalId identify the contact
     * for the escalation flag — same identity OmnichannelRepository
     * already upserts per message.
     */
    public static function process(PDO $pdo, string $replyText, string $channelType, string $externalId): string
    {
        if (preg_match(self::ESCALATE_PATTERN, $replyText)) {
            $replyText = preg_replace(self::ESCALATE_PATTERN, '', $replyText) ?? $replyText;

            try {
                OmnichannelRepository::markVip($pdo, $channelType, $externalId);
                error_log('[PG-AI · ActionProcessor] White-Glove Escalation triggered — contact flagged VIP (' . $channelType . '/' . $externalId . '). Notify Lester.');
            } catch (\Throwable $e) {
                error_log('[PG-AI · ActionProcessor] Escalation flag failed — ' . $e->getMessage());
            }
        }

        if (preg_match(self::QUOTE_LINK_PATTERN, $replyText, $m)) {
            $replyText = self::resolveQuoteLink($pdo, $replyText, $m);
        }

        return trim($replyText);
    }

    private static function resolveQuoteLink(PDO $pdo, string $replyText, array $match): string
    {
        [$sentinel, $route, $rawTitle] = $match;
        $templates = lly_pgai_quote_templates();

        if (!isset($templates[$route])) {
            error_log('[PG-AI · ActionProcessor] Unknown quote route "' . $route . '" in AI reply — sentinel dropped.');
            return str_replace($sentinel, '', $replyText);
        }

        $title = trim($rawTitle) !== '' ? trim($rawTitle) : $templates[$route]['title_internal'];

        try {
            $link = EphemeralLinkManager::create(
                $pdo,
                self::SYSTEM_CREATED_BY,
                $title,
                'quote',
                $templates[$route]['html'],
                null,
                null, // null max_views → EphemeralLinkManager applies the owner's configured default (3)
            );
        } catch (\Throwable $e) {
            error_log('[PG-AI · ActionProcessor] Could not create ephemeral quote link — ' . $e->getMessage());
            return str_replace($sentinel, '', $replyText);
        }

        return str_replace($sentinel, self::publicUrl((string) $link['token']), $replyText);
    }

    /** Same convention as api/ephemeral_links.php::lly_el_public_url() — same host the request arrived on, no hardcoded domain. */
    private static function publicUrl(string $token): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$scheme}://{$host}/api/public/l.php?t=" . rawurlencode($token);
    }
}
