<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/module_doc_editor.php
 * super_admin-only endpoint for pg_ai_config.php's Knowledge Module
 * editor. Reads/writes modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md — the
 * agnostic Santuario_Genesis blueprint (Mandato de Sincronización
 * Génesis), not any Lover Lips Yachts business content. Restricted to
 * super_admin because editing this file is a governance action on the
 * reusable molde, not a business-content edit Lester should be doing.
 *
 * (2026-08-15) Repointed from modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md
 * (now archived at modulos/archive/) to the consolidated v2.1 blueprint,
 * which also absorbed the former modulos/CONCIERGE_PROMPT_GENERICO.md —
 * see the fused doc's own section 9 for the full changelog.
 *
 * Security pipeline mirrors api/fleet_catalog.php: session auth, role
 * check (server-side — never trust a hidden UI element as the only
 * gate), POST-only, CSRF token (rotates only on 'save'), LOCK_EX write.
 *
 * Actions (POST `action`):
 *   get  — current file content
 *   save — new content (full replace)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

const MODULE_DOC_PATH = __DIR__ . '/../modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md';

function lly_mde_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_mde_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if (($_SESSION['lly_role'] ?? '') !== 'super_admin') {
    lly_mde_json('error', ['message' => 'Forbidden — super_admin only.'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_mde_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_mde_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
$lly_mde_action_preview = (string) ($_POST['action'] ?? '');
if ($lly_mde_action_preview === 'save') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

$action = (string) ($_POST['action'] ?? '');

switch ($action) {
    case 'get':
        if (!is_readable(MODULE_DOC_PATH)) {
            lly_mde_json('error', ['message' => 'Module doc not found on this environment.', 'csrf_token' => $rotatedCsrf], 404);
        }
        lly_mde_json('success', [
            'content'    => file_get_contents(MODULE_DOC_PATH),
            'csrf_token' => $rotatedCsrf,
        ]);
        // no break — lly_mde_json exits

    case 'save':
        $content = (string) ($_POST['content'] ?? '');
        if (trim($content) === '') {
            lly_mde_json('error', ['message' => 'Content cannot be empty.', 'csrf_token' => $rotatedCsrf], 400);
        }

        $ok = file_put_contents(MODULE_DOC_PATH, $content, LOCK_EX);
        if ($ok === false) {
            lly_mde_json('error', ['message' => 'Could not write the module doc — check permissions.', 'csrf_token' => $rotatedCsrf], 500);
        }

        lly_mde_json('success', ['csrf_token' => $rotatedCsrf]);
        // no break

    default:
        lly_mde_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}
