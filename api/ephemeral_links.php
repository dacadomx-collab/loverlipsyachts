<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/ephemeral_links.php
 * Owner-only endpoint for PG-AI Pink Glove AI's Self-Destruct Link module
 * (Dashboard panel). See core/EphemeralLinkManager.php and
 * modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md for the underlying design.
 * "PG-AI Pink Glove AI" is this project's product name for the combined
 * Chatbot IA + Omnichannel Handshake + Ephemeral Quotes module — an
 * internal/owner-facing label, never surfaced in public visitor copy.
 *
 * Security pipeline mirrors api/book_editor.php: session auth, POST-only,
 * CSRF token (hash_equals + rotation), prepared statements throughout.
 *
 * Actions (POST `action`):
 *   list                  — recent links + current default max_views
 *   create                — new link (title, resource_type, payload_html|target_url, max_views?)
 *   update_max_views      — change a single link's cap (id, max_views)
 *   revoke                — kill a link early (id)
 *   set_default_max_views — change the owner-wide default (max_views)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';
require __DIR__ . '/../core/EphemeralLinkManager.php';

header('Content-Type: application/json; charset=utf-8');

function lly_el_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_el_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_el_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_el_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
// Only mutating actions rotate the session-wide CSRF token — 'list' stays
// stable. pg_ai_hub.php fires several panels' read calls concurrently on
// page load, all holding the same initial token; rotating on a read would
// invalidate that token out from under the other panels still in flight
// (confirmed live, 2026-08-03 — see docs/02_SYSTEM_CODEX_REGISTRY.md).
$lly_el_action_preview = (string) ($_POST['action'] ?? '');
if (in_array($lly_el_action_preview, ['create', 'update_max_views', 'revoke', 'set_default_max_views'], true)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

try {
    $pdo = Conexion::getConnection();
} catch (RuntimeException $e) {
    error_log('[PG-AI · ephemeral_links] DB unavailable: ' . $e->getMessage());
    lly_el_json('error', ['message' => 'Database unavailable. Please try again later.'], 503);
}

$action = (string) ($_POST['action'] ?? '');
$userId = (int) ($_SESSION['lly_user_id'] ?? 0);

/**
 * Renders a public URL for a token. (2026-08-18) Was HTTP_HOST-derived
 * only, which silently dropped this project's subfolder (/loverlipsyachts/
 * locally, /cockpit/ on production) — prefers APP_URL from core/.env
 * (per-environment, not git-tracked) and only falls back to the
 * host-derived guess if APP_URL isn't configured yet. See
 * core/PgAiActionProcessor.php::publicUrl() for the sibling copy of this fix.
 */
function lly_el_public_url(string $token): string
{
    $appUrl = lly_el_read_app_url();
    if ($appUrl !== '') {
        return rtrim($appUrl, '/') . '/api/public/l.php?t=' . rawurlencode($token);
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "{$scheme}://{$host}/api/public/l.php?t=" . rawurlencode($token);
}

function lly_el_read_app_url(): string
{
    $path = __DIR__ . '/../core/.env';
    if (!is_readable($path)) {
        return '';
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_starts_with($line, 'APP_URL')) {
            continue;
        }
        if (preg_match('/^APP_URL\s*=\s*(.*)$/', $line, $m)) {
            return trim($m[1], " \t\"'");
        }
    }
    return '';
}

try {
    switch ($action) {
        case 'list':
            $links  = EphemeralLinkManager::listRecent($pdo, 50);
            $public = array_map(
                fn (array $l) => $l + ['public_url' => lly_el_public_url($l['token'])],
                $links
            );
            lly_el_json('success', [
                'links'               => $public,
                'default_max_views'   => EphemeralLinkManager::getDefaultMaxViews($pdo),
                'csrf_token'          => $rotatedCsrf,
            ]);
            // no break — lly_el_json exits

        case 'create':
            $title        = lly_el_text((string) ($_POST['title'] ?? ''));
            $resourceType = (string) ($_POST['resource_type'] ?? 'custom');
            $targetUrl    = trim((string) ($_POST['target_url'] ?? ''));
            $payloadHtml  = lly_el_safe_html((string) ($_POST['payload_html'] ?? ''));
            $maxViewsRaw  = $_POST['max_views'] ?? null;
            $maxViews     = ($maxViewsRaw !== null && $maxViewsRaw !== '') ? (int) $maxViewsRaw : null;

            if ($title === '') {
                lly_el_json('error', ['message' => 'Title is required.', 'csrf_token' => $rotatedCsrf], 400);
            }
            if ($userId <= 0) {
                lly_el_json('error', ['message' => 'Session has no owner id.', 'csrf_token' => $rotatedCsrf], 401);
            }

            $link = EphemeralLinkManager::create(
                $pdo,
                $userId,
                $title,
                $resourceType,
                $payloadHtml !== '' ? $payloadHtml : null,
                $targetUrl !== '' ? $targetUrl : null,
                $maxViews,
            );

            lly_el_json('success', [
                'link'       => $link + ['public_url' => lly_el_public_url($link['token'])],
                'csrf_token' => $rotatedCsrf,
            ]);
            // no break

        case 'update_max_views':
            $id        = (int) ($_POST['id'] ?? 0);
            $maxViews  = (int) ($_POST['max_views'] ?? 0);
            $ok        = $id > 0 && $maxViews > 0 && EphemeralLinkManager::updateMaxViews($pdo, $id, $maxViews);
            lly_el_json($ok ? 'success' : 'error', [
                'message'    => $ok ? null : 'Could not update — link may be revoked or the new cap is invalid.',
                'csrf_token' => $rotatedCsrf,
            ], $ok ? 200 : 409);
            // no break

        case 'revoke':
            $id = (int) ($_POST['id'] ?? 0);
            $ok = $id > 0 && EphemeralLinkManager::revoke($pdo, $id);
            lly_el_json($ok ? 'success' : 'error', ['csrf_token' => $rotatedCsrf], $ok ? 200 : 409);
            // no break

        case 'set_default_max_views':
            $maxViews = (int) ($_POST['max_views'] ?? 0);
            if ($maxViews <= 0) {
                lly_el_json('error', ['message' => 'Invalid value.', 'csrf_token' => $rotatedCsrf], 400);
            }
            EphemeralLinkManager::setDefaultMaxViews($pdo, $maxViews);
            lly_el_json('success', ['default_max_views' => $maxViews, 'csrf_token' => $rotatedCsrf]);
            // no break

        default:
            lly_el_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
    }
} catch (InvalidArgumentException $e) {
    lly_el_json('error', ['message' => $e->getMessage(), 'csrf_token' => $rotatedCsrf], 400);
} catch (\Throwable $e) {
    error_log('[PG-AI · ephemeral_links] Unhandled error: ' . $e->getMessage());
    lly_el_json('error', ['message' => 'Unexpected server error.', 'csrf_token' => $rotatedCsrf], 500);
}

function lly_el_text(string $input): string
{
    return strip_tags(trim($input));
}

/**
 * Allow-list for quote/itinerary bodies — a superset of api/book_editor.php's
 * lly_html(). `span`/`div` are included (with their attributes intact,
 * strip_tags() never touches attributes of an allowed tag) so PG-AI Pink
 * Glove AI quote templates keep their <span data-lang="en"|"es"> pairs —
 * without them, api/public/l.php would render both languages concatenated
 * instead of toggling per the Golden Rule (ALISER/ORO) bilingual contract.
 */
function lly_el_safe_html(string $input): string
{
    return strip_tags(trim($input), ['p', 'em', 'strong', 'br', 'ul', 'ol', 'li', 'h3', 'h4', 'table', 'tr', 'td', 'th', 'thead', 'tbody', 'span', 'div']);
}
