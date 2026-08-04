<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/test_connection.php
 * Owner-only diagnostic: confirms (a) api/conexion.php can reach the real
 * MySQL database with the current core/.env credentials, (b) whether
 * ll_fleet_catalog (sql/005_create_ll_fleet_catalog.sql) is provisioned
 * and how many verified vessels it holds, and (c) whether the AI dispatch
 * credentials PG-AI needs (AI_TENANT_ID / AI_SHARED_SECRET /
 * AI_GATEWAY_URL — see core/ProxyBridge.php::fromEnv()) are present.
 *
 * Never echoes a credential value — only presence booleans, same
 * masking discipline as core/EnvSettingsStore.php. Session-gated like
 * every other api/*.php diagnostic (aura_diagnostic.php, leads.php).
 *
 * Response contract: {"status":"success|error","message":"string","data":[]}
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

function lly_test_conn_json(string $status, string $message, array $data = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_test_conn_json('error', 'Unauthorized — please log in.', [], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_test_conn_json('error', 'Method not allowed.', [], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_test_conn_json('error', 'Invalid or expired CSRF token.', [], 403);
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$rotatedCsrf            = $_SESSION['csrf_token'];

/* ── (a) Database connection + write-path sanity (SELECT 1, no writes) ── */
$dbConnected = false;
$dbError     = null;
$pdo         = null;
try {
    $pdo = Conexion::getConnection();
    $pdo->query('SELECT 1');
    $dbConnected = true;
} catch (\Throwable $e) {
    $dbError = $e->getMessage();
    error_log('[PG-AI · test_connection] DB connection failed: ' . $dbError);
}

/* ── (b) Fleet Catalog table presence + verified vessel count ──────── */
$fleetTableReady   = false;
$fleetVerifiedCount = 0;
if ($dbConnected) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) AS c FROM ll_fleet_catalog WHERE verification_status = 'verified'");
        $fleetVerifiedCount = (int) $stmt->fetch()['c'];
        $fleetTableReady    = true;
    } catch (\PDOException $e) {
        // Table not created yet on this environment — sql/005 pending manual execution.
        $fleetTableReady = false;
    }
}

/* ── (c) AI dispatch credentials present? (booleans only, never values) ── */
$envPath   = __DIR__ . '/../core/.env';
$env       = [];
if (is_readable($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim($v, " \t\"'");
    }
}
$aiKeysPresent = [
    'AI_TENANT_ID'     => !empty($env['AI_TENANT_ID']),
    'AI_SHARED_SECRET' => !empty($env['AI_SHARED_SECRET']),
    'AI_GATEWAY_URL'   => !empty($env['AI_GATEWAY_URL']),
];
$aiFullyConfigured = !in_array(false, $aiKeysPresent, true);

$overallStatus = $dbConnected ? 'success' : 'error';
$message = $dbConnected
    ? 'Database connection OK.'
    : 'Database connection failed — see server error log for detail.';

lly_test_conn_json($overallStatus, $message, [
    'db_connected'          => $dbConnected,
    'db_error'              => $dbConnected ? null : $dbError,
    'fleet_catalog_ready'   => $fleetTableReady,
    'fleet_verified_count'  => $fleetVerifiedCount,
    'ai_dispatch_keys'      => $aiKeysPresent,
    'ai_dispatch_ready'     => $aiFullyConfigured,
    'csrf_token'            => $rotatedCsrf,
], $dbConnected ? 200 : 503);
