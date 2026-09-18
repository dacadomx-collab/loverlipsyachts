<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/inventory_catalog.php
 * Backend for checklist.php's Kitchen Utensils tab — per-vessel utensil
 * catalog (ll_inventory_catalog), via core/InventoryCatalogRepository.php.
 * Not to be confused with api/inventory_checklists.php (the pre/post-charter
 * inspection log) — this is the editable master catalog of what utensils
 * exist on a vessel, kept separate on purpose (Mandamiento 10).
 *
 * Security pipeline mirrors api/fleet_catalog.php: session auth, POST-only,
 * CSRF token (hash_equals + rotation on mutating actions only), prepared
 * statements throughout.
 *
 * Actions (POST `action`):
 *   list               — items for one vessel+category (vessel_name required,
 *                          category defaults to 'kitchen')
 *   create             — new item (vessel_name, name_en, name_es required)
 *   update             — edit one item (id required)
 *   delete             — remove one item (id required)
 *   vessel_suggestions — distinct vessel names already used (autocomplete)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/InventoryCatalogRepository.php';

header('Content-Type: application/json; charset=utf-8');

function lly_invc_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_invc_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_invc_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_invc_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}

$action = (string) ($_POST['action'] ?? '');
if (in_array($action, ['create', 'update', 'delete'], true)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[LLY · inventory_catalog] DB unavailable: ' . $e->getMessage());
    lly_invc_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

const CONDITIONS = ['good', 'fair', 'damaged', 'missing'];

/** Normalizes/validates item fields from $_POST — never trusts raw input past this point. */
function lly_invc_read_fields(): array
{
    $fields = [];
    if (isset($_POST['vessel_name'])) {
        $fields['vessel_name'] = trim(strip_tags((string) $_POST['vessel_name']));
    }
    if (isset($_POST['category'])) {
        $cat = trim(strip_tags((string) $_POST['category']));
        $fields['category'] = $cat !== '' ? $cat : 'kitchen';
    }
    foreach (['name_en', 'name_es'] as $key) {
        if (isset($_POST[$key])) {
            $fields[$key] = trim(strip_tags((string) $_POST[$key]));
        }
    }
    if (isset($_POST['quantity'])) {
        $fields['quantity'] = max(0, (int) $_POST['quantity']);
    }
    if (isset($_POST['condition_status']) && in_array($_POST['condition_status'], CONDITIONS, true)) {
        $fields['condition_status'] = $_POST['condition_status'];
    }
    if (isset($_POST['note'])) {
        $note = trim(strip_tags((string) $_POST['note']));
        $fields['note'] = $note !== '' ? $note : null;
    }
    if (isset($_POST['display_order'])) {
        $fields['display_order'] = max(0, (int) $_POST['display_order']);
    }
    return $fields;
}

try {
    switch ($action) {
        case 'list':
            $vessel = trim(strip_tags((string) ($_POST['vessel_name'] ?? '')));
            if ($vessel === '') {
                lly_invc_json('error', ['message' => 'Vessel name is required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $category = trim(strip_tags((string) ($_POST['category'] ?? 'kitchen'))) ?: 'kitchen';
            lly_invc_json('success', [
                'items'      => InventoryCatalogRepository::listItems($pdo, $vessel, $category),
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break — lly_invc_json exits

        case 'create':
            $fields = lly_invc_read_fields();
            if (empty($fields['vessel_name']) || empty($fields['name_en']) || empty($fields['name_es'])) {
                lly_invc_json('error', ['message' => 'Vessel and both English/Spanish names are required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $id = InventoryCatalogRepository::create($pdo, $fields);
            lly_invc_json('success', ['id' => $id, 'csrf_token' => $rotatedCsrf]);
            // no break

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_invc_json('error', ['message' => 'Invalid item id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $fields = lly_invc_read_fields();
            if ((array_key_exists('name_en', $fields) && $fields['name_en'] === '') || (array_key_exists('name_es', $fields) && $fields['name_es'] === '')) {
                lly_invc_json('error', ['message' => 'Names cannot be empty.', 'csrf_token' => $rotatedCsrf], 400);
            }
            InventoryCatalogRepository::update($pdo, $id, $fields);
            lly_invc_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_invc_json('error', ['message' => 'Invalid item id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            InventoryCatalogRepository::delete($pdo, $id);
            lly_invc_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'vessel_suggestions':
            lly_invc_json('success', ['vessels' => InventoryCatalogRepository::listVesselNames($pdo), 'csrf_token' => $rotatedCsrf]);
            // no break

        default:
            lly_invc_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (\Throwable $e) {
    error_log('[LLY · inventory_catalog] Unhandled error: ' . $e->getMessage());
    lly_invc_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}
