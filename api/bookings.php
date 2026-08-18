<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/bookings.php
 * Owner-only endpoint for agenda.php's booking calendar. Merges two data
 * sources for the calendar view, never duplicating one into the other:
 *   - `yacht_bookings` — formal reservations (🟢 confirmed/quote_sent/etc.)
 *   - `omnichannel_sessions` — chatbot leads with a captured route + date
 *     that never became a formal booking (🟡 interested) — read live, not
 *     copied, so a lead that later converts doesn't show up twice.
 *
 * Security pipeline mirrors api/leads.php: session auth, POST-only, CSRF
 * token (hash_equals + rotation — read-only actions never rotate, same
 * concurrency note as pg_ai_hub.php's other panels).
 *
 * Actions (POST `action`):
 *   list   — bookings + eligible leads for one calendar month (year, month)
 *   detail — one booking's full record (id) — or, if `lead_session_id` is
 *            passed instead, the equivalent view over a bare lead that
 *            hasn't been formalized into yacht_bookings yet
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

function lly_bookings_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_bookings_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_bookings_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_bookings_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
// Read-only endpoint ('list'/'detail' are its only actions) — never rotate,
// same reasoning as api/leads.php.
$rotatedCsrf = $expected;

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · bookings] DB unavailable: ' . $e->getMessage());
    lly_bookings_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

$action = (string) ($_POST['action'] ?? '');

switch ($action) {
    case 'list':
        $year  = (int) ($_POST['year'] ?? gmdate('Y'));
        $month = (int) ($_POST['month'] ?? gmdate('n'));
        if ($month < 1 || $month > 12) {
            lly_bookings_json('error', ['message' => 'Invalid month.', 'csrf_token' => $rotatedCsrf], 400);
        }
        $yacht  = trim((string) ($_POST['yacht'] ?? ''));
        $paxMin = (isset($_POST['pax_min']) && $_POST['pax_min'] !== '') ? (int) $_POST['pax_min'] : null;
        $paxMax = (isset($_POST['pax_max']) && $_POST['pax_max'] !== '') ? (int) $_POST['pax_max'] : null;

        lly_bookings_json('success', [
            'bookings'   => lly_bookings_list($pdo, $year, $month, $yacht, $paxMin, $paxMax),
            'csrf_token' => $rotatedCsrf,
        ]);
        // no break — lly_bookings_json exits

    case 'detail':
        $id            = (int) ($_POST['id'] ?? 0);
        $leadSessionId = (int) ($_POST['lead_session_id'] ?? 0);

        if ($id > 0) {
            $detail = lly_booking_detail($pdo, $id);
        } elseif ($leadSessionId > 0) {
            $detail = lly_lead_as_booking_detail($pdo, $leadSessionId);
        } else {
            lly_bookings_json('error', ['message' => 'Missing id or lead_session_id.', 'csrf_token' => $rotatedCsrf], 400);
        }

        if ($detail === null) {
            lly_bookings_json('error', ['message' => 'Booking not found.', 'csrf_token' => $rotatedCsrf], 404);
        }
        lly_bookings_json('success', $detail + ['csrf_token' => $rotatedCsrf]);
        // no break

    default:
        lly_bookings_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}

/**
 * Merges formal bookings with unconverted chatbot leads for one calendar
 * month. Degrades to an empty list (never a 500) if sql/011 hasn't been
 * run yet on this environment, same convention as api/leads.php with sql/009.
 */
function lly_bookings_list(PDO $pdo, int $year, int $month, string $yacht, ?int $paxMin, ?int $paxMax): array
{
    $monthStart = sprintf('%04d-%02d-01', $year, $month);
    $monthEnd   = date('Y-m-d', strtotime($monthStart . ' +1 month'));

    $out = [];

    try {
        $sql = 'SELECT id, session_id, yacht_name, guest_name, guest_phone, guest_email,
                       charter_date, charter_time_slot, pax_count, route_destination,
                       status, total_price, deposit_paid, payment_status
                FROM yacht_bookings
                WHERE charter_date >= :start AND charter_date < :end';
        $params = ['start' => $monthStart, 'end' => $monthEnd];

        if ($yacht !== '' && $yacht !== 'all') {
            $sql .= ' AND yacht_name = :yacht';
            $params['yacht'] = $yacht;
        }
        if ($paxMin !== null) {
            $sql .= ' AND pax_count >= :pax_min';
            $params['pax_min'] = $paxMin;
        }
        if ($paxMax !== null) {
            $sql .= ' AND pax_count <= :pax_max';
            $params['pax_max'] = $paxMax;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $row) {
            $row['source'] = 'booking';
            $out[] = $row;
        }
    } catch (\PDOException $e) {
        error_log('[PG-AI · bookings] yacht_bookings not ready (has sql/011 been run?): ' . $e->getMessage());
    }

    // 🟡 Interested tier — chatbot leads with a route + date captured
    // (core/PgAiActionProcessor.php, sql/009) that never got a
    // yacht_bookings row of their own. yacht filter doesn't apply here —
    // a bare lead has no yacht assigned yet, that only happens on formal
    // conversion.
    try {
        $sql = "SELECT s.id AS session_id, s.lead_date AS charter_date, s.lead_pax AS pax_count,
                       s.lead_route AS route_destination, s.lead_name AS guest_name,
                       s.lead_phone AS guest_phone, s.lead_email AS guest_email
                FROM omnichannel_sessions s
                LEFT JOIN yacht_bookings b ON b.session_id = s.id
                WHERE s.lead_date >= :start AND s.lead_date < :end
                  AND s.lead_route IS NOT NULL
                  AND b.id IS NULL";
        $params = ['start' => $monthStart, 'end' => $monthEnd];
        if ($paxMin !== null) {
            $sql .= ' AND s.lead_pax >= :pax_min';
            $params['pax_min'] = $paxMin;
        }
        if ($paxMax !== null) {
            $sql .= ' AND s.lead_pax <= :pax_max';
            $params['pax_max'] = $paxMax;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $row) {
            $row['id']                = null;
            $row['yacht_name']        = null;
            $row['charter_time_slot'] = null;
            $row['status']            = 'interested';
            $row['total_price']       = null;
            $row['deposit_paid']      = null;
            $row['payment_status']    = 'pending';
            $row['source']            = 'lead';
            $out[] = $row;
        }
    } catch (\PDOException $e) {
        error_log('[PG-AI · bookings] omnichannel_sessions lead query failed: ' . $e->getMessage());
    }

    usort($out, static fn ($a, $b) => strcmp((string) $a['charter_date'], (string) $b['charter_date']));

    return $out;
}

function lly_booking_detail(PDO $pdo, int $id): ?array
{
    try {
        $stmt = $pdo->prepare('SELECT * FROM yacht_bookings WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $booking = $stmt->fetch();
        if (!$booking) {
            return null;
        }
        return ['source' => 'booking', 'booking' => $booking];
    } catch (\PDOException $e) {
        error_log('[PG-AI · bookings] Booking detail query failed: ' . $e->getMessage());
        return null;
    }
}

/** Same shape as lly_booking_detail() but sourced live from a lead that was never formalized — lets the modal render identically either way. */
function lly_lead_as_booking_detail(PDO $pdo, int $sessionId): ?array
{
    try {
        $stmt = $pdo->prepare(
            'SELECT s.id, s.session_uuid, s.lead_date AS charter_date, s.lead_pax AS pax_count,
                    s.lead_route AS route_destination, s.lead_name AS guest_name,
                    s.lead_phone AS guest_phone, s.lead_email AS guest_email, s.summary,
                    ch.channel_type
             FROM omnichannel_sessions s
             JOIN omnichannel_channels ch ON ch.id = s.channel_id
             WHERE s.id = :sid LIMIT 1'
        );
        $stmt->execute(['sid' => $sessionId]);
        $lead = $stmt->fetch();
        if (!$lead) {
            return null;
        }
        return ['source' => 'lead', 'booking' => $lead];
    } catch (\PDOException $e) {
        error_log('[PG-AI · bookings] Lead-as-booking detail query failed: ' . $e->getMessage());
        return null;
    }
}
