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

require_once __DIR__ . '/api/conexion.php';
require_once __DIR__ . '/core/auth_check.php';

if (lly_is_authenticated()) {
    define('LLY_DASHBOARD_GATEKEEPER', true);
    require_once __DIR__ . '/dashboard.php';
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

  <!-- ══ AI WIDGET — Cognitive Omnichannel Operator (Web Widget) ═════
       Same markup/classes as book.php (Mandamiento 10 — no new CSS).
       index.php is only ever reached directly (never via a wrapper
       path like book.php's /my-book/), so pg_ai_widget.js's relative
       default gateway URL is correct here without setting
       window.PGAI_WIDGET_GATEWAY_URL. ─────────────────────────────── -->
  <button type="button" class="lly-ai-widget-fab" id="lly-ai-widget-fab" aria-label="Chat with us / Chatea con nosotros" aria-expanded="false">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.556-4.03 8.25-9 8.25a9.76 9.76 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
  </button>

  <div class="lly-ai-widget-panel" id="lly-ai-widget-panel" role="dialog" aria-modal="false" aria-label="Chat">
    <div class="lly-ai-widget-header">
      <div>
        <p class="lly-ai-widget-title">
          <span data-lang="en">Ask Us Anything</span>
          <span data-lang="es">Pregúntanos lo que Quieras</span>
        </p>
        <p class="lly-ai-widget-subtitle">
          <span data-lang="en">Lover Lips Yachts Assistant</span>
          <span data-lang="es">Asistente de Lover Lips Yachts</span>
        </p>
      </div>
      <button type="button" class="lly-ai-widget-close" id="lly-ai-widget-close" aria-label="Close chat / Cerrar chat">✕</button>
    </div>

    <div class="lly-ai-widget-messages" id="lly-ai-widget-messages">
      <div class="lly-ai-widget-msg lly-ai-widget-msg--bot" data-lang="en">Hi! Ask me anything about the fleet, the book, or your charter.</div>
      <div class="lly-ai-widget-msg lly-ai-widget-msg--bot" data-lang="es">¡Hola! Pregúntame lo que quieras sobre la flota, el libro o tu chárter.</div>
    </div>

    <form class="lly-ai-widget-inputrow" id="lly-ai-widget-form">
      <input type="text" class="lly-ai-widget-input" id="lly-ai-widget-input" maxlength="2000" autocomplete="off"
             data-placeholder-en="Type your message…" data-placeholder-es="Escribe tu mensaje…"
             aria-label="Message / Mensaje" />
      <button type="submit" class="lly-ai-widget-send" id="lly-ai-widget-send" aria-label="Send / Enviar">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
      </button>
    </form>
  </div>

  <script src="assets/js/main.js" defer></script>
  <script src="assets/js/auth.js" defer></script>
  <script src="assets/js/pg_ai_widget.js" defer></script>

</body>
</html>
