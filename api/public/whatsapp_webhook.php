<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — api/public/whatsapp_webhook.php
 * PG-AI Pink Glove AI — WhatsApp Cloud API normalizer (Fase 3 + Fase 5 — see
 * modulos/MOD_OPERADOR_COGNITIVO_OMNICANAL.md).
 *
 * GET  — Meta's subscription handshake (hub.mode / hub.verify_token / hub.challenge).
 * POST — inbound message events. Signature-verified against the raw body
 * (X-Hub-Signature-256), normalized to OCMC, persisted to omnichannel_*
 * (same tables the web widget writes to — this is what makes WhatsApp and
 * the site widget share one conversation history per tenant), forwarded
 * through ProxyBridge, and replied to the same WhatsApp thread.
 *
 * Requires in core/.env: WHATSAPP_VERIFY_TOKEN, WHATSAPP_APP_SECRET,
 * WHATSAPP_ACCESS_TOKEN, WHATSAPP_PHONE_NUMBER_ID. Until those are set
 * (Meta app not provisioned yet), POST still validates+persists+forwards
 * but the outbound send degrades to a logged no-op — never a 500 back to
 * Meta, which would trigger their retry/backoff penalties.
 */

require __DIR__ . '/../../core/ProxyBridge.php';
require __DIR__ . '/../../core/OmnichannelRepository.php';
require __DIR__ . '/../../core/PgAiActionProcessor.php';
require __DIR__ . '/../../core/EphemeralLinkManager.php';
require __DIR__ . '/../../core/pgai_templates.php';
require __DIR__ . '/../conexion.php';

/** Minimal .env reader — same convention as ProxyBridge::loadEnv() (private there, duplicated here by design). */
function lly_wa_load_env(string $path): array
{
    $vars = [];
    if (!is_readable($path)) {
        return $vars;
    }
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $vars[trim($k)] = trim($v, " \t\"'");
    }
    return $vars;
}

$env = lly_wa_load_env(__DIR__ . '/../../core/.env');

/* ── GET — subscription handshake ─────────────────────────────────
   Meta flattens hub.mode -> hub_mode etc. when populating $_GET. */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode      = $_GET['hub_mode'] ?? '';
    $token     = (string) ($_GET['hub_verify_token'] ?? '');
    $challenge = $_GET['hub_challenge'] ?? '';
    $expected  = (string) ($env['WHATSAPP_VERIFY_TOKEN'] ?? '');

    if ($expected !== '' && $mode === 'subscribe' && hash_equals($expected, $token)) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(200);
        echo $challenge;
        exit;
    }

    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

/* ── POST — signature validation against the raw body ─────────────
   Must run before any json_decode: re-serializing the payload would
   change byte order/escaping and break the HMAC comparison. */
$rawBody   = (string) file_get_contents('php://input');
$appSecret = (string) ($env['WHATSAPP_APP_SECRET'] ?? '');

function lly_wa_validate_signature(string $rawBody, string $appSecret): bool
{
    if ($appSecret === '') {
        return false;
    }
    $header = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    if ($header === '') {
        return false;
    }
    $parts = explode('=', $header, 2);
    if (count($parts) !== 2 || strtolower($parts[0]) !== 'sha256') {
        return false;
    }
    return hash_equals(hash_hmac('sha256', $rawBody, $appSecret), $parts[1]);
}

$signatureValid = lly_wa_validate_signature($rawBody, $appSecret);

$decoded = json_decode($rawBody, true);
if (!is_array($decoded)) {
    http_response_code(400);
    exit;
}

$bridge   = ProxyBridge::fromEnv();
$tenantId = $bridge->getTenantId();

/* ── Audit trail: log the raw event before any further processing,
   even if the signature is invalid — Fase 2 requirement (omnichannel_webhooks). */
try {
    $pdo = Conexion::getConnection();
    $log = $pdo->prepare(
        'INSERT INTO omnichannel_webhooks (tenant_id, channel_type, raw_payload, signature_valid)
         VALUES (:tenant, :type, :payload, :valid)'
    );
    $log->execute([
        'tenant'  => $tenantId,
        'type'    => 'whatsapp',
        'payload' => $rawBody,
        'valid'   => $signatureValid ? 1 : 0,
    ]);
} catch (\Throwable $e) {
    error_log('[PG-AI · whatsapp_webhook] Could not log raw webhook — ' . $e->getMessage());
}

// Meta re-delivers on anything but a 200 — but we must not process an
// unsigned/forged payload. Ack with 200 so Meta stops retrying, and stop.
// Fail-closed either way — the distinction below is only for the log:
// "not provisioned yet" is the expected state until Meta app setup is
// complete (PG-AI degraded mode); "signature mismatch" is a real forgery
// attempt worth flagging differently in ops.
if (!$signatureValid) {
    if ($appSecret === '') {
        error_log('[PG-AI · whatsapp_webhook] WhatsApp channel not provisioned yet (WHATSAPP_APP_SECRET unset) — event discarded, PG-AI degraded mode.');
    } else {
        error_log('[PG-AI · whatsapp_webhook] Signature mismatch — event discarded (possible forged request).');
    }
    http_response_code(200);
    exit('EVENT_RECEIVED');
}

/* ── Normalize each inbound message entry to OCMC and process ────── */
$entries = $decoded['entry'] ?? [];
foreach ((is_array($entries) ? $entries : []) as $entry) {
    $changes = $entry['changes'] ?? [];
    foreach ((is_array($changes) ? $changes : []) as $change) {
        $value    = $change['value'] ?? [];
        $messages = $value['messages'] ?? [];
        $contacts = $value['contacts'] ?? [];

        foreach ((is_array($messages) ? $messages : []) as $msg) {
            $waId        = (string) ($msg['from'] ?? '');
            $displayName = $contacts[0]['profile']['name'] ?? null;

            if ($waId === '') {
                continue;
            }

            $ocmc = [
                'channel'            => 'whatsapp',
                'channel_message_id' => (string) ($msg['id'] ?? bin2hex(random_bytes(12))),
                'contact'            => [
                    'external_id'  => $waId,
                    'display_name' => $displayName,
                ],
                // WhatsApp has no widget-style session_id — the wa_id itself
                // is stable per contact, so it doubles as the omnichannel
                // session_uuid seed (uniqid keeps it collision-free per thread open).
                'session_id' => 'wa_' . $waId,
                'message'    => [
                    'type'      => (string) ($msg['type'] ?? 'text'),
                    'text'      => $msg['text']['body'] ?? null,
                    'media_url' => null,
                ],
                'received_at' => gmdate('Y-m-d\TH:i:s\Z'),
            ];

            $response = $bridge->forward($ocmc);

            if (($response['status'] ?? '') === 'success' && !empty($response['reply'])) {
                try {
                    $response['reply'] = PgAiActionProcessor::process($pdo, (string) $response['reply'], 'whatsapp', $waId);
                } catch (\Throwable $e) {
                    error_log('[PG-AI · whatsapp_webhook] Action processing skipped — ' . $e->getMessage());
                }
            }

            try {
                $sessionPk = OmnichannelRepository::persistInbound($pdo, $tenantId, $ocmc);

                if (($response['status'] ?? '') === 'success' && !empty($response['reply'])) {
                    OmnichannelRepository::persistOutbound($pdo, $tenantId, $sessionPk, (string) $response['reply']);
                }
            } catch (\Throwable $e) {
                error_log('[PG-AI · whatsapp_webhook] Omnichannel persistence failed — ' . $e->getMessage());
            }

            if (($response['status'] ?? '') === 'success' && !empty($response['reply'])) {
                lly_wa_send_reply($env, $waId, (string) $response['reply']);
            }
        }
    }
}

http_response_code(200);
echo 'EVENT_RECEIVED';

/**
 * Best-effort send back to the WhatsApp Cloud API. Degrades to a logged
 * no-op when WHATSAPP_ACCESS_TOKEN / WHATSAPP_PHONE_NUMBER_ID aren't set
 * yet — the inbound side (persistence + AURA forwarding) still works so
 * the conversation history stays complete even before the Meta app is live.
 */
function lly_wa_send_reply(array $env, string $toWaId, string $text): void
{
    $accessToken  = (string) ($env['WHATSAPP_ACCESS_TOKEN'] ?? '');
    $phoneNumberId = (string) ($env['WHATSAPP_PHONE_NUMBER_ID'] ?? '');

    if ($accessToken === '' || $phoneNumberId === '') {
        error_log('[PG-AI · whatsapp_webhook] Reply not sent — WHATSAPP_ACCESS_TOKEN/WHATSAPP_PHONE_NUMBER_ID not configured.');
        return;
    }

    $body = json_encode([
        'messaging_product' => 'whatsapp',
        'to'                => $toWaId,
        'type'              => 'text',
        'text'              => ['body' => $text],
    ], JSON_UNESCAPED_UNICODE);

    $ch = curl_init("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
        ],
    ]);
    curl_exec($ch);
    if (curl_errno($ch) !== 0) {
        error_log('[PG-AI · whatsapp_webhook] Send failed: ' . curl_error($ch));
    }
    curl_close($ch);
}
