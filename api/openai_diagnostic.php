<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/openai_diagnostic.php
 * Owner-only diagnostic backing pg_ai_config.php's "Test OpenAI Connection"
 * button (Credentials Vault, super_admin only). Sibling of
 * api/aura_diagnostic.php — same security pipeline, same telemetry style.
 *
 * Server-side only — the browser's API key input is only ever sent here
 * over the authenticated session to run one throwaway completion; it is
 * never written to core/.env unless the owner separately submits the
 * Save Connection Settings form (api/pgai_settings.php).
 *
 * Actions (POST `action`):
 *   test — dispatches one minimal completion. `api_key`/`model` are
 *     optional overrides so an unsaved candidate key can be validated
 *     before committing it; blank falls back to the persisted
 *     core/.env value via OpenAiFallbackClient::fromEnv().
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/EnvSettingsStore.php';
require __DIR__ . '/../core/OpenAiFallbackClient.php';

header('Content-Type: application/json; charset=utf-8');

function lly_openai_diag_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_openai_diag_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_openai_diag_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_openai_diag_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$rotatedCsrf            = $_SESSION['csrf_token'];

$action = (string) ($_POST['action'] ?? '');
if ($action !== 'test') {
    lly_openai_diag_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}

$envPath        = __DIR__ . '/../core/.env';
$candidateKey   = trim((string) ($_POST['api_key'] ?? ''));
$candidateModel = trim((string) ($_POST['model'] ?? ''));

if ($candidateKey !== '') {
    $client = new OpenAiFallbackClient($candidateKey, $candidateModel !== '' ? $candidateModel : 'gpt-4o-mini');
} else {
    $client = OpenAiFallbackClient::fromEnv($envPath);
    if ($candidateModel !== '') {
        // Persisted key, but the operator picked a different model in the
        // still-open form than what's saved — honor the visible selection.
        $client = new OpenAiFallbackClient($client->getApiKey(), $candidateModel);
    }
}

if (!$client->isConfigured()) {
    lly_openai_diag_json('success', [
        'result' => [
            'success'      => false,
            'httpCode'     => 0,
            'latencyMs'    => 0,
            'model'        => $candidateModel !== '' ? $candidateModel : 'gpt-4o-mini',
            'response'     => null,
            'errorMessage' => 'Not configured — enter an API key above or save one first.',
        ],
        'csrf_token' => $rotatedCsrf,
    ]);
}

$result = $client->dispatch(
    'You are a connectivity diagnostic probe. Reply with exactly one short sentence confirming you received this message.',
    'ping'
);

error_log(sprintf(
    '[PG-AI · openai_diagnostic] test: http=%d success=%s latency_ms=%d model=%s',
    $result['httpCode'],
    $result['success'] ? '1' : '0',
    $result['latencyMs'],
    $result['model'],
));

lly_openai_diag_json('success', [
    'result'     => $result,
    'csrf_token' => $rotatedCsrf,
]);
