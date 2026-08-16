<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/AuraSatelliteClient.php
 * M2M satellite client for the AURA central inference engine. See
 * modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md for the agnostic blueprint
 * this class implements — it's a straight fill-in of that molde's
 * reference artifact, no project-specific logic added.
 *
 * Promoted from diagnostic-only to the real chat dispatch path on
 * 2026-08-03 (see docs/02_SYSTEM_CODEX_REGISTRY.md) — core/ProxyBridge.php
 * ::dispatchViaAura() is the live production caller for the widget and
 * WhatsApp webhook. api/aura_diagnostic.php still uses this same client
 * for sandboxed connectivity checks against the same real endpoints.
 */
final class AuraSatelliteClient
{
    private const LAN_CONNECT_TIMEOUT = 3;
    private const READ_TIMEOUT        = 8;
    /** WAN inference measured ~21s in production for a trivial prompt (2026-08-03 live test against axon.acadep.com) — the old READ_TIMEOUT+4 (12s) budget cut off a request the server would have answered successfully. */
    private const WAN_READ_TIMEOUT    = 45;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $gatewayEndpoint,
        private readonly string $apiKey,
        private readonly string $tenant,
        private readonly ?string $fallbackUrl = null,
        private readonly ?string $fallbackIp = null,
        private readonly string $defaultAgentId = '',
    ) {
    }

    /** Builds an instance from core/.env's ACADEP_AURA_* keys via EnvSettingsStore. */
    public static function fromEnv(string $envPath): self
    {
        $settings = EnvSettingsStore::getRaw($envPath);

        return new self(
            baseUrl: $settings['ACADEP_AURA_BASE_URL'] ?? '',
            gatewayEndpoint: $settings['ACADEP_AURA_GATEWAY_ENDPOINT'] ?? '',
            apiKey: $settings['ACADEP_AURA_KEY'] ?? '',
            tenant: $settings['ACADEP_AURA_TENANT'] ?? '',
            fallbackUrl: $settings['ACADEP_AURA_FALLBACK_URL'] ?? null,
            fallbackIp: $settings['ACADEP_AURA_FALLBACK_IP'] ?? null,
            defaultAgentId: $settings['ACADEP_AURA_AGENT_ID'] ?? '',
        );
    }

    /**
     * The real agent UUID registered in AURA's axon_core_db for this
     * tenant (ACADEP_AURA_AGENT_ID in core/.env) — never hardcode this
     * value in calling code. Confirmed 2026-08-03: the previous
     * placeholder 'lover_lips_agent' was a dummy identifier not
     * registered in AURA's database, which is why dispatch() calls
     * succeeded (HTTP 200) but returned generic, personality-less
     * replies — AURA had no persona/knowledge to key off of.
     */
    public function getDefaultAgentId(): string
    {
        return $this->defaultAgentId;
    }

    /**
     * Dispatches one prompt. Tries the LAN host first; falls back to the
     * WAN host only on a LAN **connection-level** failure — cURL errno 6
     * (CURLE_COULDNT_RESOLVE_HOST) or 7 (CURLE_COULDNT_CONNECT). An
     * HTTP-level error from LAN (401/403/500/504, timeout, malformed
     * body) is returned as-is, with no WAN retry.
     *
     * Updated 2026-08-03 (Especificación Técnica Oficial MOD_CONEXION_
     * SATELLITE_AURA_M2M v1.3): this client was promoted from
     * diagnostic-only (api/aura_diagnostic.php) to the real chat dispatch
     * path (core/ProxyBridge.php::dispatchViaAura(), same session). Real
     * traffic changes the risk calculus from the original "any failure"
     * policy this class used to have: retrying a request that got a real
     * HTTP response (e.g. a timeout after AURA already started
     * processing/billing it) risks double-processing a billed dispatch.
     * Only a connection that never reached AURA at all is safe to retry
     * elsewhere — which is also the blueprint's own original conservative
     * default (modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md, section 5.3).
     * Net effect for api/aura_diagnostic.php: a LAN 401 (wrong port/key)
     * now surfaces directly instead of being masked by a WAN attempt —
     * more informative for diagnosing that exact failure mode, not less.
     *
     * A third tier handles a WAN failure specifically caused by DNS
     * resolution (curl errno 6 — see the blueprint's section 2.2): if the
     * domain won't resolve but a known public IP for that same server is
     * configured, retry once via CURLOPT_RESOLVE (never by rewriting the
     * URL to the raw IP — that would break TLS SNI/certificate
     * validation against a hostname-issued cert). Never throws.
     */
    public function dispatch(string $agentId, string $sessionId, string $prompt): array
    {
        // Protocolo de Contexto Persistente M2M (Especificación Técnica
        // Oficial v1.4, 2026-08-03): recurring chat dispatches are
        // ultra-lightweight — no system prompt/knowledge travels here
        // anymore. AURA holds the tenant's onboarded context server-side
        // (see syncTenantContext() below) and looks it up by agent_id.
        return $this->dispatchPayload([
            'agent_id'     => $agentId,
            'user_session' => $sessionId,
            'prompt'       => $prompt,
        ]);
    }

    /**
     * Administrative, low-frequency call: pushes the full system prompt
     * (core/prompts/pg_ai_lester_master.md) to AURA once, so recurring
     * dispatch() calls can stay a ~200-byte payload instead of resending
     * the ~10KB prompt on every chat message (the cause of the WAN 502s
     * observed 2026-08-03 with the old per-message payload). Not wired
     * into any automatic trigger yet — call manually (e.g. from a CLI
     * script or a future admin action) whenever
     * core/prompts/pg_ai_lester_master.md changes.
     *
     * (2026-08-15) Endpoint confirmed live: AURA published a dedicated
     * onboarding route, POST {gatewayEndpoint}/sync-context — a sibling
     * path of the chat dispatch endpoint, not the same URL with an
     * `action` discriminator. The original provisional contract (this
     * same URL as dispatch() + `action: 'sync_context'`) was tried and
     * rejected with HTTP 400 on 2026-08-03 — see
     * docs/02_SYSTEM_CODEX_REGISTRY.md. This version targets the real,
     * dedicated route and drops the now-unnecessary `action` field.
     */
    public function syncTenantContext(string $agentId, string $systemPrompt): array
    {
        return $this->dispatchPayload([
            'agent_id'      => $agentId,
            'tenant'        => $this->tenant,
            'system_prompt' => $systemPrompt,
        ], endpointSuffix: '/sync-context');
    }

    /**
     * Shared LAN → WAN → WAN-by-IP cascade (section 2 of the blueprint) —
     * both dispatch() and syncTenantContext() funnel through here so the
     * failover policy lives in exactly one place. $endpointSuffix lets a
     * caller target a sibling route (e.g. syncTenantContext()'s
     * "/sync-context") without duplicating the cascade.
     */
    private function dispatchPayload(array $payload, string $endpointSuffix = ''): array
    {
        if ($this->apiKey === '' || $this->baseUrl === '') {
            return $this->result(success: false, channel: 'none', errorMessage: 'AURA client not configured (missing base URL or API key).');
        }

        $body     = json_encode($payload, JSON_THROW_ON_ERROR);
        $endpoint = $this->gatewayEndpoint . $endpointSuffix;

        $lan = $this->attempt($this->baseUrl . $endpoint, $body, 'lan');

        if (!$lan['connectFailed'] || $this->fallbackUrl === null || $this->fallbackUrl === '') {
            return $lan['raw'];
        }

        $wanUrl = $this->fallbackUrl . $endpoint;
        $wan    = $this->attempt($wanUrl, $body, 'wan', readTimeout: self::WAN_READ_TIMEOUT);

        if ($wan['raw']['success'] || !$wan['dnsFailed'] || $this->fallbackIp === null || $this->fallbackIp === '') {
            $wan['raw']['lanErrorMessage'] = $lan['raw']['errorMessage'];
            return $wan['raw'];
        }

        // Third tier: WAN domain didn't resolve, but a direct IP is known.
        $wanIp = $this->attempt($wanUrl, $body, 'wan_ip', readTimeout: self::WAN_READ_TIMEOUT, resolveIp: $this->fallbackIp);
        $wanIp['raw']['lanErrorMessage'] = $lan['raw']['errorMessage'];
        $wanIp['raw']['wanErrorMessage'] = $wan['raw']['errorMessage'];
        return $wanIp['raw'];
    }

    /** @return array{connectFailed: bool, dnsFailed: bool, raw: array} */
    private function attempt(string $url, string $body, string $channel, ?int $readTimeout = null, ?string $resolveIp = null): array
    {
        $start = microtime(true);

        $ch = curl_init($url);
        $opts = [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::LAN_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => $readTimeout ?? self::READ_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-AURA-KEY: ' . $this->apiKey,
            ],
        ];

        if ($resolveIp !== null) {
            $parts = parse_url($url);
            $host  = $parts['host'] ?? '';
            $port  = $parts['port'] ?? (($parts['scheme'] ?? 'http') === 'https' ? 443 : 80);
            if ($host !== '') {
                // Pins DNS for this request only — preserves Host/SNI so
                // TLS validates against the hostname's real certificate,
                // unlike connecting to "https://{ip}/..." directly.
                $opts[CURLOPT_RESOLVE] = [$host . ':' . $port . ':' . $resolveIp];
            }
        }

        curl_setopt_array($ch, $opts);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Diagnostic log (2026-08-06 directive): the exact RAW response
        // AXON/AURA returned, so a failure ("still connecting…" fallback
        // on the widget) can be told apart as an HTTP error (502/504),
        // a curl-level failure (timeout/DNS/connect), or a malformed body
        // — without this, only a summarized errorMessage reached the log.
        // Truncated to 800 chars — enough to see the shape of a JSON error
        // or an HTML error page, never meant to capture full guest content
        // long-term (see Fase 3 of MOD_CONEXION_SATELLITE_AURA_M2M.md on
        // not logging prompts/replies in full).
        error_log(sprintf(
            '[PG-AI · AuraSatelliteClient] RAW (channel=%s, http=%d, curl_errno=%d%s): %s',
            $channel,
            $httpCode,
            $errno,
            $errno !== 0 ? ', curl_error=' . curl_strerror($errno) : '',
            substr((string) $response, 0, 800)
        ));

        $networkLatencyMs = (int) round((microtime(true) - $start) * 1000);
        $dnsFailed         = $errno === CURLE_COULDNT_RESOLVE_HOST;
        $connectFailed     = $dnsFailed || $errno === CURLE_COULDNT_CONNECT;

        if ($errno !== 0) {
            return [
                'connectFailed' => $connectFailed,
                'dnsFailed'     => $dnsFailed,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: 0,
                    errorMessage: 'Network error (curl ' . $errno . ': ' . curl_strerror($errno) . ') on ' . $channel . ' channel.',
                ),
            ];
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return [
                'connectFailed' => false,
                'dnsFailed'     => false,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: $httpCode,
                    errorMessage: 'Non-JSON response from ' . $channel . ' channel (HTTP ' . $httpCode . ').',
                ),
            ];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'connectFailed' => false,
                'dnsFailed'     => false,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: $httpCode,
                    errorMessage: (string) ($decoded['message'] ?? 'Unauthorized — check API key / tenant.'),
                ),
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'connectFailed' => false,
                'dnsFailed'     => false,
                'raw' => $this->result(
                    success: false,
                    channel: $channel,
                    networkLatencyMs: $networkLatencyMs,
                    httpCode: $httpCode,
                    errorMessage: (string) ($decoded['message'] ?? ('AURA responded with HTTP ' . $httpCode)),
                ),
            ];
        }

        // This AURA deployment nests the payload under "data" rather than
        // returning it flat at the top level (confirmed live, 2026-07-31 —
        // see docs/02_SYSTEM_CODEX_REGISTRY.md). Accept both shapes: prefer
        // "data" when present, fall back to top-level for a server that
        // matches the blueprint's flat contract literally.
        $payload = is_array($decoded['data'] ?? null) ? $decoded['data'] : $decoded;

        return [
            'connectFailed' => false,
            'dnsFailed'     => false,
            'raw' => $this->result(
                success: (($decoded['status'] ?? '') === 'success'),
                channel: $channel,
                networkLatencyMs: $networkLatencyMs,
                httpCode: $httpCode,
                response: $payload['response'] ?? null,
                engine: $payload['engine'] ?? null,
                model: $payload['model'] ?? null,
                reportedLatencyMs: isset($payload['latencyMs']) ? (int) $payload['latencyMs'] : null,
                tokensUsed: isset($payload['tokensUsed']) ? (int) $payload['tokensUsed'] : null,
                tokensRemaining: isset($payload['tokensRemaining']) ? (int) $payload['tokensRemaining'] : null,
                sessionId: $payload['sessionId'] ?? null,
                tenantName: $payload['tenantName'] ?? null,
                errorMessage: ($decoded['status'] ?? '') === 'error' ? (string) ($decoded['message'] ?? 'AURA returned status=error.') : null,
            ),
        ];
    }

    private function result(
        bool $success,
        string $channel,
        ?int $networkLatencyMs = null,
        int $httpCode = 0,
        ?string $response = null,
        ?string $engine = null,
        ?string $model = null,
        ?int $reportedLatencyMs = null,
        ?int $tokensUsed = null,
        ?int $tokensRemaining = null,
        ?string $sessionId = null,
        ?string $tenantName = null,
        ?string $errorMessage = null,
    ): array {
        return [
            'success'           => $success,
            'httpCode'          => $httpCode,
            'channelUsed'       => $channel,
            'networkLatencyMs'  => $networkLatencyMs,
            'reportedLatencyMs' => $reportedLatencyMs,
            'response'          => $response,
            'engine'            => $engine,
            'model'             => $model,
            'tokensUsed'        => $tokensUsed,
            'tokensRemaining'   => $tokensRemaining,
            'sessionId'         => $sessionId,
            'tenantName'        => $tenantName,
            'errorMessage'      => $errorMessage,
        ];
    }
}
