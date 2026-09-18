<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/crew.php
 * Backend for checklist.php's Crew tab — roles catalog (ll_crew_roles) +
 * per-vessel roster (ll_crew_members), via core/CrewRepository.php.
 *
 * Security pipeline mirrors api/fleet_catalog.php: session auth, POST-only,
 * CSRF token (hash_equals + rotation on mutating actions only), prepared
 * statements throughout.
 *
 * Actions (POST `action`):
 *   list_roles      — every position (id, label_en, label_es, display_order)
 *   create_role     — new position (label_en + label_es required)
 *   update_role     — edit one position (id required)
 *   delete_role     — remove one position (id required; blocked with 409
 *                      while any crew member still references it)
 *   list_members     — roster for one vessel (vessel_name required)
 *   create_member    — new crew member (vessel_name, role_id, full_name required)
 *   update_member    — edit one crew member (id required)
 *   delete_member     — remove one crew member (id required)
 *   vessel_suggestions — distinct vessel names already used (autocomplete)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/CrewRepository.php';

header('Content-Type: application/json; charset=utf-8');

function lly_crew_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_crew_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_crew_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_crew_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}

// Only mutating actions rotate the session-wide CSRF token — same reasoning
// as api/fleet_catalog.php (concurrent read calls on page load share the
// same initial token; rotating on a read would invalidate it out from
// under a still-in-flight sibling request).
const MUTATING_ACTIONS = ['create_role', 'update_role', 'delete_role', 'create_member', 'update_member', 'delete_member'];
$action = (string) ($_POST['action'] ?? '');
if (in_array($action, MUTATING_ACTIONS, true)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[LLY · crew] DB unavailable: ' . $e->getMessage());
    lly_crew_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

const STATUSES = ['active', 'inactive'];

/** Normalizes/validates role fields from $_POST — never trusts raw input past this point. */
function lly_crew_read_role_fields(): array
{
    $fields = [];
    foreach (['label_en', 'label_es'] as $key) {
        if (isset($_POST[$key])) {
            $fields[$key] = trim(strip_tags((string) $_POST[$key]));
        }
    }
    if (isset($_POST['display_order'])) {
        $fields['display_order'] = max(0, (int) $_POST['display_order']);
    }
    return $fields;
}

/** Normalizes/validates crew member fields from $_POST — never trusts raw input past this point. */
function lly_crew_read_member_fields(): array
{
    $fields = [];
    if (isset($_POST['vessel_name'])) {
        $fields['vessel_name'] = trim(strip_tags((string) $_POST['vessel_name']));
    }
    if (isset($_POST['role_id'])) {
        $fields['role_id'] = max(0, (int) $_POST['role_id']);
    }
    if (isset($_POST['full_name'])) {
        $fields['full_name'] = trim(strip_tags((string) $_POST['full_name']));
    }
    foreach (['phone', 'whatsapp', 'email'] as $key) {
        if (isset($_POST[$key])) {
            $val = trim(strip_tags((string) $_POST[$key]));
            $fields[$key] = $val !== '' ? $val : null;
        }
    }
    if (isset($_POST['status']) && in_array($_POST['status'], STATUSES, true)) {
        $fields['status'] = $_POST['status'];
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
        case 'list_roles':
            lly_crew_json('success', ['roles' => CrewRepository::listRoles($pdo), 'csrf_token' => $rotatedCsrf]);
            // no break — lly_crew_json exits

        case 'create_role':
            $fields = lly_crew_read_role_fields();
            if (empty($fields['label_en']) || empty($fields['label_es'])) {
                lly_crew_json('error', ['message' => 'Both English and Spanish labels are required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $id = CrewRepository::createRole($pdo, $fields);
            lly_crew_json('success', ['id' => $id, 'csrf_token' => $rotatedCsrf]);
            // no break

        case 'update_role':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_crew_json('error', ['message' => 'Invalid role id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $fields = lly_crew_read_role_fields();
            if ((array_key_exists('label_en', $fields) && $fields['label_en'] === '') || (array_key_exists('label_es', $fields) && $fields['label_es'] === '')) {
                lly_crew_json('error', ['message' => 'Labels cannot be empty.', 'csrf_token' => $rotatedCsrf], 400);
            }
            CrewRepository::updateRole($pdo, $id, $fields);
            lly_crew_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'delete_role':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_crew_json('error', ['message' => 'Invalid role id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $ok = CrewRepository::deleteRole($pdo, $id);
            if (!$ok) {
                lly_crew_json('error', ['message' => 'This position is still assigned to a crew member — reassign or remove them first.', 'csrf_token' => $rotatedCsrf], 409);
            }
            lly_crew_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'list_members':
            $vessel = trim(strip_tags((string) ($_POST['vessel_name'] ?? '')));
            if ($vessel === '') {
                lly_crew_json('error', ['message' => 'Vessel name is required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            lly_crew_json('success', ['members' => CrewRepository::listMembers($pdo, $vessel), 'csrf_token' => $rotatedCsrf]);
            // no break

        case 'create_member':
            $fields = lly_crew_read_member_fields();
            if (empty($fields['vessel_name']) || empty($fields['role_id']) || empty($fields['full_name'])) {
                lly_crew_json('error', ['message' => 'Vessel, position, and full name are required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $id = CrewRepository::createMember($pdo, $fields);
            lly_crew_json('success', ['id' => $id, 'csrf_token' => $rotatedCsrf]);
            // no break

        case 'update_member':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_crew_json('error', ['message' => 'Invalid crew member id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            $fields = lly_crew_read_member_fields();
            if (array_key_exists('full_name', $fields) && $fields['full_name'] === '') {
                lly_crew_json('error', ['message' => 'Full name cannot be empty.', 'csrf_token' => $rotatedCsrf], 400);
            }
            CrewRepository::updateMember($pdo, $id, $fields);
            lly_crew_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'delete_member':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                lly_crew_json('error', ['message' => 'Invalid crew member id.', 'csrf_token' => $rotatedCsrf], 400);
            }
            CrewRepository::deleteMember($pdo, $id);
            lly_crew_json('success', ['csrf_token' => $rotatedCsrf]);
            // no break

        case 'vessel_suggestions':
            lly_crew_json('success', ['vessels' => CrewRepository::listVesselNames($pdo), 'csrf_token' => $rotatedCsrf]);
            // no break

        default:
            lly_crew_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (\Throwable $e) {
    error_log('[LLY · crew] Unhandled error: ' . $e->getMessage());
    lly_crew_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}
