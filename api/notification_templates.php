<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/notification_templates.php
 * Owner + super_admin endpoint for pg_ai_config.php's lead-notification
 * template editor. Reads/writes `ll_notification_templates` — the
 * internal "new lead captured" alert content, not the guest-facing
 * chatbot quote templates (core/pgai_templates.php).
 *
 * Security pipeline mirrors api/fleet_catalog.php: session auth,
 * POST-only, CSRF token (rotates only on 'update', never on 'list' —
 * see docs/02_SYSTEM_CODEX_REGISTRY.md for why), prepared statements.
 *
 * Actions (POST `action`):
 *   list   — every template row
 *   update — edit one row's subject/body (id required)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/NotificationTemplateRepository.php';

header('Content-Type: application/json; charset=utf-8');

function lly_nt_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_nt_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_nt_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_nt_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
$lly_nt_action_preview = (string) ($_POST['action'] ?? '');
if ($lly_nt_action_preview === 'update') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · notification_templates] DB unavailable: ' . $e->getMessage());
    lly_nt_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

$action = (string) ($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'list':
            lly_nt_json('success', [
                'templates'  => NotificationTemplateRepository::listAll($pdo),
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break — lly_nt_json exits

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_nt_json('error', ['message' => 'Invalid template id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $lly_nt_fields = [];
            if (isset($_POST['subject_en'])) { $lly_nt_fields['subject_en'] = trim(strip_tags((string) $_POST['subject_en'])); }
            if (isset($_POST['subject_es'])) { $lly_nt_fields['subject_es'] = trim(strip_tags((string) $_POST['subject_es'])); }
            if (isset($_POST['body_en']))    { $lly_nt_fields['body_en']    = trim((string) $_POST['body_en']); }
            if (isset($_POST['body_es']))    { $lly_nt_fields['body_es']    = trim((string) $_POST['body_es']); }
            NotificationTemplateRepository::update($pdo, $id, $lly_nt_fields);
            lly_nt_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        default:
            lly_nt_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (\Throwable $e) {
    error_log('[PG-AI · notification_templates] Unhandled error: ' . $e->getMessage());
    lly_nt_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}
