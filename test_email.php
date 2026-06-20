<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — Infrastructure Diagnostic: SMTP Connection Test
 * TEMPORARY FILE. Delete this file (and test_db.php) from the server once
 * connectivity has been confirmed.
 *
 * Access:   https://lly.tourfindy.com/test_email.php?token=<DIAG_TOKEN>
 * Without a matching token, this returns 404 and does nothing.
 *
 * This performs a real SMTP-over-TLS handshake (EHLO + AUTH LOGIN) against
 * the mail server and confirms the server accepts the credentials — it
 * deliberately does NOT send a test email, so re-running this during
 * debugging never spams hello@lly.tourfindy.com's outbox or any recipient.
 */

function ll_load_env(string $path): array
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
        [$key, $val] = explode('=', $line, 2);
        $vars[trim($key)] = trim($val, " \t\"'");
    }
    return $vars;
}

$env = ll_load_env(__DIR__ . '/core/.env');

$expectedToken = $env['DIAG_TOKEN'] ?? '';
$providedToken = $_GET['token'] ?? '';
if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(404);
    exit;
}

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$host = $env['SMTP_HOST'] ?? '';
$port = (int) ($env['SMTP_PORT'] ?? 465);
$user = $env['SMTP_USER'] ?? '';
$pass = $env['SMTP_PASS'] ?? '';

$ok = false;

$context = stream_context_create(['ssl' => [
    'verify_peer'       => true,
    'verify_peer_name'  => true,
]]);

$sock = @stream_socket_client(
    "ssl://{$host}:{$port}",
    $errno,
    $errstr,
    8,
    STREAM_CLIENT_CONNECT,
    $context
);

if ($sock) {
    stream_set_timeout($sock, 8);
    $readLine = static fn () => fgets($sock, 512);

    $readLine(); // banner

    fwrite($sock, "EHLO {$host}\r\n");
    do {
        $line = $readLine();
    } while ($line !== false && isset($line[3]) && $line[3] === '-');

    fwrite($sock, "AUTH LOGIN\r\n");
    $readLine();
    fwrite($sock, base64_encode($user) . "\r\n");
    $readLine();
    fwrite($sock, base64_encode($pass) . "\r\n");
    $authResponse = $readLine();

    fwrite($sock, "QUIT\r\n");
    fclose($sock);

    $ok = $authResponse !== false && substr(trim($authResponse), 0, 3) === '235';
    if (!$ok) {
        error_log('[LLY diag] SMTP auth failed: ' . trim((string) $authResponse));
    }
} else {
    error_log("[LLY diag] SMTP socket connection failed: {$errstr} ({$errno})");
}

$statusColor = $ok ? '#10B981' : '#EF4444';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex, nofollow">
<title>LLY · SMTP Connection Test</title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;background:#0f1115;color:#eee;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
  .card{background:#1b1e25;border-radius:14px;padding:2.25rem 2.75rem;text-align:center;max-width:440px;border:1px solid <?= $statusColor ?>}
  h1{font-size:1.05rem;margin:0 0 1.25rem;font-weight:600;color:#fff}
  .status{font-size:1.3rem;font-weight:700;color:<?= $statusColor ?>;line-height:1.6}
  .lang{display:block;font-size:.95rem;margin:.15rem 0}
  .note{font-size:.78rem;color:#888;margin-top:1.5rem;line-height:1.5}
</style>
</head>
<body>
  <div class="card">
    <h1>Lover Lips Yachts — SMTP Connection Test</h1>
    <div class="status">
      <?php if ($ok): ?>
        <span class="lang" data-lang="en">✅ Mail Server Responding &amp; Authenticated</span>
        <span class="lang" data-lang="es">✅ Servidor de Correo Respondiendo y Autenticado</span>
      <?php else: ?>
        <span class="lang" data-lang="en">❌ Mail Server Test Failed</span>
        <span class="lang" data-lang="es">❌ Prueba de Servidor de Correo Fallida</span>
      <?php endif; ?>
    </div>
    <p class="note">
      <span class="lang" data-lang="en">No test email was sent — this only verifies the server accepts the credentials. This is a temporary diagnostic file — delete it from the server once verified.</span>
      <span class="lang" data-lang="es">No se envió ningún correo de prueba — esto solo verifica que el servidor acepta las credenciales. Este es un archivo de diagnóstico temporal — elimínalo del servidor una vez verificado.</span>
    </p>
  </div>
</body>
</html>
