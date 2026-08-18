<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — core/EnvSettingsStore.php
 * Whitelisted read/write access to core/.env for the PG-AI Hub settings
 * panel (pg_ai_hub.php Section C — AURA + WhatsApp connection fields).
 *
 * Only keys in ALLOWED_KEYS can ever be read or written through this
 * class — it never becomes a general-purpose .env editor. Writes replace
 * the value on an existing `KEY="..."` line in place (same convention as
 * Conexion::loadEnv()'s parser) so comments/structure/ordering survive.
 * If a whitelisted key has no line yet, it's appended once at EOF.
 */
final class EnvSettingsStore
{
    private const ALLOWED_KEYS = [
        'ACADEP_AURA_BASE_URL',
        'ACADEP_AURA_GATEWAY_ENDPOINT',
        'ACADEP_AURA_KEY',
        'ACADEP_AURA_TENANT',
        'ACADEP_AURA_AGENT_ID',
        'ACADEP_AURA_FALLBACK_URL',
        'ACADEP_AURA_FALLBACK_IP',
        'WHATSAPP_PHONE_NUMBER_ID',
        'WHATSAPP_ACCESS_TOKEN',
        'WHATSAPP_VERIFY_TOKEN',
        'WHATSAPP_APP_SECRET',
        'FALLBACK_AI_PROVIDER_KEY',
        'FALLBACK_AI_PROVIDER_MODEL',
        // Alias accepted alongside FALLBACK_AI_PROVIDER_KEY (2026-08-15) —
        // FALLBACK_AI_PROVIDER_KEY stays canonical (matches the existing
        // pg_ai_hub.php Section C field); this just means core/.env also
        // works if OPENAI_API_KEY is what gets pasted in. See
        // OpenAiFallbackClient::fromEnv() for the read-order.
        'OPENAI_API_KEY',
        // Which route core/ProxyBridge.php::forward() tries first —
        // 'openai' or 'aura'; the other stays as the automatic fallback
        // either way (see ProxyBridge::forward()'s docblock). Not a
        // secret — plain routing config.
        'PRIMARY_AI_PROVIDER',
    ];

    /** Keys whose value is never echoed back in full — only a masked tail. */
    private const SECRET_KEYS = [
        'ACADEP_AURA_KEY',
        'WHATSAPP_ACCESS_TOKEN',
        'WHATSAPP_APP_SECRET',
        'FALLBACK_AI_PROVIDER_KEY',
        'OPENAI_API_KEY',
    ];

    public static function isAllowedKey(string $key): bool
    {
        return in_array($key, self::ALLOWED_KEYS, true);
    }

    /**
     * Unmasked values for the whitelisted keys — server-side use only
     * (e.g. AuraSatelliteClient::fromEnv()). Never return this array's
     * contents in an HTTP response; use getAll() for anything client-facing.
     */
    public static function getRaw(string $envPath): array
    {
        $env    = self::readEnv($envPath);
        $result = [];

        foreach (self::ALLOWED_KEYS as $key) {
            $raw = $env[$key] ?? '';
            $result[$key] = ($raw !== 'TO_BE_SET') ? $raw : '';
        }

        return $result;
    }

    /**
     * Returns every allowed key with its current value — secrets masked
     * to their last 4 characters, plus an `is_set` flag so the UI can
     * show "configured" vs "TO_BE_SET" without ever exposing the value.
     */
    public static function getAll(string $envPath): array
    {
        $env    = self::readEnv($envPath);
        $result = [];

        foreach (self::ALLOWED_KEYS as $key) {
            $raw   = $env[$key] ?? '';
            $isSet = $raw !== '' && $raw !== 'TO_BE_SET';

            $result[$key] = [
                'is_set' => $isSet,
                'value'  => in_array($key, self::SECRET_KEYS, true)
                    ? self::maskSecret($raw)
                    : ($isSet ? $raw : ''),
            ];
        }

        return $result;
    }

    /**
     * Writes one whitelisted key. Throws on any non-whitelisted key so a
     * caller mistake can never touch DB_PASS/FTP_PASS/etc.
     */
    public static function set(string $envPath, string $key, string $value): void
    {
        if (!self::isAllowedKey($key)) {
            throw new InvalidArgumentException('Key is not editable from the PG-AI Hub settings panel.');
        }

        $lines   = is_readable($envPath) ? file($envPath, FILE_IGNORE_NEW_LINES) : [];
        $escaped = str_replace('"', '\\"', $value);
        $found   = false;

        foreach ($lines as $i => $line) {
            if (preg_match('/^(\s*)' . preg_quote($key, '/') . '\s*=/', $line)) {
                $lines[$i] = $key . '="' . $escaped . '"';
                $found     = true;
                break;
            }
        }

        if (!$found) {
            $lines[] = $key . '="' . $escaped . '"';
        }

        $ok = file_put_contents($envPath, implode("\n", $lines) . "\n", LOCK_EX);
        if ($ok === false) {
            throw new RuntimeException('Could not write core/.env — check file permissions.');
        }
    }

    private static function maskSecret(string $value): string
    {
        if ($value === '' || $value === 'TO_BE_SET') {
            return '';
        }
        $tail = mb_substr($value, -4);
        return str_repeat('•', 8) . $tail;
    }

    /** Same KEY="value" / KEY=value parser as Conexion::loadEnv(). */
    private static function readEnv(string $path): array
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
            [$k, $v] = explode('=', $line, 2);
            $vars[trim($k)] = trim($v, " \t\"'");
        }
        return $vars;
    }
}
