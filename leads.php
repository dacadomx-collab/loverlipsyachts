<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — leads.php
 * Concierge IA Lover Lips — dedicated Live Leads CRM. Was Section A of
 * pg_ai_hub.php (2026-08-18 directive — moved to its own page so leads
 * management isn't sharing a screen with templates/config). Direct deep
 * link (not an include), validates its own session like every other
 * Cockpit page.
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
  <meta name="description" content="Lover Lips Yachts — Concierge IA Lover Lips Live Leads" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Concierge IA Leads</title>
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

        <a href="dashboard.php" class="topbar-logo" aria-label="Lover Lips Yachts — Owner Dashboard">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Concierge IA Lover Lips · Leads</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="pg_ai_hub.php" class="topbar-back-btn">
            <span data-lang="en">⬅️ Back to Hub</span>
            <span data-lang="es">⬅️ Regresar al Hub</span>
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
    <section class="section section-white" aria-labelledby="leads-page-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Concierge IA Lover Lips</span>
          <span data-lang="es">Concierge IA Lover Lips</span>
        </p>
        <h1 class="section-title" id="leads-page-title">
          <span data-lang="en">💬 Live Leads</span>
          <span data-lang="es">💬 Leads en Vivo</span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          Every conversation captured from the Website Widget and WhatsApp Business, in one unified thread per contact.
        </p>
        <p class="section-subtitle" data-lang="es">
          Cada conversación capturada desde el Widget Web y WhatsApp Business, en un solo hilo unificado por contacto.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         LEADS TABLE + FILTERS
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="leads-section" aria-labelledby="leads-page-title">
      <div class="container">
        <input type="hidden" id="leads-csrf-field" value="<?= $lly_csrf ?>">

        <div class="leads-filters">
          <input type="search" id="leads-search-input" class="leads-filter-input leads-filter-input--search"
                 placeholder="Search name, phone, email…" data-placeholder-en="Search name, phone, email…" data-placeholder-es="Buscar nombre, teléfono, correo…" />
          <label class="leads-filter-label">
            <span data-lang="en">From</span><span data-lang="es">Desde</span>
            <input type="date" id="leads-date-from" class="leads-filter-input" />
          </label>
          <label class="leads-filter-label">
            <span data-lang="en">To</span><span data-lang="es">Hasta</span>
            <input type="date" id="leads-date-to" class="leads-filter-input" />
          </label>
          <button type="button" class="dash-card-btn dash-card-btn--secondary" id="leads-filter-clear">
            <span data-lang="en">Clear</span><span data-lang="es">Limpiar</span>
          </button>
        </div>

        <div class="ephemeral-panel">
          <div class="table-wrap">
            <table class="data-table" id="leads-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Client Name</span><span data-lang="es">Nombre del Cliente</span></th>
                  <th><span data-lang="en">Date / Time</span><span data-lang="es">Fecha / Hora</span></th>
                  <th><span data-lang="en">Phone / WhatsApp</span><span data-lang="es">Teléfono/WhatsApp</span></th>
                  <th>Email</th>
                  <th><span data-lang="en">Status</span><span data-lang="es">Estado</span></th>
                  <th><span data-lang="en">Actions</span><span data-lang="es">Acciones</span></th>
                </tr>
              </thead>
              <tbody id="leads-tbody">
                <tr><td colspan="6"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         LEAD DETAIL DIALOG — opens from a row's "👁️ Ver Resumen y Charla"
    ═══════════════════════════════════════════════════════════════ -->
    <dialog class="chapter-dialog" id="lead-detail-dialog" aria-labelledby="lead-detail-dialog-title">
      <div class="chapter-dialog-inner">
        <button type="button" class="chapter-dialog-close" id="lead-detail-close" aria-label="Close">✕</button>
        <p class="chapter-dialog-eyebrow" id="lead-detail-eyebrow"></p>
        <h2 class="chapter-dialog-title" id="lead-detail-dialog-title"></h2>
        <div class="chapter-dialog-body">
          <h3 class="lead-detail-block-title">
            <span data-lang="en">📋 Executive Summary</span><span data-lang="es">📋 Resumen Ejecutivo</span>
          </h3>
          <p id="lead-detail-summary" class="lead-detail-summary"></p>

          <h3 class="lead-detail-block-title">
            <span data-lang="en">💬 Full Transcript</span><span data-lang="es">💬 Transcripción Completa</span>
          </h3>
          <div class="lly-ai-widget-messages lead-detail-transcript" id="lead-detail-transcript"></div>
        </div>
      </div>
    </dialog>

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
        <span data-lang="en">Concierge IA Lover Lips · Leads · Confidential · Owner Only</span>
        <span data-lang="es">Concierge IA Lover Lips · Leads · Confidencial · Solo Propietario</span>
      </p>
    </div>
  </footer>

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js" defer></script>

</body>
</html>
