<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/public/ai_widget_gateway.php
 * PG-AI Pink Glove AI — Synchronous Web Widget Gateway (Fase 3 — see
 * modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md).
 *
 * Public, unauthenticated endpoint: any site visitor talking to the
 * public chat widget hits this, not just the logged-in Owner. It
 * normalizes the widget's request into a partial OCMC message,
 * persists it (Fase 2 — omnichannel_*, shared with the WhatsApp
 * channel via OmnichannelRepository so both surfaces share one
 * conversation thread), and forwards it via ProxyBridge. No business
 * logic, no LLM keys, no knowledge content lives in this file.
 *
 * DB persistence is best-effort: a DB outage degrades to "keep chatting,
 * skip the history row" rather than a 500 to the visitor (Circuit
 * Breaker rule, docs/05_RUNTIME_GUARDRAILS.md).
 */

require __DIR__ . '/../../core/ProxyBridge.php';
require __DIR__ . '/../../core/OmnichannelRepository.php';
require __DIR__ . '/../../core/PgAiActionProcessor.php';
require __DIR__ . '/../../core/EphemeralLinkManager.php';
require __DIR__ . '/../../core/pgai_templates.php';
require __DIR__ . '/../conexion.php';

header('Content-Type: application/json; charset=utf-8');

function lly_widget_json(string $status, string $reply, int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $status, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
    exit;
}

const FALLBACK_COPY = [
    'en' => "Thanks for your message — I'm still connecting to the assistant. Please try again in a moment.",
    'es' => 'Gracias por tu mensaje — todavía me estoy conectando con el asistente. Intenta de nuevo en un momento.',
];
const RATE_LIMIT_COPY = [
    'en' => "You're sending messages a bit fast — please wait a few seconds and try again.",
    'es' => 'Estás enviando mensajes muy rápido — espera unos segundos e intenta de nuevo.',
];
const EMPTY_MESSAGE_COPY = [
    'en' => 'Please type a message first.',
    'es' => 'Por favor escribe un mensaje primero.',
];

/* ── Method guard ────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    lly_widget_json('error', 'Method not allowed.', 405);
}

/* ── Parse + sanitize input ──────────────────────────────────────── */
$raw = file_get_contents('php://input');
$in  = json_decode((string) $raw, true);
if (!is_array($in)) {
    $in = [];
}

$lang = (($in['lang'] ?? '') === 'es') ? 'es' : 'en';

$message = strip_tags(trim((string) ($in['message'] ?? '')));
if ($message === '') {
    lly_widget_json('error', EMPTY_MESSAGE_COPY[$lang], 400);
}
if (mb_strlen($message) > 2000) {
    $message = mb_substr($message, 0, 2000);
}

$sessionId = trim((string) ($in['session_id'] ?? ''));
if (!preg_match('/^[a-zA-Z0-9\-]{8,64}$/', $sessionId)) {
    lly_widget_json('error', 'Invalid session_id.', 400);
}

/* ── Lightweight per-session rate limit (no DB — session-scoped) ───
   15 messages per rolling 60s window is generous for a real visitor
   and cheap enough to not need Fase 2's tables just to demo Fase 3. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$now = time();
$rl  = $_SESSION['lly_ai_widget_rl'] ?? ['count' => 0, 'window_start' => $now];
if ($now - $rl['window_start'] > 60) {
    $rl = ['count' => 0, 'window_start' => $now];
}
$rl['count']++;
$_SESSION['lly_ai_widget_rl'] = $rl;

if ($rl['count'] > 15) {
    lly_widget_json('error', RATE_LIMIT_COPY[$lang], 429);
}

/* ── Build the partial OCMC message and forward it ──────────────── */
$ocmc = [
    'channel'            => 'web_widget',
    'channel_message_id' => bin2hex(random_bytes(12)),
    'contact'            => [
        'external_id'  => $sessionId,
        'display_name' => null,
    ],
    'session_id' => $sessionId,
    'message'    => [
        'type'      => 'text',
        'text'      => $message,
        'media_url' => null,
    ],
    'received_at' => gmdate('Y-m-d\TH:i:s\Z'),
];

$bridge   = ProxyBridge::fromEnv();
$response = $bridge->forward($ocmc);

/* ── Resolve PG-AI action sentinels (quote link / escalation) before
   the reply is persisted or shown — see core/PgAiActionProcessor.php.
   Best-effort: a failure here must never block the chat reply, so it
   degrades to the raw (sentinel-stripped-on-error) reply. ────────── */
if (($response['status'] ?? '') === 'success' && !empty($response['reply'])) {
    try {
        $pdoForAction = Conexion::getConnection();
        $response['reply'] = PgAiActionProcessor::process($pdoForAction, (string) $response['reply'], 'web_widget', $sessionId);
    } catch (\Throwable $e) {
        error_log('[PG-AI · ai_widget_gateway] Action processing skipped — ' . $e->getMessage());
    }
}

/* ── Persist inbound/outbound into omnichannel_* (Fase 2) ─────────
   Best-effort: a DB outage must never block the chat reply. */
try {
    $pdo       = Conexion::getConnection();
    $sessionPk = OmnichannelRepository::persistInbound($pdo, $bridge->getTenantId(), $ocmc);

    if (($response['status'] ?? '') === 'success' && !empty($response['reply'])) {
        OmnichannelRepository::persistOutbound($pdo, $bridge->getTenantId(), $sessionPk, (string) $response['reply']);
    }
} catch (\Throwable $e) {
    error_log('[PG-AI · ai_widget_gateway] Omnichannel persistence skipped — ' . $e->getMessage());
}

/* ── Map the bridge's response to a widget-ready reply ───────────── */
if (($response['status'] ?? '') === 'success' && !empty($response['reply'])) {
    lly_widget_json('success', (string) $response['reply']);
}

// Anything else (degraded, malformed, or the Gateway not provisioned
// yet) — controlled fallback copy, never a raw error to the visitor.
lly_widget_json('degraded', FALLBACK_COPY[$lang]);
