<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/leads.php
 * Owner-only endpoint for PG-AI Pink Glove AI's unified leads viewer
 * (Dashboard Card #1). Reads the omnichannel_* tables persisted by
 * core/OmnichannelRepository.php — same tenant, same session thread
 * whether the lead came in via the WhatsApp webhook or the site widget.
 *
 * Security pipeline mirrors api/ephemeral_links.php: session auth,
 * POST-only, CSRF token (hash_equals + rotation), prepared statements.
 *
 * Actions (POST `action`):
 *   list   — recent leads (session + contact + channel + last message)
 *   detail — one session's executive summary + full message transcript
 *            (`session_id` int param — the omnichannel_sessions.id, not
 *            the public UUID) for pg_ai_hub.php's "Ver Resumen y Charla" modal
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

function lly_leads_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_leads_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_leads_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_leads_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
// This endpoint is read-only ('list' is its only action) — never rotate
// the session-wide CSRF token here. pg_ai_hub.php fires several panels'
// "list"/"get" calls concurrently on page load, all holding the same
// initial token; rotating on a read would invalidate that token out from
// under the other panels still in flight (confirmed live, 2026-08-03 —
// see docs/02_SYSTEM_CODEX_REGISTRY.md).
$rotatedCsrf = $expected;

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · leads] DB unavailable: ' . $e->getMessage());
    lly_leads_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

$action = (string) ($_POST['action'] ?? '');

switch ($action) {
    case 'list':
        $dateFrom = trim((string) ($_POST['date_from'] ?? ''));
        $dateTo   = trim((string) ($_POST['date_to'] ?? ''));
        $dateFrom = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) ? $dateFrom : null;
        $dateTo   = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) ? $dateTo : null;

        lly_leads_json('success', [
            'leads'      => lly_leads_list($pdo, 100, $dateFrom, $dateTo),
            'csrf_token' => $rotatedCsrf,
        ]);
        // no break — lly_leads_json exits

    case 'detail':
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        if ($sessionId <= 0) {
            lly_leads_json('error', ['message' => 'Missing or invalid session_id.', 'csrf_token' => $rotatedCsrf], 400);
        }
        $detail = lly_lead_detail($pdo, $sessionId);
        if ($detail === null) {
            lly_leads_json('error', ['message' => 'Lead not found.', 'csrf_token' => $rotatedCsrf], 404);
        }
        lly_leads_json('success', $detail + ['csrf_token' => $rotatedCsrf]);
        // no break

    default:
        lly_leads_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}

/**
 * Most recent open/active conversations across WhatsApp and the web
 * widget, each with its latest message as a preview. Degrades to an
 * empty list (never a 500) if sql/003_create_omnichannel_schema.sql
 * (or sql/009, for the lead_name/lead_phone/lead_email/summary columns)
 * has not been run yet on this environment — the panel still renders,
 * just with a "no leads yet" state. $dateFrom/$dateTo (Y-m-d, already
 * format-validated by the caller) filter on last_activity_at — "when the
 * conversation happened," the natural way to browse leads.php by date.
 */
function lly_leads_list(PDO $pdo, int $limit, ?string $dateFrom = null, ?string $dateTo = null): array
{
    $limit = max(1, min(100, $limit));

    try {
        $sql = "SELECT
                s.id, s.session_uuid, s.lead_date, s.lead_pax, s.lead_route, s.lead_contact,
                s.lead_name, s.lead_phone, s.lead_email, s.summary,
                s.status, s.last_activity_at,
                c.display_name, c.external_id, c.is_vip,
                ch.channel_type,
                (
                    SELECT m.content
                    FROM omnichannel_messages m
                    WHERE m.session_id = s.id
                    ORDER BY m.created_at DESC
                    LIMIT 1
                ) AS last_message
             FROM omnichannel_sessions s
             JOIN omnichannel_contacts c ON c.id = s.contact_id
             JOIN omnichannel_channels ch ON ch.id = s.channel_id
             WHERE 1=1";
        $params = [];

        if ($dateFrom !== null) {
            $sql .= ' AND DATE(s.last_activity_at) >= :date_from';
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo !== null) {
            $sql .= ' AND DATE(s.last_activity_at) <= :date_to';
            $params['date_to'] = $dateTo;
        }

        $sql .= " ORDER BY s.last_activity_at DESC LIMIT {$limit}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    } catch (\PDOException $e) {
        error_log('[PG-AI · leads] Omnichannel schema not ready: ' . $e->getMessage());
        return [];
    }
}

/** One session's lead fields/summary + its full ordered transcript, for the "Ver Resumen y Charla" modal. Null if the session doesn't exist. */
function lly_lead_detail(PDO $pdo, int $sessionId): ?array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT
                s.id, s.lead_date, s.lead_pax, s.lead_route,
                s.lead_name, s.lead_phone, s.lead_email, s.summary,
                s.status, s.last_activity_at,
                c.display_name, c.is_vip, ch.channel_type
             FROM omnichannel_sessions s
             JOIN omnichannel_contacts c ON c.id = s.contact_id
             JOIN omnichannel_channels ch ON ch.id = s.channel_id
             WHERE s.id = :sid
             LIMIT 1"
        );
        $stmt->execute(['sid' => $sessionId]);
        $session = $stmt->fetch();
        if (!$session) {
            return null;
        }

        $msgStmt = $pdo->prepare(
            'SELECT direction, content, created_at
             FROM omnichannel_messages
             WHERE session_id = :sid
             ORDER BY created_at ASC, id ASC'
        );
        $msgStmt->execute(['sid' => $sessionId]);

        return ['session' => $session, 'messages' => $msgStmt->fetchAll()];
    } catch (\PDOException $e) {
        error_log('[PG-AI · leads] Lead detail query failed: ' . $e->getMessage());
        return null;
    }
}
