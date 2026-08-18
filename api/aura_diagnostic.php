<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/aura_diagnostic.php
 * Owner-only endpoint backing aura_diagnostic.php's live handshake +
 * prompt sandbox. Server-side only — the browser never sees
 * ACADEP_AURA_KEY; it only talks to this endpoint, which talks to AURA.
 *
 * Security pipeline mirrors api/ephemeral_links.php: session auth,
 * POST-only, CSRF token (hash_equals + rotation).
 *
 * Actions (POST `action`):
 *   handshake — fixed diagnostic ping (agent_id=diagnostic)
 *   prompt    — free-text sandbox prompt (field `prompt`)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/EnvSettingsStore.php';
require __DIR__ . '/../core/AuraSatelliteClient.php';

header('Content-Type: application/json; charset=utf-8');

function lly_aura_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_aura_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_aura_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_aura_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$rotatedCsrf            = $_SESSION['csrf_token'];

$action = (string) ($_POST['action'] ?? '');

if (!in_array($action, ['handshake', 'prompt'], true)) {
    lly_aura_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}

// (2026-08-18) The old plain "diagnostic ping" wording got interpreted by
// AURA's onboarded Lester persona as a service request — it applied
// NO_PRICE_WITHOUT_LEAD_DATA and asked the diagnostic for a customer name
// before "proceeding" (observed live, pg_ai_config.php's AURA test card).
// This explicit framing is a best-effort fix, not a guarantee — see the
// 2026-08-15 registry entry on AURA's fast-path not always honoring short
// embedded directives.
$prompt = $action === 'handshake'
    ? '[SYSTEM HEALTH CHECK — this is an internal connectivity test, not a guest. Do not ask for a name, email, or any booking/customer details.] Reply with one short sentence confirming you received this message.'
    : trim((string) ($_POST['prompt'] ?? ''));

if ($prompt === '') {
    lly_aura_json('error', ['message' => 'Prompt is required.', 'csrf_token' => $rotatedCsrf], 400);
}
if (mb_strlen($prompt) > 2000) {
    $prompt = mb_substr($prompt, 0, 2000);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['lly_aura_diag_session'])) {
    $_SESSION['lly_aura_diag_session'] = 'diag-' . bin2hex(random_bytes(8));
}
$sessionId = $_SESSION['lly_aura_diag_session'];

$client = AuraSatelliteClient::fromEnv(__DIR__ . '/../core/.env');
$result = $client->dispatch('diagnostic', $sessionId, $prompt);

// Best-effort telemetry — never blocks the response the operator is
// waiting on (Fase 3 of the blueprint: never a plaintext key in logs).
error_log(sprintf(
    '[AURA diagnostic] action=%s channel=%s http=%d success=%s net_ms=%s reported_ms=%s tokens_used=%s',
    $action,
    $result['channelUsed'],
    $result['httpCode'],
    $result['success'] ? '1' : '0',
    $result['networkLatencyMs'] ?? '-',
    $result['reportedLatencyMs'] ?? '-',
    $result['tokensUsed'] ?? '-',
));

lly_aura_json('success', [
    'result'     => $result,
    'csrf_token' => $rotatedCsrf,
]);
