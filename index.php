<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — index.php
 * Server-side gatekeeper for the Owner Dashboard. This file decides
 * whether the visitor is authenticated BEFORE any HTML is sent. An
 * unauthenticated request never receives dashboard.php's markup — it
 * only ever sees the login screen below. There is nothing for a client
 * (browser, curl, view-source) to bypass, because the protected content
 * simply isn't in the response.
 */

require __DIR__ . '/api/conexion.php';

session_start();

function lly_check_remember_me(): bool
{
    if (empty($_COOKIE['lly_remember'])) {
        return false;
    }

    $token = (string) $_COOKIE['lly_remember'];
    $pdo   = Conexion::getConnection();

    $stmt = $pdo->prepare(
        'SELECT id, email FROM lly_users WHERE remember_token = :token AND token_expiry > NOW() LIMIT 1'
    );
    $stmt->execute(['token' => $token]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['lly_user_id'] = (int) $user['id'];
    $_SESSION['lly_email']   = $user['email'];

    // Rotate the token on every remember-login: sliding 30-day window,
    // and the old cookie value stops working the moment it's used once.
    $newToken  = bin2hex(random_bytes(32));
    $newExpiry = (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s');

    $update = $pdo->prepare('UPDATE lly_users SET remember_token = :token, token_expiry = :expiry WHERE id = :id');
    $update->execute(['token' => $newToken, 'expiry' => $newExpiry, 'id' => $user['id']]);

    setcookie('lly_remember', $newToken, [
        'expires'  => (new DateTimeImmutable('+30 days'))->getTimestamp(),
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    return true;
}

$isAuthenticated = !empty($_SESSION['lly_user_id']) || lly_check_remember_me();

if ($isAuthenticated) {
    define('LLY_DASHBOARD_GATEKEEPER', true);
    require __DIR__ . '/dashboard.php';
    exit;
}
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Owner Control Dashboard" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Owner Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <!-- Blocking: applies saved theme before first paint — prevents flash -->
  <script src="assets/js/theme-init.js"></script>
</head>

<body data-active-lang="en">

  <div class="auth-page">
    <div class="gate-card">
      <img class="gate-logo" src="assets/img/logo.png" alt="Lover Lips Yachts" />

      <p class="gate-eyebrow">
        <span data-lang="en">Private Access</span>
        <span data-lang="es">Acceso Privado</span>
      </p>
      <h1 class="gate-title" id="gate-title">
        <span data-lang="en">Owner Dashboard</span>
        <span data-lang="es">Panel del Propietario</span>
      </h1>
      <p class="gate-sub">
        <span data-lang="en">This dashboard contains confidential business information. Please sign in to continue.</span>
        <span data-lang="es">Este panel contiene información confidencial del negocio. Inicia sesión para continuar.</span>
      </p>

      <form id="login-form" class="gate-form" method="post" autocomplete="off">
        <label class="gate-label" for="login-email">
          <span data-lang="en">Email</span>
          <span data-lang="es">Correo</span>
        </label>
        <input id="login-email" name="email" class="gate-input" type="email" required autocomplete="username" />

        <label class="gate-label" for="login-password">
          <span data-lang="en">Password</span>
          <span data-lang="es">Contraseña</span>
        </label>
        <input id="login-password" name="password" class="gate-input" type="password" required autocomplete="current-password" />

        <div class="gate-remember">
          <input id="login-remember" name="remember" type="checkbox" />
          <label for="login-remember">
            <span data-lang="en">Remember this session for 30 days</span>
            <span data-lang="es">Recordar esta sesión por 30 días</span>
          </label>
        </div>

        <p id="login-error" class="gate-error">
          <span data-lang="en">Incorrect email or password.</span>
          <span data-lang="es">Correo o contraseña incorrectos.</span>
        </p>

        <button type="submit" id="login-submit" class="gate-submit">
          <span data-lang="en">Sign In</span>
          <span data-lang="es">Iniciar Sesión</span>
        </button>
      </form>

      <div class="gate-lang">
        <button type="button" onclick="setLang('en')">EN</button>
        <span>·</span>
        <button type="button" onclick="setLang('es')">ES</button>
      </div>
    </div>
  </div>

  <script src="assets/js/main.js" defer></script>
  <script src="assets/js/auth.js" defer></script>

</body>
</html>
