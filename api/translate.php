<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/translate.php
 *
 * Backend proxy for the Book Editor Studio "Translate Missing Fields"
 * feature. Receives English source text and returns the Spanish
 * translation via Google Translate's public "gtx" endpoint.
 *
 * Provider: translate.googleapis.com (client=gtx) — no API key required,
 * chosen after DeepL's account-registration was regionally blocked for
 * the client. No secret to configure in core/.env for this endpoint.
 *
 * Security pipeline (mirrors api/book_editor.php):
 *   1. Auth check  (session + remember-me)
 *   2. Method guard (POST only)
 *   3. CSRF token  (hash_equals — NOT rotated here; this is a read-only
 *      utility call fired many times per click, not a state-mutating save.
 *      The token is still rotated normally when the form is actually saved.)
 *   4. Input validation (non-empty, length-capped)
 *   5. Provider call over HTTPS with a short timeout
 */

require __DIR__ . '/conexion.php';
require __DIR__ . '/../core/auth_check.php';

header('Content-Type: application/json; charset=utf-8');

function lly_json(string $status, string $msg, int $code = 200, array $data = []): never
{
    http_response_code($code);
    $payload = ['status' => $status, 'message' => $msg];
    if ($data !== []) {
        $payload['data'] = $data;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ── Layer 1: Auth ───────────────────────────────────────────────── */

if (!lly_is_authenticated()) {
    lly_json('error', 'Unauthorized — please log in.', 401);
}

/* ── Layer 2: Method ─────────────────────────────────────────────── */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_json('error', 'Method not allowed.', 405);
}

/* ── Layer 3: JSON body + CSRF ───────────────────────────────────── */

$raw  = file_get_contents('php://input');
$body = json_decode((string) $raw, true);

if (!is_array($body)) {
    lly_json('error', 'Malformed JSON body.', 400);
}

$submitted = (string) ($body['csrf_token'] ?? '');
$expected  = (string) ($_SESSION['csrf_token'] ?? '');

if ($expected === '' || !hash_equals($expected, $submitted)) {
    lly_json('error', 'Invalid or expired CSRF token.', 403);
}

/* ── Layer 4: Input validation ───────────────────────────────────── */

$text = trim((string) ($body['text'] ?? ''));

if ($text === '') {
    lly_json('error', 'No text provided to translate.', 400);
}

if (mb_strlen($text) > 8000) {
    lly_json('error', 'Text exceeds the 8000 character translation limit.', 400);
}

$targetLang = strtolower((string) ($body['target_lang'] ?? 'es'));
$sourceLang = strtolower((string) ($body['source_lang'] ?? 'en'));

/* ── Layer 5: Provider call — Google Translate public "gtx" endpoint ── */

$url = 'https://translate.googleapis.com/translate_a/single?'
     . http_build_query([
         'client' => 'gtx',
         'sl'     => $sourceLang,
         'tl'     => $targetLang,
         'dt'     => 't',
         'q'      => $text,
       ]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['User-Agent: Mozilla/5.0 (compatible; LLY-BookEditor/1.0)'],
]);

$response  = curl_exec($ch);
$curlErr   = curl_error($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    error_log('[LLY translate] cURL failed: ' . $curlErr);
    lly_json('error', 'Translation provider unreachable. Please try again.', 502);
}

if ($httpCode < 200 || $httpCode >= 300) {
    error_log("[LLY translate] Provider returned HTTP {$httpCode}: {$response}");
    lly_json('error', 'Translation provider rejected the request.', 502);
}

/* Google's gtx endpoint returns a deeply nested, undocumented JSON array —
   e.g. [[["Hola","Hello",null,null,1]], ...]. Long input is split into
   multiple sentence chunks; each chunk's translated text is in position
   [0], so we concatenate them in order to rebuild the full translation. */
$decoded = json_decode((string) $response, true);

$translated = '';
if (isset($decoded[0]) && is_array($decoded[0])) {
    foreach ($decoded[0] as $sentence) {
        $translated .= $sentence[0] ?? '';
    }
}

if ($translated === '') {
    error_log('[LLY translate] Unexpected provider response shape: ' . $response);
    lly_json('error', 'Translation provider returned an unexpected response.', 502);
}

echo json_encode([
    'status'  => 'success',
    'data'    => ['translated_text' => $translated],
], JSON_UNESCAPED_UNICODE);
