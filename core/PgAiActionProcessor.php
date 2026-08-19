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
                self::buildQuotePayloadHtml($templates[$route]),
                null,
                null, // null max_views → EphemeralLinkManager applies the owner's configured default (3)
            );
        } catch (\Throwable $e) {
            error_log('[PG-AI · ActionProcessor] Could not create ephemeral quote link — ' . $e->getMessage());
            return str_replace($sentinel, '', $replyText);
        }

        return str_replace($sentinel, self::publicUrl((string) $link['token']), $replyText);
    }

    /**
     * Renders the structured template (core/pgai_templates.php, restructured
     * 2026-08-18) into the "Clear Luxury / Pink Glove" quote card markup —
     * api/public/l.php wraps this with the page chrome (header, language
     * toggle, security badge, WhatsApp CTA) that applies to every ephemeral
     * link, quote or not. `.quote-card-*` classes only style content that
     * opts into them, so a plain owner-typed ephemeral link (api/ephemeral_
     * links.php) still renders as ordinary prose, unaffected.
     */
    private static function buildQuotePayloadHtml(array $template): string
    {
        $h = static fn (string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

        $inclusionsHtml = '';
        foreach ($template['inclusions'] as $item) {
            $inclusionsHtml .= '<li><span class="quote-card-inclusion-icon">' . $h($item['icon']) . '</span>'
                . '<span data-lang="en">' . $h($item['en']) . '</span><span data-lang="es">' . $h($item['es']) . '</span></li>';
        }

        $policiesHtml = '';
        foreach ($template['policies'] as $item) {
            $policiesHtml .= '<li><span data-lang="en">' . $h($item['en']) . '</span><span data-lang="es">' . $h($item['es']) . '</span></li>';
        }

        $rate = number_format((float) $template['rate_mxn'], 0, '.', ',');

        return '<div class="quote-card">'
            . '<p class="quote-card-eyebrow">🛥️ <span data-lang="en">Experience</span><span data-lang="es">Experiencia</span></p>'
            . '<h3>' . '<span data-lang="en">' . $h($template['title']['en']) . '</span><span data-lang="es">' . $h($template['title']['es']) . '</span></h3>'
            . '<p class="quote-card-description"><span data-lang="en">' . $h($template['description']['en']) . '</span><span data-lang="es">' . $h($template['description']['es']) . '</span></p>'
            . '</div>'
            . '<div class="quote-card quote-card--rate">'
            . '<p class="quote-card-eyebrow">💎 <span data-lang="en">Official Rate</span><span data-lang="es">Tarifa Oficial</span></p>'
            . '<p class="quote-card-rate">$' . $rate . ' <span class="quote-card-rate-currency">MXN</span></p>'
            . '</div>'
            . '<div class="quote-card">'
            . '<p class="quote-card-eyebrow">✨ <span data-lang="en">VIP Inclusions</span><span data-lang="es">Inclusiones VIP</span></p>'
            . '<ul class="quote-card-list">' . $inclusionsHtml . '</ul>'
            . '</div>'
            . '<div class="quote-card">'
            . '<p class="quote-card-eyebrow">📋 <span data-lang="en">Booking Policies</span><span data-lang="es">Políticas de Reserva</span></p>'
            . '<ul class="quote-card-list quote-card-list--policies">' . $policiesHtml . '</ul>'
            . '</div>';
    }

    /**
     * (2026-08-18) Was HTTP_HOST-derived only — broke on any deployment
     * where this codebase doesn't live at the web root. Confirmed live:
     * this project runs at /loverlipsyachts/ locally and /cockpit/ on
     * production, so a bare "{scheme}://{host}/api/public/l.php" silently
     * dropped that prefix and produced a 404 in both places. APP_URL
     * (core/.env, per-environment — this file is never git-tracked, so
     * local and production each keep their own correct value permanently,
     * same pattern as DB_HOST_LOCAL in api/conexion.php) fixes this by
     * being explicit instead of derived. Same convention as
     * api/ephemeral_links.php::lly_el_public_url().
     */
    private static function publicUrl(string $token): string
    {
        $appUrl = self::readAppBaseUrl();
        if ($appUrl !== '') {
            return rtrim($appUrl, '/') . '/api/public/l.php?t=' . rawurlencode($token);
        }

        // Fallback only — used if neither APP_URL_LOCAL nor APP_COCKPIT_URL
        // is set in core/.env yet. May produce a wrong path on any
        // deployment mounted under a subfolder.
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return "{$scheme}://{$host}/api/public/l.php?t=" . rawurlencode($token);
    }

    /**
     * (2026-08-18) Reads the APP_URL_LOCAL/APP_COCKPIT_URL pair that was
     * already provisioned in core/.env from the original project scaffolding
     * (dated 2026-05-30) — an earlier version of this fix added a new,
     * redundant APP_URL key instead of noticing these two already existed;
     * corrected to use them instead, same env-detection signals
     * api/conexion.php::isLocalRequest() already uses for DB_HOST_LOCAL.
     */
    private static function readAppBaseUrl(): string
    {
        $env = self::readEnvFile();

        if (self::isLocalRequest()) {
            return $env['APP_URL_LOCAL'] ?? '';
        }
        return rtrim($env['APP_COCKPIT_URL'] ?? '', '/');
    }

    private static function isLocalRequest(): bool
    {
        $httpHost   = (string) ($_SERVER['HTTP_HOST']   ?? '');
        $serverAddr = (string) ($_SERVER['SERVER_ADDR'] ?? '');
        $remoteAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        if (in_array($httpHost, ['localhost', '127.0.0.1'], true) || str_starts_with($httpHost, 'localhost:') || str_starts_with($httpHost, '127.0.0.1:')) {
            return true;
        }
        if (in_array($serverAddr, ['127.0.0.1', '::1'], true)) {
            return true;
        }
        return in_array($remoteAddr, ['127.0.0.1', '::1'], true);
    }

    private static function readEnvFile(): array
    {
        $path = __DIR__ . '/.env';
        $vars = [];
        if (!is_readable($path)) {
            return $vars;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v, " \t\"'");
        }
        return $vars;
    }

    /* ═══════════════════════════════════════════════════════════════════
       DETERMINISTIC LEAD CAPTURE (2026-08-18)
       Regex-based, on purpose — the AI's own output has already been shown
       unreliable for structured extraction (session memory failures
       documented 2026-08-15). Re-scans EVERY guest message in the session
       on every turn (cheap at this volume) rather than only the latest one,
       so a fact given in an earlier turn is never lost just because a later
       turn doesn't repeat it — that re-scan IS the "preserve what's already
       captured" mechanism, no separate merge bookkeeping needed. Requires
       sql/009_add_lead_fields_and_summary.sql to have been run (lead_name/
       lead_phone/lead_email/summary columns) — degrades to a no-op (logs
       and returns) if they don't exist yet, never breaks the chat reply.
       ═══════════════════════════════════════════════════════════════════ */

    private const MONTHS = [
        'enero' => 1, 'ene' => 1, 'january' => 1, 'jan' => 1,
        'febrero' => 2, 'feb' => 2, 'february' => 2,
        'marzo' => 3, 'mar' => 3, 'march' => 3,
        'abril' => 4, 'abr' => 4, 'april' => 4, 'apr' => 4,
        'mayo' => 5, 'may' => 5,
        'junio' => 6, 'jun' => 6, 'june' => 6,
        'julio' => 7, 'jul' => 7, 'july' => 7,
        'agosto' => 8, 'ago' => 8, 'august' => 8, 'aug' => 8,
        'septiembre' => 9, 'setiembre' => 9, 'sep' => 9, 'september' => 9, 'sept' => 9,
        'octubre' => 10, 'oct' => 10, 'october' => 10,
        'noviembre' => 11, 'nov' => 11, 'november' => 11,
        'diciembre' => 12, 'dic' => 12, 'december' => 12, 'dec' => 12,
    ];

    private const ROUTE_KEYWORDS = [
        'espíritu santo'  => 'Isla Espíritu Santo',
        'espiritu santo'  => 'Isla Espíritu Santo',
        'balandra'        => 'Balandra',
        'tiburón ballena' => 'Nado con Tiburón Ballena',
        'tiburon ballena' => 'Nado con Tiburón Ballena',
        'whale shark'     => 'Nado con Tiburón Ballena',
        'pink lips'       => 'Pink Lips',
        'maranatha'       => 'CNR Maranatha 120',
    ];

    /**
     * Called once per turn, after the guest's message is safely persisted
     * (api/public/ai_widget_gateway.php, after OmnichannelRepository::
     * persistInbound()) — needs $sessionId's full message history in the
     * DB already, including the current turn. Best-effort: never throws,
     * a DB/schema issue just skips this turn's capture.
     */
    public static function extractAndSummarizeLead(PDO $pdo, int $sessionId): void
    {
        try {
            $stmt = $pdo->prepare(
                'SELECT direction, content FROM omnichannel_messages WHERE session_id = :sid ORDER BY created_at ASC, id ASC'
            );
            $stmt->execute(['sid' => $sessionId]);
            $rows = $stmt->fetchAll();
        } catch (\Throwable $e) {
            error_log('[PG-AI · ActionProcessor] Lead extraction skipped (history read failed) — ' . $e->getMessage());
            return;
        }

        $guestText = implode("\n", array_map(
            static fn (array $r) => (string) $r['content'],
            array_filter($rows, static fn (array $r) => $r['direction'] === 'inbound'),
        ));

        if (trim($guestText) === '') {
            return;
        }

        $extracted = array_filter([
            'lead_name'  => self::extractName($guestText),
            'lead_phone' => self::extractPhone($guestText),
            'lead_email' => self::extractEmail($guestText),
            'lead_date'  => self::extractDate($guestText),
            'lead_pax'   => self::extractPax($guestText),
            'lead_route' => self::extractRoute($guestText),
        ], static fn ($v) => $v !== null);

        if ($extracted) {
            try {
                $sets   = array_map(static fn ($col) => "{$col} = :{$col}", array_keys($extracted));
                $sql    = 'UPDATE omnichannel_sessions SET ' . implode(', ', $sets) . ' WHERE id = :sid';
                $pdo->prepare($sql)->execute($extracted + ['sid' => $sessionId]);
            } catch (\Throwable $e) {
                error_log('[PG-AI · ActionProcessor] Lead field update skipped (has sql/009 been run?) — ' . $e->getMessage());
                return;
            }

            // Keep omnichannel_contacts.display_name in sync too — the web
            // widget never sends one at contact-creation time (session UUID
            // is the only external_id it has), so without this the Live
            // Leads table falls through to showing the raw UUID for any
            // lead whose name we only learned mid-conversation. Never
            // overwrites an existing display_name (e.g. one a WhatsApp
            // profile already supplied).
            if (isset($extracted['lead_name'])) {
                try {
                    $pdo->prepare(
                        'UPDATE omnichannel_contacts c
                         JOIN omnichannel_sessions s ON s.contact_id = c.id
                         SET c.display_name = :name
                         WHERE s.id = :sid AND c.display_name IS NULL'
                    )->execute(['name' => $extracted['lead_name'], 'sid' => $sessionId]);
                } catch (\Throwable $e) {
                    error_log('[PG-AI · ActionProcessor] Contact display_name sync skipped — ' . $e->getMessage());
                }
            }
        }

        self::regenerateSummary($pdo, $sessionId);
    }

    /**
     * Template-filled, not AI-generated — deliberate, same "deterministic"
     * reasoning as the extraction above: reliable and free, versus another
     * network call to a provider already shown flaky for this project.
     */
    private static function regenerateSummary(PDO $pdo, int $sessionId): void
    {
        try {
            $stmt = $pdo->prepare(
                'SELECT lead_route, lead_pax, lead_date, lead_name, lead_phone, lead_email
                 FROM omnichannel_sessions WHERE id = :sid'
            );
            $stmt->execute(['sid' => $sessionId]);
            $row = $stmt->fetch();
            if (!$row) {
                return;
            }

            $parts = [];

            if ($row['lead_route'] || $row['lead_pax'] || $row['lead_date']) {
                $ask = 'Solicitó chárter';
                if ($row['lead_route']) { $ask .= ' a ' . $row['lead_route']; }
                if ($row['lead_pax'])   { $ask .= ' para ' . $row['lead_pax'] . ' personas'; }
                if ($row['lead_date'])  { $ask .= ' el ' . self::formatSpanishDate($row['lead_date']); }
                $parts[] = $ask . '.';
            }

            $contactBits = array_filter([$row['lead_name'], $row['lead_phone'], $row['lead_email']]);
            $parts[] = $contactBits
                ? 'Datos de contacto confirmados: ' . implode(', ', $contactBits) . '.'
                : 'Datos de contacto aún no capturados.';

            $summary = trim(implode(' ', $parts));
            if ($summary === '') {
                return;
            }

            $pdo->prepare('UPDATE omnichannel_sessions SET summary = :s WHERE id = :sid')
                ->execute(['s' => $summary, 'sid' => $sessionId]);
        } catch (\Throwable $e) {
            error_log('[PG-AI · ActionProcessor] Summary regeneration skipped — ' . $e->getMessage());
        }
    }

    private static function extractEmail(string $text): ?string
    {
        if (preg_match('/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $text, $m)) {
            return mb_strtolower($m[0]);
        }
        return null;
    }

    private static function extractPhone(string $text): ?string
    {
        if (preg_match('/(\+?\d[\d\s\-\(\)]{7,17}\d)/', $text, $m)) {
            $digits = preg_replace('/[^\d+]/', '', $m[1]);
            return (strlen((string) preg_replace('/\D/', '', $digits)) >= 8) ? $digits : null;
        }
        return null;
    }

    /** Guards the riskiest trigger ("soy" is a common verb outside of self-introduction, e.g. "soy muy feliz") — checked against the first captured word regardless of which trigger matched, since a real name is never one of these anyway. */
    private const NAME_STOPWORDS = [
        'muy', 'un', 'una', 'de', 'del', 'el', 'la', 'los', 'las', 'aquí', 'aqui', 'ahora', 'bien', 'feliz', 'nuevo', 'nueva',
        'very', 'just', 'here', 'now', 'fine', 'good', 'sure', 'not', 'from', 'new',
    ];

    private static function extractName(string $text): ?string
    {
        // (2026-08-18) Trigger phrase AND the captured name are now BOTH
        // case-insensitive — a guest typing in all lowercase ("mi nombre
        // es david cabrera") is common and was previously missed entirely
        // because the capture group required an already-capitalized word.
        // The stopword guard below is what now does the false-positive
        // filtering that capitalization used to do.
        //
        // Real bug found live the same day: $text here is every inbound
        // message of the session joined with "\n" (see caller) — \s+
        // between captured words matches a newline too, so a name at the
        // very end of one message ("...mi nombre es david cabrera") kept
        // eating into the FIRST word of the guest's next message ("mi
        // correo es...") as if it were a third name word, producing
        // "David Cabrera\nMi". [^\S\n] (whitespace, but never \n) keeps
        // every word of the captured name on the one message it came from.
        $pattern = '/\b(?:soy|me llamo|mi nombre es|i am|i\'m|my name is)[^\S\n]+'
            . '([a-zà-öø-ÿ]+(?:[^\S\n]+[a-zà-öø-ÿ]+){0,2})/iu';

        if (!preg_match($pattern, $text, $m)) {
            return null;
        }

        $name = trim((string) preg_replace('/[,.;!?].*$/', '', $m[1]));
        if ($name === '') {
            return null;
        }

        $firstWord = mb_strtolower(explode(' ', $name)[0]);
        if (in_array($firstWord, self::NAME_STOPWORDS, true)) {
            return null;
        }

        // mb_convert_case (not ucwords()) — ucwords() only understands the
        // ASCII byte range and mangles accented names ("cabrera" is fine,
        // but it would butcher e.g. "José").
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    private static function extractPax(string $text): ?int
    {
        if (preg_match('/\b(\d{1,3})\s*(?:personas?|pax|people|guests?|huéspedes?)\b/iu', $text, $m)) {
            $n = (int) $m[1];
            return ($n > 0 && $n <= 500) ? $n : null;
        }
        return null;
    }

    private static function extractDate(string $text): ?string
    {
        // Spanish: "20 de Noviembre" / "20 de nov"
        if (preg_match('/\b(\d{1,2})\s+de\s+([a-záéíóúñ]+)\b/iu', $text, $m)) {
            $date = self::buildDate((int) $m[1], $m[2]);
            if ($date !== null) {
                return $date;
            }
        }
        // English: "November 20" / "Nov 20th"
        if (preg_match('/\b([a-z]+)\s+(\d{1,2})(?:st|nd|rd|th)?\b/i', $text, $m)
            && isset(self::MONTHS[mb_strtolower($m[1])])) {
            return self::buildDate((int) $m[2], $m[1]);
        }
        return null;
    }

    private static function buildDate(int $day, string $monthName): ?string
    {
        $month = self::MONTHS[mb_strtolower($monthName)] ?? null;
        if ($month === null || $day < 1 || $day > 31) {
            return null;
        }

        try {
            $candidate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', (int) date('Y'), $month, $day));
        } catch (\Exception) {
            return null;
        }

        // A named date with no year is assumed to mean "the next time this
        // date occurs" — if it already passed this year, roll to next year.
        if ($candidate < new \DateTimeImmutable('today')) {
            $candidate = $candidate->modify('+1 year');
        }

        return $candidate->format('Y-m-d');
    }

    private const SPANISH_MONTHS = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    /** The summary is Spanish-only by design (internal Cockpit copy) — PHP's own format('F') would give the English month name, so this maps it explicitly rather than depending on the server locale. */
    private static function formatSpanishDate(string $ymd): string
    {
        $date = new \DateTimeImmutable($ymd);
        return $date->format('d') . ' de ' . self::SPANISH_MONTHS[(int) $date->format('n')];
    }

    private static function extractRoute(string $text): ?string
    {
        $lower = mb_strtolower($text);
        foreach (self::ROUTE_KEYWORDS as $needle => $label) {
            if (str_contains($lower, $needle)) {
                return $label;
            }
        }
        return null;
    }
}
