<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — Infrastructure Diagnostic: Database Connection Test
 * TEMPORARY FILE. Delete this file (and test_email.php) from the server
 * once connectivity has been confirmed — do not leave diagnostic scripts
 * with production DB access permanently reachable.
 *
 * Access:   https://lly.tourfindy.com/test_db.php?token=<DIAG_TOKEN>
 * Without a matching token, this returns 404 and does nothing — it never
 * reveals its own existence, and never prints the DB host/user/password.
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

$host = $env['DB_HOST'] ?? '';
$name = $env['DB_NAME'] ?? '';
$user = $env['DB_USER'] ?? '';
$pass = $env['DB_PASS'] ?? '';

$ok = false;
mysqli_report(MYSQLI_REPORT_OFF);
$link = @mysqli_connect($host, $user, $pass, $name, 3306);
if ($link instanceof mysqli) {
    $ok = true;
    mysqli_close($link);
} else {
    error_log('[LLY diag] DB connection failed: ' . mysqli_connect_error());
}

$statusColor = $ok ? '#10B981' : '#EF4444';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex, nofollow">
<title>LLY · DB Connection Test</title>
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
    <h1>Lover Lips Yachts — Database Connection Test</h1>
    <div class="status">
      <?php if ($ok): ?>
        <span class="lang" data-lang="en">✅ Connection Test Passed</span>
        <span class="lang" data-lang="es">✅ Prueba de Conexión Exitosa</span>
      <?php else: ?>
        <span class="lang" data-lang="en">❌ Connection Test Failed</span>
        <span class="lang" data-lang="es">❌ Prueba de Conexión Fallida</span>
      <?php endif; ?>
    </div>
    <p class="note">
      <span class="lang" data-lang="en">This is a temporary diagnostic file — delete it from the server once verified.</span>
      <span class="lang" data-lang="es">Este es un archivo de diagnóstico temporal — elimínalo del servidor una vez verificado.</span>
    </p>
  </div>
</body>
</html>
