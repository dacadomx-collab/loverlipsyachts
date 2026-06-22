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
require __DIR__ . '/core/auth_check.php';

if (lly_is_authenticated()) {
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
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
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
