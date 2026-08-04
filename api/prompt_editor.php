<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/prompt_editor.php
 * Owner + super_admin endpoint for pg_ai_config.php's Master Prompt
 * editor. Reads/writes core/prompts/pg_ai_lester_master.md directly —
 * the exact file core/ProxyBridge.php::readKnowledgeBase() sends as the
 * AI's system prompt on every dispatch. Editing here changes what the
 * live chatbot says on the next message (APCu cache is keyed by the
 * file's mtime, so a save invalidates it automatically).
 *
 * Security pipeline mirrors api/fleet_catalog.php: session auth,
 * POST-only, CSRF token (rotates only on 'save'), LOCK_EX write.
 *
 * Actions (POST `action`):
 *   get  — current file content
 *   save — new content (full replace — this is a text editor, not a
 *          diff/patch tool)
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

const PROMPT_PATH = __DIR__ . '/../core/prompts/pg_ai_lester_master.md';

function lly_pe_json(string $status, array $extra = [], int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!lly_is_authenticated()) {
    lly_pe_json('error', ['message' => 'Unauthorized — please log in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_pe_json('error', ['message' => 'Method not allowed.'], 405);
}

$submitted = (string) ($_POST['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');
if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_pe_json('error', ['message' => 'Invalid or expired CSRF token.'], 403);
}
$lly_pe_action_preview = (string) ($_POST['action'] ?? '');
if ($lly_pe_action_preview === 'save') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$rotatedCsrf = $_SESSION['csrf_token'];

$action = (string) ($_POST['action'] ?? '');

switch ($action) {
    case 'get':
        if (!is_readable(PROMPT_PATH)) {
            lly_pe_json('error', ['message' => 'Prompt file not found on this environment.', 'csrf_token' => $rotatedCsrf], 404);
        }
        lly_pe_json('success', [
            'content'    => file_get_contents(PROMPT_PATH),
            'csrf_token' => $rotatedCsrf,
        ]);
        // no break — lly_pe_json exits

    case 'save':
        $content = (string) ($_POST['content'] ?? '');
        if (trim($content) === '') {
            lly_pe_json('error', ['message' => 'Content cannot be empty.', 'csrf_token' => $rotatedCsrf], 400);
        }

        $ok = file_put_contents(PROMPT_PATH, $content, LOCK_EX);
        if ($ok === false) {
            lly_pe_json('error', ['message' => 'Could not write the prompt file — check permissions.', 'csrf_token' => $rotatedCsrf], 500);
        }

        // APCu cache in ProxyBridge::readKnowledgeBase() is keyed by this
        // file's mtime, so the write above already invalidates it — no
        // separate cache-clear call needed.
        lly_pe_json('success', ['csrf_token' => $rotatedCsrf]);
        // no break

    default:
        lly_pe_json('error', ['message' => 'Unknown action.', 'csrf_token' => $rotatedCsrf], 400);
}
