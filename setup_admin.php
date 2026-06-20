<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — setup_admin.php
 * ONE-TIME provisioning script for the lly_users table.
 *
 * Why the password is never typed into this file: this repository is
 * public on GitHub. Any plaintext credential committed here stays in
 * the git history forever, even if deleted later. Instead, this script
 * shows a one-time form — you type the password directly in the browser,
 * it is hashed server-side with password_hash(PASSWORD_BCRYPT) and saved
 * straight to the database. The plaintext value never touches disk.
 *
 * TEMPORARY FILE. After successfully creating the admin row, this script
 * writes a lock marker (core/.setup_admin.lock) and refuses to run again.
 * Delete this file from the server once you've confirmed the account works.
 *
 * Access: https://lly.tourfindy.com/setup_admin.php?token=<DIAG_TOKEN>
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
$providedToken = $_GET['token'] ?? $_POST['token'] ?? '';
if ($expectedToken === '' || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(404);
    exit;
}

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$lockFile = __DIR__ . '/core/.setup_admin.lock';
$locked   = file_exists($lockFile);

$message = '';
$success = false;

if (!$locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['password_confirm'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address. / Correo inválido.';
    } elseif (strlen($password) < 10) {
        $message = 'Password must be at least 10 characters. / La contraseña debe tener al menos 10 caracteres.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match. / Las contraseñas no coinciden.';
    } else {
        $host = $env['DB_HOST'] ?? '';
        $name = $env['DB_NAME'] ?? '';
        $user = $env['DB_USER'] ?? '';
        $pass = $env['DB_PASS'] ?? '';

        mysqli_report(MYSQLI_REPORT_OFF);
        $link = @mysqli_connect($host, $user, $pass, $name, 3306);

        if (!$link) {
            $message = 'Database connection failed — see error_log. / Falló la conexión a la base de datos.';
            error_log('[LLY setup_admin] DB connection failed: ' . mysqli_connect_error());
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = mysqli_prepare(
                $link,
                'INSERT INTO lly_users (email, password_hash) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)'
            );
            mysqli_stmt_bind_param($stmt, 'ss', $email, $hash);

            if (mysqli_stmt_execute($stmt)) {
                file_put_contents($lockFile, 'locked at ' . date('c') . ' for ' . $email);
                $locked  = true;
                $success = true;
                $message = 'Admin account created. This script is now locked. / Cuenta creada. Este script ya está bloqueado.';
            } else {
                $message = 'Insert failed — see error_log. / Falló la inserción.';
                error_log('[LLY setup_admin] Insert failed: ' . mysqli_error($link));
            }

            mysqli_stmt_close($stmt);
            mysqli_close($link);
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex, nofollow">
<title>LLY · Admin Setup</title>
<style>
  body{font-family:Arial,Helvetica,sans-serif;background:#0f1115;color:#eee;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem}
  .card{background:#1b1e25;border-radius:14px;padding:2.25rem 2.5rem;max-width:420px;width:100%;border:1px solid <?= $success ? '#10B981' : '#D4AF37' ?>}
  h1{font-size:1.05rem;margin:0 0 1.25rem;font-weight:600;color:#fff}
  label{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#999;margin:1rem 0 .35rem}
  input{width:100%;box-sizing:border-box;padding:.7rem .85rem;border-radius:8px;border:1px solid #333;background:#14151a;color:#eee;font-size:.9rem}
  button{margin-top:1.5rem;width:100%;padding:.8rem;border:none;border-radius:999px;background:#FF007F;color:#fff;font-weight:700;letter-spacing:.05em;text-transform:uppercase;font-size:.78rem;cursor:pointer}
  .msg{margin-top:1rem;font-size:.8rem;color:<?= $success ? '#10B981' : '#FF007F' ?>;line-height:1.5}
  .note{margin-top:1.5rem;font-size:.75rem;color:#777;line-height:1.5}
</style>
</head>
<body>
  <div class="card">
    <h1>Lover Lips Yachts — Admin Account Setup</h1>

    <?php if ($locked): ?>
      <p class="msg">
        🔒 This script is locked — an admin account already exists or was just created.<br>
        🔒 Este script está bloqueado — ya existe una cuenta de administrador o se acaba de crear.
      </p>
      <p class="note">Delete setup_admin.php and core/.setup_admin.lock from the server now. / Elimina setup_admin.php y core/.setup_admin.lock del servidor ahora.</p>
    <?php else: ?>
      <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($providedToken, ENT_QUOTES) ?>">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required value="lester.keizer@gmail.com">

        <label for="password">New Password / Nueva Contraseña</label>
        <input id="password" name="password" type="password" required minlength="10" autocomplete="new-password">

        <label for="password_confirm">Confirm Password / Confirmar Contraseña</label>
        <input id="password_confirm" name="password_confirm" type="password" required minlength="10" autocomplete="new-password">

        <button type="submit">Create Admin Account</button>
      </form>
      <?php if ($message): ?><p class="msg"><?= htmlspecialchars($message, ENT_QUOTES) ?></p><?php endif; ?>
      <p class="note">This password is hashed with bcrypt and saved directly to the database — it is never written to any file. / Esta contraseña se hashea con bcrypt y se guarda directo en la base de datos — nunca se escribe en ningún archivo.</p>
    <?php endif; ?>
  </div>
</body>
</html>
