<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/OpenAiFallbackClient.php
 * PG-AI Pink Glove AI — Route 3 of the dispatch cascade (2026-08-03
 * directive): only called when both AURA routes (LAN + WAN, handled
 * internally by core/AuraSatelliteClient.php) have failed. Talks to
 * OpenAI's Chat Completions API directly — no other provider abstraction,
 * this is a narrow last-resort path, not a general LLM client.
 *
 * Configuration: FALLBACK_AI_PROVIDER_KEY / FALLBACK_AI_PROVIDER_MODEL in
 * core/.env (whitelisted via core/EnvSettingsStore.php, editable from
 * pg_ai_hub.php Section C — super_admin only).
 *
 * STATUS (2026-08-03): implemented against OpenAI's documented Chat
 * Completions contract, but NOT YET VALIDATED live — no real API key has
 * been provided yet (same situation AuraSatelliteClient::syncTenantContext()
 * was in before its contract was confirmed). Do not treat this path as
 * production-proven until it's been exercised against a real key and that
 * result is logged in docs/02_SYSTEM_CODEX_REGISTRY.md.
 */
final class OpenAiFallbackClient
{
    private const ENDPOINT        = 'https://api.openai.com/v1/chat/completions';
    private const CONNECT_TIMEOUT = 5;
    private const READ_TIMEOUT    = 30;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
    ) {
    }

    public static function fromEnv(string $envPath): self
    {
        $settings = EnvSettingsStore::getRaw($envPath);

        return new self(
            apiKey: $settings['FALLBACK_AI_PROVIDER_KEY'] ?? '',
            model: $settings['FALLBACK_AI_PROVIDER_MODEL'] ?? 'gpt-4o-mini',
        );
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @return array{success: bool, response: ?string, errorMessage: ?string}
     * Never throws — network/API failures degrade to success=false so the
     * caller (core/ProxyBridge.php) can fall through to the final
     * controlled "still connecting" reply instead of a raw error.
     */
    public function dispatch(string $systemPrompt, string $userMessage): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'response' => null, 'errorMessage' => 'OpenAI fallback not configured (missing FALLBACK_AI_PROVIDER_KEY).'];
        }

        try {
            $body = json_encode([
                'model'    => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'max_tokens' => 600,
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return ['success' => false, 'response' => null, 'errorMessage' => 'Payload encoding failed: ' . $e->getMessage()];
        }

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT        => self::READ_TIMEOUT,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
        ]);

        $response = curl_exec($ch);
        $errno    = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            return ['success' => false, 'response' => null, 'errorMessage' => 'Network error (curl ' . $errno . '): ' . curl_strerror($errno)];
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            return ['success' => false, 'response' => null, 'errorMessage' => 'Non-JSON response from OpenAI (HTTP ' . $httpCode . ').'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['success' => false, 'response' => null, 'errorMessage' => (string) ($decoded['error']['message'] ?? 'OpenAI responded with HTTP ' . $httpCode)];
        }

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if ($content === null || trim((string) $content) === '') {
            return ['success' => false, 'response' => null, 'errorMessage' => 'OpenAI response had no message content.'];
        }

        return ['success' => true, 'response' => (string) $content, 'errorMessage' => null];
    }
}
