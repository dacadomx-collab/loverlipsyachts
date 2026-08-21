<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/inventory_checklists.php
 * Backend for checklist.php's Catamaran Inventory Checklist — persists every
 * pre/post-charter inspection as one row in ll_inventory_checklists (see
 * sql/012_create_ll_inventory_checklists.sql), the fleet's digital bitácora.
 *
 * Security pipeline mirrors api/leads.php / api/fleet_catalog.php: session
 * auth, POST-only, CSRF token (hash_equals + rotation on mutating actions
 * only), prepared statements.
 *
 * Actions (POST `action`):
 *   list   — historial rows (id, header fields, flagged counts, created_at),
 *            filtered by optional q (keyword — matches search_blob via
 *            FULLTEXT, falls back to LIKE if FULLTEXT is unavailable),
 *            date_from/date_to (charter_date range)
 *   get    — one full record incl. decoded payload_json, for the detail
 *            dialog and for checklist.php's "Edit" (populates the live form)
 *   save   — INSERTs a new row (a fresh inspection)
 *   update — overwrites one existing row by id (correcting a saved entry —
 *            checklist.php's "Edit" flow)
 *   delete — removes one row by id (checklist.php's "Delete", confirmed
 *            client-side via window.confirm(), same pattern as
 *            api/fleet_catalog.php's vessel delete)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

function lly_ic_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_ic_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_ic_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_ic_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}

$action = (string) ($_POST['action'] ?? '');

// Only mutating actions rotate the session-wide CSRF token — 'list'/'get'
// stay stable so concurrent read calls on page load don't invalidate each
// other's token (same reasoning as api/leads.php / api/fleet_catalog.php).
if (in_array($action, ['save', 'update', 'delete'], true)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[LLY · inventory_checklists] DB unavailable: ' . $e->getMessage());
    lly_ic_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

switch ($action) {
    case 'list':
        $q        = trim((string) ($_POST['q'] ?? ''));
        $dateFrom = trim((string) ($_POST['date_from'] ?? ''));
        $dateTo   = trim((string) ($_POST['date_to'] ?? ''));
        $dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) ? $dateFrom : null;
        $dateTo   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) ? $dateTo : null;

        lly_ic_json('success', [
            'checklists' => lly_ic_list($pdo, $q !== '' ? $q : null, $dateFrom, $dateTo),
            'csrf_token' => $rotatedCsrf,
        ]);
        // no break — lly_ic_json exits

    case 'get':
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            lly_ic_json('error', ['message' => 'Missing or invalid id.', 'csrf_token' => $rotatedCsrf], 400);
        }
        $record = lly_ic_get($pdo, $id);
        if ($record === null) {
            lly_ic_json('error', ['message' => 'Checklist not found.', 'csrf_token' => $rotatedCsrf], 404);
        }
        lly_ic_json('success', ['checklist' => $record, 'csrf_token' => $rotatedCsrf]);
        // no break

    case 'save':
        $id = lly_ic_save($pdo);
        lly_ic_json('success', ['id' => $id, 'csrf_token' => $rotatedCsrf]);
        // no break

    case 'update':
        $updateId = (int) ($_POST['id'] ?? 0);
        if ($updateId <= 0) {
            lly_ic_json('error', ['message' => 'Invalid or missing id.', 'csrf_token' => $rotatedCsrf], 400);
        }
        lly_ic_update($pdo, $updateId);
        lly_ic_json('success', ['id' => $updateId, 'csrf_token' => $rotatedCsrf]);
        // no break

    case 'delete':
        $deleteId = (int) ($_POST['id'] ?? 0);
        if ($deleteId <= 0) {
            lly_ic_json('error', ['message' => 'Invalid or missing id.', 'csrf_token' => $rotatedCsrf], 400);
        }
        lly_ic_delete($pdo, $deleteId);
        lly_ic_json('success', ['csrf_token' => $rotatedCsrf]);
        // no break

    default:
        lly_ic_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}

/**
 * Historial rows for the table — never the full payload_json (kept out of
 * the list response on purpose, it can get large across 10 sections).
 * Degrades to an empty list (never a 500) if sql/012 hasn't been run yet.
 */
function lly_ic_list(PDO $pdo, ?string $q, ?string $dateFrom, ?string $dateTo): array
{
    try {
        $sql = "SELECT id, vessel_name, charter_date, inspection_mode, guests_count,
                       captain_name, checked_by,
                       good_count, damaged_count, missing_count, replace_count,
                       created_at
                FROM ll_inventory_checklists WHERE 1=1";
        $params = [];

        if ($dateFrom !== null) {
            $sql .= ' AND (charter_date >= :date_from OR charter_date IS NULL)';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $sql .= ' AND (charter_date <= :date_to OR charter_date IS NULL)';
            $params['date_to'] = $dateTo;
        }

        if ($q !== null) {
            try {
                $test = $pdo->prepare($sql . ' AND MATCH(search_blob) AGAINST (:q IN NATURAL LANGUAGE MODE) LIMIT 200');
                $test->execute($params + ['q' => $q]);
                return $test->fetchAll();
            } catch (\PDOException) {
                // No FULLTEXT index (older schema) — fall back to a LIKE scan.
                $sql .= ' AND (search_blob LIKE :qlike OR vessel_name LIKE :qlike OR captain_name LIKE :qlike OR checked_by LIKE :qlike)';
                $params['qlike'] = '%' . $q . '%';
            }
        }

        $sql .= ' ORDER BY COALESCE(charter_date, DATE(created_at)) DESC, created_at DESC LIMIT 200';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (\PDOException $e) {
        error_log('[LLY · inventory_checklists] list failed (schema not ready?): ' . $e->getMessage());
        return [];
    }
}

/** One full record, payload_json decoded to an array for the detail dialog. */
function lly_ic_get(PDO $pdo, int $id): ?array
{
    try {
        $stmt = $pdo->prepare('SELECT * FROM ll_inventory_checklists WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['payload'] = json_decode((string) $row['payload_json'], true) ?: [];
        unset($row['payload_json']);
        return $row;
    } catch (\PDOException $e) {
        error_log('[LLY · inventory_checklists] get failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Reads + validates every save/update field from $_POST — shared by
 * lly_ic_save() (INSERT) and lly_ic_update() (UPDATE) so the two never
 * drift apart. items_json is the client's flat
 * {fieldId: {status,count,notes,expiry}} map (see assets/js/main.js §9c) —
 * stored as-is in payload_json; search_blob is rebuilt server-side from every
 * non-empty note plus the header text fields so Historial search covers all
 * of them through one FULLTEXT index.
 */
function lly_ic_collect_fields(): array
{
    $vessel      = trim(strip_tags((string) ($_POST['vessel_name'] ?? ''))) ?: 'NOMADA';
    $charterDate = trim((string) ($_POST['charter_date'] ?? ''));
    $charterDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $charterDate) ? $charterDate : null;
    $mode        = ($_POST['inspection_mode'] ?? 'before') === 'after' ? 'after' : 'before';
    $guests      = isset($_POST['guests_count']) && $_POST['guests_count'] !== ''
        ? max(0, (int) $_POST['guests_count']) : null;
    $captain     = trim(strip_tags((string) ($_POST['captain_name'] ?? ''))) ?: null;
    $checkedBy   = trim(strip_tags((string) ($_POST['checked_by'] ?? ''))) ?: null;
    $missingRpt  = trim(strip_tags((string) ($_POST['missing_report'] ?? ''))) ?: null;
    $actions     = trim(strip_tags((string) ($_POST['required_actions'] ?? ''))) ?: null;
    $signature   = trim(strip_tags((string) ($_POST['captain_signature'] ?? '')));
    $signedAt    = trim((string) ($_POST['signed_at'] ?? ''));

    if ($signature === '') {
        lly_ic_json('error', ['message' => 'Captain signature is required.'], 400);
    }

    $itemsRaw = (string) ($_POST['items_json'] ?? '{}');
    $items    = json_decode($itemsRaw, true);
    if (!is_array($items)) {
        $items = [];
    }

    $counts = ['good' => 0, 'damaged' => 0, 'missing' => 0, 'replace' => 0];
    $noteFragments = [];
    foreach ($items as $field) {
        if (!is_array($field)) {
            continue;
        }
        $status = (string) ($field['status'] ?? '');
        if (isset($counts[$status])) {
            $counts[$status]++;
        }
        $note = trim(strip_tags((string) ($field['notes'] ?? '')));
        if ($note !== '') {
            $noteFragments[] = $note;
        }
    }

    $searchBlob = implode(' ', array_filter([
        $vessel, $captain, $checkedBy, $missingRpt, $actions, implode(' ', $noteFragments),
    ]));

    return [
        'vessel'           => $vessel,
        'charter_date'     => $charterDate,
        'mode'             => $mode,
        'guests'           => $guests,
        'captain'          => $captain,
        'checked_by'       => $checkedBy,
        'good'             => $counts['good'],
        'damaged'          => $counts['damaged'],
        'missing'          => $counts['missing'],
        'replace'          => $counts['replace'],
        'missing_report'   => $missingRpt,
        'required_actions' => $actions,
        'signature'        => $signature,
        'signed_at'        => $signedAt !== '' ? $signedAt : null,
        'payload'          => json_encode($items, JSON_UNESCAPED_UNICODE),
        'search_blob'      => $searchBlob !== '' ? $searchBlob : null,
        'user_id'          => $_SESSION['lly_user_id'] ?? null,
    ];
}

function lly_ic_save(PDO $pdo): int
{
    $f = lly_ic_collect_fields();

    $stmt = $pdo->prepare(
        'INSERT INTO ll_inventory_checklists
            (vessel_name, charter_date, inspection_mode, guests_count, captain_name, checked_by,
             good_count, damaged_count, missing_count, replace_count,
             missing_report, required_actions, captain_signature, signed_at,
             payload_json, search_blob, created_by_user_id)
         VALUES
            (:vessel, :charter_date, :mode, :guests, :captain, :checked_by,
             :good, :damaged, :missing, :replace,
             :missing_report, :required_actions, :signature, :signed_at,
             :payload, :search_blob, :user_id)'
    );
    $stmt->execute($f);

    return (int) $pdo->lastInsertId();
}

/** Overwrites one existing bitácora row in place — checklist.php's "Edit" flow. */
function lly_ic_update(PDO $pdo, int $id): void
{
    $f = lly_ic_collect_fields();
    unset($f['user_id']); // created_by_user_id is set once at INSERT and never reassigned on edit
    $f['id'] = $id;

    $stmt = $pdo->prepare(
        'UPDATE ll_inventory_checklists SET
            vessel_name = :vessel, charter_date = :charter_date, inspection_mode = :mode,
            guests_count = :guests, captain_name = :captain, checked_by = :checked_by,
            good_count = :good, damaged_count = :damaged, missing_count = :missing, replace_count = :replace,
            missing_report = :missing_report, required_actions = :required_actions,
            captain_signature = :signature, signed_at = :signed_at,
            payload_json = :payload, search_blob = :search_blob
         WHERE id = :id'
    );
    $stmt->execute($f);
}

/** Removes one bitácora row — checklist.php confirms client-side via window.confirm() before calling this. */
function lly_ic_delete(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM ll_inventory_checklists WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
