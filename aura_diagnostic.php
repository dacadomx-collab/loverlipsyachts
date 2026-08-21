<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — aura_diagnostic.php
 * Live M2M diagnostic view for the AURA satellite connection (see
 * modulos/MOD_CONEXION_SATELLITE_AURA_M2M.md, Fase 4 — Validación en
 * Vivo). Direct deep link from pg_ai_hub.php's Section C, validates its
 * own session like strategy.php.
 *
 * Diagnostic-only: does not change what the live widget/WhatsApp
 * dispatch through (still ProxyBridge — see core/ProxyBridge.php).
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';
require __DIR__ . '/core/dev_bypass.php';

if (!lly_is_authenticated()) {
    header('Location: index.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$lly_csrf = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — AURA M2M Connection Diagnostic" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · AURA Diagnostic</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <script src="assets/js/theme-init.js"></script>
</head>

<body data-active-lang="en">

  <!-- ═══════════════════════════════════════════════════════════════
       TOPBAR
  ═══════════════════════════════════════════════════════════════ -->
  <header class="topbar" role="banner">
    <div class="container">
      <div class="topbar-inner">

        <a href="pg_ai_hub.php" class="topbar-logo" aria-label="Lover Lips Yachts — PG-AI Hub">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>AURA Diagnostic · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="pg_ai_hub.php" class="topbar-back-btn">
            <span data-lang="en">⬅️ Back to PG-AI Hub</span>
            <span data-lang="es">⬅️ Regresar al Centro PG-AI</span>
          </a>
          <button class="theme-toggle" id="theme-toggle" aria-label="Switch to Night Mode" aria-pressed="false">
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
            </svg>
          </button>
          <div class="lang-toggle" role="group" aria-label="Language / Idioma">
            <button type="button" class="lang-btn active" id="btn-en" aria-pressed="true">EN</button>
            <button type="button" class="lang-btn"        id="btn-es" aria-pressed="false">ES</button>
          </div>
        </div>

      </div>
    </div>
  </header>

  <main>

    <!-- ═══════════════════════════════════════════════════════════════
         HEADER
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="aura-diag-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">AURA M2M · Connection Diagnostic</span>
          <span data-lang="es">AURA M2M · Diagnóstico de Conexión</span>
        </p>
        <h1 class="section-title" id="aura-diag-title">
          <span data-lang="en">AURA Satellite <em>Diagnostic</em></span>
          <span data-lang="es">Diagnóstico del <em>Satélite AURA</em></span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          Run a real handshake against the AURA Linux server (LAN primary, WAN fallback) and inspect latency, tokens, and engine/model detected — diagnostic-only, does not change what the live site dispatches through.
        </p>
        <p class="section-subtitle" data-lang="es">
          Ejecuta un handshake real contra el servidor Linux AURA (LAN primario, respaldo WAN) e inspecciona latencia, tokens, y el motor/modelo detectado — solo diagnóstico, no cambia por dónde despacha el sitio en vivo.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         HANDSHAKE + TELEMETRY
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="aura-diag-section" aria-labelledby="aura-diag-handshake-title">
      <div class="container">
        <h2 class="section-title" id="aura-diag-handshake-title">
          <span data-lang="en">📡 Handshake</span>
          <span data-lang="es">📡 Handshake</span>
        </h2>

        <div class="ephemeral-panel">
          <form id="aura-handshake-form" class="aura-diag-actions">
            <input type="hidden" name="csrf_token" id="aura-diag-csrf-field" value="<?= $lly_csrf ?>">
            <button type="submit" class="dash-card-btn" id="aura-handshake-btn">
              <span data-lang="en">📡 Run Handshake</span>
              <span data-lang="es">📡 Ejecutar Handshake</span>
            </button>
            <span class="aura-diag-channel-indicator" id="aura-diag-channel-indicator">
              <span data-lang="en">No test run yet.</span>
              <span data-lang="es">Aún no se ha ejecutado ninguna prueba.</span>
            </span>
          </form>

          <div class="aura-diag-telemetry" id="aura-diag-telemetry">
            <div class="aura-diag-tile">
              <p class="aura-diag-tile-label"><span data-lang="en">Status</span><span data-lang="es">Estado</span></p>
              <p class="aura-diag-tile-value" id="aura-diag-status">—</p>
            </div>
            <div class="aura-diag-tile">
              <p class="aura-diag-tile-label"><span data-lang="en">Network Latency</span><span data-lang="es">Latencia de Red</span></p>
              <p class="aura-diag-tile-value" id="aura-diag-net-latency">—</p>
            </div>
            <div class="aura-diag-tile">
              <p class="aura-diag-tile-label"><span data-lang="en">AURA-Reported Latency</span><span data-lang="es">Latencia Reportada por AURA</span></p>
              <p class="aura-diag-tile-value" id="aura-diag-reported-latency">—</p>
            </div>
            <div class="aura-diag-tile">
              <p class="aura-diag-tile-label"><span data-lang="en">Tokens Used / Remaining</span><span data-lang="es">Tokens Usados / Restantes</span></p>
              <p class="aura-diag-tile-value" id="aura-diag-tokens">—</p>
            </div>
            <div class="aura-diag-tile">
              <p class="aura-diag-tile-label"><span data-lang="en">Engine / Model</span><span data-lang="es">Motor / Modelo</span></p>
              <p class="aura-diag-tile-value" id="aura-diag-engine">—</p>
            </div>
            <div class="aura-diag-tile">
              <p class="aura-diag-tile-label"><span data-lang="en">Tenant Confirmed</span><span data-lang="es">Tenant Confirmado</span></p>
              <p class="aura-diag-tile-value" id="aura-diag-tenant">—</p>
            </div>
          </div>

          <p id="aura-diag-error" class="ephemeral-feedback" role="status" aria-live="polite"></p>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         PROMPT SANDBOX
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" id="aura-diag-sandbox-section" aria-labelledby="aura-diag-sandbox-title">
      <div class="container">
        <h2 class="section-title" id="aura-diag-sandbox-title">
          <span data-lang="en">🧪 Prompt Sandbox</span>
          <span data-lang="es">🧪 Sandbox de Prompts</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Send a custom prompt straight to AURA and see the raw processed reply — the API key never leaves the server; your browser only talks to api/aura_diagnostic.php.</span>
          <span data-lang="es">Envía un prompt personalizado directo a AURA y observa la respuesta procesada cruda — la llave de API nunca sale del servidor; tu navegador solo habla con api/aura_diagnostic.php.</span>
        </p>

        <div class="ephemeral-panel">
          <form id="aura-sandbox-form" class="aura-diag-sandbox-form">
            <textarea id="aura-sandbox-prompt" rows="3" maxlength="2000" placeholder="Type a test prompt…"></textarea>
            <button type="submit" class="dash-card-btn" id="aura-sandbox-send">
              <span data-lang="en">🧪 Send to AURA</span>
              <span data-lang="es">🧪 Enviar a AURA</span>
            </button>
          </form>
          <div class="aura-diag-sandbox-reply" id="aura-diag-sandbox-reply"></div>
        </div>
      </div>
    </section>

  </main>

  <!-- ═══════════════════════════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════════════════════════ -->
  <footer class="footer" role="contentinfo">
    <div class="container">
      <div class="footer-logo">
        <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
        <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
      </div>
      <p>
        <strong>Lover Lips Yachts</strong> &nbsp;·&nbsp;
        <span data-lang="en">AURA Diagnostic · Confidential · Owner Only</span>
        <span data-lang="es">Diagnóstico AURA · Confidencial · Solo Propietario</span>
      </p>
    </div>
  </footer>

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>

  <script>
  (function () {
    var csrfField = document.getElementById('aura-diag-csrf-field');

    function post(action, extraFields) {
      var body = new URLSearchParams();
      body.set('action', action);
      body.set('csrf_token', csrfField ? csrfField.value : '');
      if (extraFields) {
        Object.keys(extraFields).forEach(function (key) { body.set(key, extraFields[key]); });
      }
      return fetch('api/aura_diagnostic.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
      }).then(function (res) {
        return res.json().then(function (data) {
          if (csrfField && data.csrf_token) { csrfField.value = data.csrf_token; }
          return data;
        });
      });
    }

    function renderTelemetry(result) {
      var statusEl   = document.getElementById('aura-diag-status');
      var netEl      = document.getElementById('aura-diag-net-latency');
      var reportedEl = document.getElementById('aura-diag-reported-latency');
      var tokensEl   = document.getElementById('aura-diag-tokens');
      var engineEl   = document.getElementById('aura-diag-engine');
      var tenantEl   = document.getElementById('aura-diag-tenant');
      var channelEl  = document.getElementById('aura-diag-channel-indicator');
      var errorEl    = document.getElementById('aura-diag-error');

      statusEl.textContent = result.success ? ('✅ ' + result.httpCode + ' OK') : ('❌ ' + (result.httpCode || 'ERR'));
      netEl.textContent = result.networkLatencyMs != null ? result.networkLatencyMs + ' ms' : '—';
      reportedEl.textContent = result.reportedLatencyMs != null ? result.reportedLatencyMs + ' ms' : '—';
      tokensEl.textContent = (result.tokensUsed != null || result.tokensRemaining != null)
        ? ((result.tokensUsed != null ? result.tokensUsed : '—') + ' / ' + (result.tokensRemaining != null ? result.tokensRemaining : '—'))
        : '—';
      engineEl.textContent = (result.engine || result.model) ? ((result.engine || '—') + ' / ' + (result.model || '—')) : '—';
      tenantEl.textContent = result.tenantName || '—';

      var channelLabels = { lan: '🏠 LAN (Primary)', wan: '☁️ WAN (Fallback)', wan_ip: '📍 WAN (Direct IP)', none: '⚠️ Not configured' };
      channelEl.textContent = channelLabels[result.channelUsed] || result.channelUsed || '—';

      var trail = [];
      if (result.lanErrorMessage) { trail.push('LAN failed (' + result.lanErrorMessage + ')'); }
      if (result.wanErrorMessage) { trail.push('WAN domain failed (' + result.wanErrorMessage + ') → tried direct IP'); }
      errorEl.textContent = trail.length
        ? (trail.join(' → ') + '. ' + (result.errorMessage || ''))
        : (result.errorMessage || '');
    }

    var handshakeForm = document.getElementById('aura-handshake-form');
    var handshakeBtn  = document.getElementById('aura-handshake-btn');
    if (handshakeForm) {
      handshakeForm.addEventListener('submit', function (e) {
        e.preventDefault();
        handshakeBtn.disabled = true;
        post('handshake').then(function (data) {
          if (data.status === 'success') { renderTelemetry(data.result); }
          else { document.getElementById('aura-diag-error').textContent = data.message || 'Handshake failed.'; }
        }).finally(function () { handshakeBtn.disabled = false; });
      });
    }

    var sandboxForm  = document.getElementById('aura-sandbox-form');
    var sandboxBtn   = document.getElementById('aura-sandbox-send');
    var sandboxReply = document.getElementById('aura-diag-sandbox-reply');
    if (sandboxForm) {
      sandboxForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var prompt = document.getElementById('aura-sandbox-prompt').value.trim();
        if (prompt === '') return;
        sandboxBtn.disabled = true;
        sandboxReply.textContent = '…';
        post('prompt', { prompt: prompt }).then(function (data) {
          if (data.status === 'success') {
            renderTelemetry(data.result);
            sandboxReply.textContent = data.result.response || data.result.errorMessage || '(no response field)';
          } else {
            sandboxReply.textContent = data.message || 'Request failed.';
          }
        }).finally(function () { sandboxBtn.disabled = false; });
      });
    }
  }());
  </script>

</body>
</html>
