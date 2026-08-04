<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/pgai_settings.php
 * Owner-only endpoint for pg_ai_hub.php Section C (AURA + WhatsApp
 * connection settings). Reads/writes a fixed whitelist of core/.env
 * keys via core/EnvSettingsStore.php — never a general config editor.
 *
 * Security pipeline mirrors api/ephemeral_links.php: session auth,
 * POST-only, CSRF token (hash_equals + rotation).
 *
 * Actions (POST `action`):
 *   get  — current values (secrets masked, see EnvSettingsStore)
 *   save — key/value (key must be in the whitelist)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/EnvSettingsStore.php';

header('Content-Type: application/json; charset=utf-8');

function lly_pgs_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_pgs_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_pgs_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_pgs_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}

$envPath = __DIR__ . '/../core/.env';
$action  = (string) ($_POST['action'] ?? '');

// Only 'save' rotates the session-wide CSRF token — 'get' stays stable.
// pg_ai_hub.php fires several panels' read calls concurrently on page
// load, all holding the same initial token; rotating on a read would
// invalidate that token out from under the other panels still in flight
// (confirmed live, 2026-08-03 — see docs/02_SYSTEM_CODEX_REGISTRY.md).
if ($action === 'save') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    switch ($action) {
        case 'get':
            lly_pgs_json('success', [
                'settings'   => EnvSettingsStore::getAll($envPath),
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break — lly_pgs_json exits

        case 'save':
            $key   = (string) ($_POST['key'] ?? '');
            $value = trim((string) ($_POST['value'] ?? ''));

            if (!EnvSettingsStore::isAllowedKey($key)) {
                lly_pgs_json('error', ['message' => 'Unknown or non-editable setting.', 'csrf_token' => $rotatedCsrf], 400);
            }

            EnvSettingsStore::set($envPath, $key, $value);

            lly_pgs_json('success', [
                'settings'   => EnvSettingsStore::getAll($envPath),
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break

        default:
            lly_pgs_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (InvalidArgumentException $e) {
    lly_pgs_json('error', ['message' => $e->getMessage(), 'csrf_token' => $rotatedCsrf], 400);
} catch (\Throwable $e) {
    error_log('[PG-AI · pgai_settings] Unhandled error: ' . $e->getMessage());
    lly_pgs_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}
