<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/ProxyBridge.php
 * Local bridge of PG-AI Pink Glove AI's Cognitive Omnichannel Operator (see
 * modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md, section 6.2, for the
 * agnostic blueprint this class implements for this project).
 *
 * This class does not reason or decide anything — it only reads local
 * knowledge, signs the outbound payload, and forwards it to the central
 * inference Gateway. All AI provider keys and system prompts live on the
 * Gateway side only; this file never sees them.
 *
 * Configuration comes from core/.env (AI_TENANT_ID, AI_SHARED_SECRET,
 * AI_GATEWAY_URL) — never hardcoded, mirroring the pattern already used
 * by Conexion::loadEnv() and core/seed_owner.php's env reader.
 */
require_once __DIR__ . '/FleetCatalogRepository.php';
require_once __DIR__ . '/../api/conexion.php';
require_once __DIR__ . '/EnvSettingsStore.php';
require_once __DIR__ . '/AuraSatelliteClient.php';
require_once __DIR__ . '/OpenAiFallbackClient.php';

final class ProxyBridge
{
    private const CONNECT_TIMEOUT = 3;
    private const READ_TIMEOUT    = 8;
    private const APCU_TTL        = 300;


    public function __construct(
        private readonly string $gatewayUrl,
        private readonly string $tenantId,
        private readonly string $sharedSecret,
        private readonly ?string $knowledgeMdPath = null,
    ) {
    }

    /** Build an instance from core/.env — the normal way to obtain one. */
    public static function fromEnv(): self
    {
        $env = self::loadEnv(__DIR__ . '/.env');

        // Official system prompt (persona, verified fleet facts, commercial
        // locks, quote-link/escalation sentinel contracts — see
        // core/PgAiActionProcessor.php for how those sentinels get resolved
        // after AURA replies). Supersedes the broader company-wide
        // consolidated doc as the AI-facing content: that doc covers
        // marketing/governance material the chatbot has no reason to see.
        $knowledgePath = __DIR__ . '/prompts/pg_ai_lester_master.md';

        return new self(
            gatewayUrl: $env['AI_GATEWAY_URL']   ?? '',
            tenantId: $env['AI_TENANT_ID']       ?? '',
            sharedSecret: $env['AI_SHARED_SECRET'] ?? '',
            knowledgeMdPath: is_readable($knowledgePath) ? $knowledgePath : null,
        );
    }

    /** Tenant slug this bridge is scoped to — callers need it to persist OCMC rows under the same tenant. */
    public function getTenantId(): string
    {
        return $this->tenantId;
    }

    /**
     * Entry point: receives a partial OCMC message (channel, contact,
     * session_id, message) and returns the central engine's response.
     * Never throws — network/config failures degrade to a controlled
     * status the caller can render as a "please wait" reply, never a 500.
     *
     * Dispatch order (updated 2026-08-15 — direct high-speed OpenAI route,
     * explicit Architect directive):
     *   1. OpenAI direct (core/OpenAiFallbackClient.php, FALLBACK_AI_PROVIDER_KEY/
     *      _MODEL) — tried FIRST when a real key is configured. Sends the full
     *      local system_prompt (core/prompts/pg_ai_lester_master.md, fleet-
     *      substituted) + the guest's message every call — no server-side
     *      persistent-context dependency, so this project keeps full local
     *      sovereignty over persona/language/catalog on this route, and avoids
     *      AURA's WAN latency (10-30s+ observed) entirely. Falls through
     *      (returns null, never throws) when unconfigured or the call fails.
     *   2. AURA LAN → WAN (dispatchViaAura(), delegates the LAN→WAN→WAN-by-IP
     *      failover to AuraSatelliteClient::dispatch()) — the default route
     *      whenever OpenAI isn't configured, and the fallback if OpenAI is
     *      configured but fails. Context lives server-side (Protocolo de
     *      Contexto Persistente M2M) — see core/AuraSatelliteClient.php
     *      ::syncTenantContext() docblock for how that context gets onboarded.
     *   3. Legacy HMAC-signed gateway (AI_GATEWAY_URL/AI_TENANT_ID/
     *      AI_SHARED_SECRET) — dormant, kept only because AI_TENANT_ID is
     *      still used by getTenantId() for OCMC persistence; the gateway
     *      itself was never provisioned with a real host. Candidate for
     *      removal (Mandamiento 8) once someone explicitly confirms it
     *      will never be provisioned.
     *   4. Controlled "still connecting" degraded reply.
     *
     * (2026-08-03 → 2026-08-15 history: OpenAI was originally wired as
     * step 3, reached only if both AURA routes failed — see
     * docs/02_SYSTEM_CODEX_REGISTRY.md for that decision. Promoted to
     * step 1 now that "soberanía total" over the prompt is the explicit
     * goal; this reorder has zero live behavioral effect while
     * FALLBACK_AI_PROVIDER_KEY remains unset — OpenAiFallbackClient::
     * isConfigured() gates it, so an unconfigured key falls straight
     * through to AURA exactly as before.)
     */
    public function forward(array $ocmcMessage): array
    {
        $knowledge = '';
        try {
            $knowledge = $this->readKnowledgeBase();
        } catch (\Throwable $e) {
            error_log('[PG-AI · ProxyBridge] Knowledge base read failed — ' . $e::class . ': ' . $e->getMessage());
        }

        $openAiReply = $this->dispatchViaOpenAiFallback($knowledge, $ocmcMessage);
        if ($openAiReply !== null) {
            return $openAiReply;
        }

        $auraReply = $this->dispatchViaAura($ocmcMessage);
        if ($auraReply !== null) {
            return $auraReply;
        }

        if ($this->tenantId === '' || $this->sharedSecret === '' || $this->gatewayUrl === '') {
            error_log('[PG-AI · ProxyBridge] OpenAI direct + AURA both unavailable/failed and no legacy AI_GATEWAY_URL configured — degrading.');
            return $this->degraded();
        }

        try {
            $body = json_encode([
                'ocmc_version' => '1.0',
                'tenant_slug'  => $this->tenantId,
                'knowledge'    => $knowledge,
            ] + $ocmcMessage, JSON_THROW_ON_ERROR);

            return $this->dispatch($body);
        } catch (\Throwable $e) {
            // Never log $body or the shared secret — only the failure shape.
            error_log('[PG-AI · ProxyBridge] Legacy gateway degraded — ' . $e::class . ': ' . $e->getMessage());
            return $this->degraded();
        }
    }

    /**
     * Direct high-speed route (core/OpenAiFallbackClient.php) — tried
     * FIRST by forward() as of 2026-08-15 when a real key is configured
     * (still named "Fallback" in the class/file name — historical, from
     * when this was step 3; renaming is a separate decision, not made
     * here per Mandamiento 8/10 discipline on unforced renames). Returns
     * null (never throws) when not configured or the call fails, so
     * forward() falls through to AURA exactly as if this route didn't
     * exist. Sends the full knowledge (system prompt, already
     * fleet-substituted) as the system message on every call — unlike
     * AURA's ultra-light persistent-context payload, OpenAI has no
     * server-side context mechanism here, so this project's prompt stays
     * fully sovereign/local on this route by construction.
     */
    private function dispatchViaOpenAiFallback(string $knowledge, array $ocmcMessage): ?array
    {
        $client = OpenAiFallbackClient::fromEnv(__DIR__ . '/.env');
        if (!$client->isConfigured()) {
            return null;
        }

        $guestMessage = (string) ($ocmcMessage['message']['text'] ?? '');
        $result       = $client->dispatch($knowledge, $guestMessage);

        if (!$result['success'] || $result['response'] === null) {
            error_log('[PG-AI · ProxyBridge] OpenAI fallback did not produce a usable reply — ' . ($result['errorMessage'] ?? 'unknown error'));
            return null;
        }

        return ['status' => 'success', 'reply' => (string) $result['response']];
    }

    /**
     * Local Language Lock — deliberately NOT the full ~10KB system prompt.
     *
     * 2026-08-15 finding: AURA replied in Spanish to unambiguous English
     * messages ("hi IM INTERESTING", later reproduced with a clean "Information
     * about swimming with whale sharks" on a fresh session) even after the
     * language rule in core/prompts/pg_ai_lester_master.md was strengthened —
     * because that file is only read locally (readKnowledgeBase() below) and,
     * per the Protocolo de Contexto Persistente M2M, is never resent on the
     * AURA path (see dispatchViaAura()'s own docblock). AURA v3.0's "Fast-Path
     * Router" appears to handle short/simple messages differently from
     * complex ones (complex queries DID correctly reflect the onboarded
     * persona/fleet context in earlier live tests) — consistent with a fast
     * lane that gives less weight to onboarded context on quick turns.
     *
     * The obvious fix — prepend the full master prompt to every AURA
     * dispatch — is exactly what caused the WAN 502s documented in
     * dispatchViaAura()'s docblock on 2026-08-03. Re-introducing that payload
     * size on every message is not an acceptable trade for fixing a language
     * bug. Instead, this constant is a short, fixed-cost directive (tens of
     * bytes, not ~10KB) prepended to the guest's message on every AURA
     * dispatch — small enough to be unlikely to trip the same size-related
     * instability, and small enough that a fast-path/short-message lane is
     * more likely to actually honor it than a large buried instruction.
     *
     * Do not "fix" a future language complaint by expanding this into the
     * full prompt — see the WAN 502 history above first.
     */
    private const LANGUAGE_LOCK_DIRECTIVE = "[LANGUAGE LOCK: Reply strictly in the same language as the user message below — do not translate or switch languages, regardless of earlier turns in this conversation.]\n\n";

    /**
     * Dispatches through the AURA M2M satellite (core/AuraSatelliteClient.php,
     * ACADEP_AURA_* keys in core/.env).
     *
     * Protocolo de Contexto Persistente M2M (Especificación Técnica Oficial
     * v1.4, 2026-08-03): the ~10KB system prompt (persona, commercial locks,
     * fleet facts) is no longer prepended per message — that was the exact
     * cause of the WAN 502s observed the same day (the AXON origin timing
     * out on oversized prompts). AURA now holds that context server-side,
     * onboarded once via AuraSatelliteClient::syncTenantContext() (manual,
     * not yet wired to a trigger — see that method's docblock and
     * modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md section 3.4). Each chat
     * dispatch sends only the guest's raw message plus the short
     * LANGUAGE_LOCK_DIRECTIVE above (2026-08-15) — still ~200-400 bytes,
     * nowhere near the ~10KB that caused WAN 502s. Returns null (never
     * throws) when AURA isn't configured or its dispatch fails, so
     * forward() can fall through to the legacy gateway instead of failing
     * the whole request.
     */
    private function dispatchViaAura(array $ocmcMessage): ?array
    {
        $client = AuraSatelliteClient::fromEnv(__DIR__ . '/.env');

        // ACADEP_AURA_AGENT_ID in core/.env — the real UUID registered in
        // AURA's axon_core_db (confirmed 2026-08-03). The earlier
        // placeholder 'lover_lips_agent' was a dummy id: AURA accepted it
        // (HTTP 200) but had no persona/knowledge registered under it, so
        // every reply was generic. Never hardcode this id in code again —
        // it's tenant config, not a constant.
        $agentId = $client->getDefaultAgentId();
        if ($agentId === '') {
            error_log('[PG-AI · ProxyBridge] ACADEP_AURA_AGENT_ID not configured in core/.env — cannot dispatch to AURA.');
            return null;
        }

        $guestMessage = (string) ($ocmcMessage['message']['text'] ?? '');
        $sessionId    = (string) ($ocmcMessage['session_id'] ?? '');

        // Only the outbound dispatch is decorated — $ocmcMessage itself
        // (and therefore what gets persisted to omnichannel_messages and
        // shown in any leads view) keeps the guest's original, undecorated
        // text. See LANGUAGE_LOCK_DIRECTIVE above for why this exists.
        $result = $client->dispatch($agentId, $sessionId, self::LANGUAGE_LOCK_DIRECTIVE . $guestMessage);

        if (!$result['success'] || $result['response'] === null || trim((string) $result['response']) === '') {
            // See core/AuraSatelliteClient.php's own '[PG-AI · AuraSatelliteClient] RAW' log
            // line for the exact response body/curl errno behind this summary.
            error_log('[PG-AI · ProxyBridge] AURA dispatch did not produce a usable reply (channel=' . $result['channelUsed'] . ', http=' . $result['httpCode'] . ', error=' . ($result['errorMessage'] ?? 'none') . ') — falling back.');
            return null;
        }

        return [
            'status' => 'success',
            'reply'  => (string) $result['response'],
        ];
    }

    private function degraded(): array
    {
        return [
            'status'      => 'degraded',
            'message_key' => 'degraded_fallback',
        ];
    }

    /**
     * Local knowledge lookup with APCu cache keyed by mtime, falling back
     * to a direct LOCK_EX read. Returns '' when no knowledge file is
     * configured yet — the widget still works, just without RAG context.
     * The cache holds the raw file (pre-substitution) so a fleet catalog
     * edit is reflected immediately without waiting on the file's mtime.
     */
    private function readKnowledgeBase(): string
    {
        if ($this->knowledgeMdPath === null || !is_readable($this->knowledgeMdPath)) {
            return '';
        }

        $mtime    = (string) filemtime($this->knowledgeMdPath);
        $cacheKey = 'proxy_bridge_md_' . $this->tenantId . '_' . md5($this->knowledgeMdPath) . '_' . $mtime;

        $content = null;
        if (function_exists('apcu_fetch')) {
            $hit = apcu_fetch($cacheKey, $success);
            if ($success) {
                $content = $hit;
            }
        }

        if ($content === null) {
            $fh = fopen($this->knowledgeMdPath, 'rb');
            if ($fh === false) {
                throw new \RuntimeException('Knowledge file could not be opened.');
            }

            try {
                if (!flock($fh, LOCK_EX)) {
                    throw new \RuntimeException('Knowledge file could not be locked.');
                }
                $content = stream_get_contents($fh);
                flock($fh, LOCK_UN);
            } finally {
                fclose($fh);
            }

            if ($content === false) {
                throw new \RuntimeException('Knowledge file read failed.');
            }

            if (function_exists('apcu_store')) {
                apcu_store($cacheKey, $content, self::APCU_TTL);
            }
        }

        return $this->substituteFleetCatalog($content);
    }

    /**
     * Resolves {{FLEET_CATALOG_TABLE}} from ll_fleet_catalog (single source
     * of truth shared with propuestas.php — see
     * core/FleetCatalogRepository.php). Best-effort: a DB outage must never
     * block a chat reply, so on failure the placeholder is left in place
     * for the honest fallback text FleetCatalogRepository would have used —
     * degrades to "catalog temporarily unavailable", never a stale guess.
     */
    private function substituteFleetCatalog(string $content): string
    {
        if (!str_contains($content, '{{FLEET_CATALOG_TABLE}}')) {
            return $content;
        }

        try {
            $table = FleetCatalogRepository::renderPromptMarkdownTable(Conexion::getConnection());
        } catch (\Throwable $e) {
            error_log('[PG-AI · ProxyBridge] Fleet catalog substitution skipped — ' . $e->getMessage());
            $table = '_(Catálogo de flota temporalmente no disponible — no cites capacidad ni tarifas de ninguna embarcación hasta que este dato regrese.)_';
        }

        return str_replace('{{FLEET_CATALOG_TABLE}}', $table, $content);
    }

    /** HMAC-SHA256 signature (3 factors + nonce) and native cURL dispatch. */
    private function dispatch(string $body): array
    {
        $timestamp = (string) time();
        $nonce     = bin2hex(random_bytes(16));
        $signature = hash_hmac('sha256', $timestamp . $nonce . $body, $this->sharedSecret);

        $ch = curl_init($this->gatewayUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::READ_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-Tenant-Id: ' . $this->tenantId,
                'X-Signature: ' . $signature,
                'X-Timestamp: ' . $timestamp,
                'X-Nonce: ' . $nonce,
            ],
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $error    = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            throw new \RuntimeException('Gateway network failure (curl errno ' . $errno . '): ' . $error);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException('Gateway responded with HTTP ' . $httpCode);
        }

        $decoded = json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
        return is_array($decoded) ? $decoded : [];
    }

    /** Minimal .env reader — same convention as Conexion::loadEnv() (private, can't reuse directly). */
    private static function loadEnv(string $path): array
    {
        $vars = [];
        if (!is_readable($path)) {
            return $vars;
        }
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $vars[trim($key)] = trim($val, " \t\"'");
        }
        return $vars;
    }
}
