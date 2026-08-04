<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/fleet_catalog.php
 * Owner-only endpoint for PG-AI Pink Glove AI's Fleet Catalog editor
 * (pg_ai_hub.php Section E). Reads/writes `ll_fleet_catalog` — the same
 * table core/ProxyBridge.php substitutes into the AI's system prompt
 * (core/FleetCatalogRepository.php::renderPromptMarkdownTable()) and
 * propuestas.php's Accordion 1 render — so a vessel Lester adds/edits
 * here is what both the AI and that private report show, from one source.
 *
 * Security pipeline mirrors api/ephemeral_links.php: session auth,
 * POST-only, CSRF token (hash_equals + rotation), prepared statements
 * throughout (core/FleetCatalogRepository.php).
 *
 * Actions (POST `action`):
 *   list   — every vessel, any verification_status (owner-only view — the
 *            AI only ever sees 'verified' rows via a separate read path)
 *   create — new vessel (vessel_name required; everything else optional)
 *   update — edit one vessel (id required)
 *   delete — remove one vessel (id required)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/FleetCatalogRepository.php';

header('Content-Type: application/json; charset=utf-8');

function lly_fc_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_fc_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_fc_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_fc_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
// Only mutating actions rotate the session-wide CSRF token — 'list' stays
// stable. pg_ai_hub.php fires several panels' read calls concurrently on
// page load, all holding the same initial token; rotating on a read would
// invalidate that token out from under the other panels still in flight
// (confirmed live, 2026-08-03 — see docs/02_SYSTEM_CODEX_REGISTRY.md).
$lly_fc_action_preview = (string) ($_POST['action'] ?? '');
if (in_array($lly_fc_action_preview, ['create', 'update', 'delete'], true)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · fleet_catalog] DB unavailable: ' . $e->getMessage());
    lly_fc_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

const STATUS_PILLS         = ['pill-pink', 'pill-gold', 'pill-green', 'pill-orange'];
const VERIFICATION_STATES  = ['verified', 'pending'];

/** Normalizes and validates the vessel fields shared by create/update — never trusts raw $_POST past this point. */
function lly_fc_read_fields(): array
{
    $fields = [];

    if (isset($_POST['vessel_name'])) {
        $fields['vessel_name'] = trim(strip_tags((string) $_POST['vessel_name']));
    }
    if (isset($_POST['vessel_slug'])) {
        $slug = trim(strip_tags((string) $_POST['vessel_slug']));
        $fields['vessel_slug'] = $slug !== '' ? $slug : null;
    }
    foreach (['role_label_en', 'role_label_es', 'rate_note_en', 'rate_note_es'] as $key) {
        if (isset($_POST[$key])) {
            $fields[$key] = trim(strip_tags((string) $_POST[$key]));
        }
    }
    if (isset($_POST['max_pax'])) {
        $raw = trim((string) $_POST['max_pax']);
        $fields['max_pax'] = $raw !== '' ? max(0, (int) $raw) : null;
    }
    if (isset($_POST['length_ft'])) {
        $raw = trim((string) $_POST['length_ft']);
        $fields['length_ft'] = $raw !== '' ? max(0, (int) $raw) : null;
    }
    if (isset($_POST['display_order'])) {
        $fields['display_order'] = max(0, (int) $_POST['display_order']);
    }
    if (isset($_POST['status_pill']) && in_array($_POST['status_pill'], STATUS_PILLS, true)) {
        $fields['status_pill'] = $_POST['status_pill'];
    }
    if (isset($_POST['verification_status']) && in_array($_POST['verification_status'], VERIFICATION_STATES, true)) {
        $fields['verification_status'] = $_POST['verification_status'];
    }

    return $fields;
}

$action = (string) ($_POST['action'] ?? '');

try {
    switch ($action) {
        case 'list':
            lly_fc_json('success', [
                'vessels'    => FleetCatalogRepository::listAll($pdo),
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break — lly_fc_json exits

        case 'create':
            $fields = lly_fc_read_fields();
            if (empty($fields['vessel_name'])) {
                lly_fc_json('error', ['message' => 'Vessel name is required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            // New vessels default to 'pending' regardless of what the client sent —
            // only an explicit later edit (a deliberate human action) promotes a
            // vessel to 'verified' (Mandamiento 4 — Anti-Alucinación).
            $fields['verification_status'] = 'pending';
            $id = FleetCatalogRepository::create($pdo, $fields);
            lly_fc_json('success', ['id' => $id, 'csrf_token' => $rotatedCsrf]);
            // no break

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_fc_json('error', ['message' => 'Invalid vessel id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $fields = lly_fc_read_fields();
            if (array_key_exists('vessel_name', $fields) && $fields['vessel_name'] === '') {
                lly_fc_json('error', ['message' => 'Vessel name cannot be empty.', 'csrf_token' => $rotatedCsrf], 400);
            }
            FleetCatalogRepository::update($pdo, $id, $fields);
            lly_fc_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_fc_json('error', ['message' => 'Invalid vessel id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            FleetCatalogRepository::delete($pdo, $id);
            lly_fc_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        default:
            lly_fc_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (\Throwable $e) {
    error_log('[PG-AI · fleet_catalog] Unhandled error: ' . $e->getMessage());
    lly_fc_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}
