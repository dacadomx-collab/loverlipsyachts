<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — dashboard.php
 * Quick-access menu. Standalone page — validates its own session so it
 * also works when requested directly (not only via index.php's include).
 */

require_once __DIR__ . '/api/conexion.php';
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/dev_bypass.php';

if (!lly_is_authenticated()) {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Owner Control Center" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Owner Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <!-- Blocking: applies saved theme before first paint — prevents flash -->
  <script src="assets/js/theme-init.js"></script>
</head>

<body data-active-lang="en">

  <!-- ═══════════════════════════════════════════════════════════════
       TOPBAR
  ═══════════════════════════════════════════════════════════════ -->
  <header class="topbar" role="banner">
    <div class="container">
      <div class="topbar-inner">

        <!-- Brand -->
        <a href="index.php" class="topbar-logo" aria-label="Lover Lips Yachts — Home">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Owner Control Center</span>
          </div>
        </a>

        <!-- Controls -->
        <div class="topbar-actions">

          <nav class="topbar-nav" role="navigation" aria-label="Quick Tools">
            <a class="topbar-nav-link topbar-nav-link--editor" href="book.html" target="_blank" rel="noopener noreferrer">
              <span data-lang="en">📖 Public Book Page</span><span data-lang="es">📖 Página Pública del Libro</span>
            </a>
          </nav>

          <!-- Theme toggle -->
          <button
            type="button"
            class="theme-toggle"
            id="theme-toggle"
            aria-label="Switch to Night Mode"
            aria-pressed="false"
          >
            <!-- Moon icon (shown in light mode) -->
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <!-- Sun icon (shown in dark mode) -->
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
            </svg>
          </button>

          <!-- Language toggle -->
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
         1. HERO — BRIEF WELCOME
    ═══════════════════════════════════════════════════════════════ -->
    <section class="hero hero--minimal" aria-labelledby="hero-title">
      <div class="container">
        <p class="hero-eyebrow">
          <span data-lang="en">Owner Control Center</span>
          <span data-lang="es">Centro de Control</span>
        </p>

        <h1 class="hero-title" id="hero-title">
          <span data-lang="en">Welcome, <span class="brand-name">Lester &amp; Family</span></span>
          <span data-lang="es">Bienvenido, <span class="brand-name">Lester y Familia</span></span>
        </h1>

        <p class="hero-desc" data-lang="en">
          Everything about your Lover Lips Yachts digital ecosystem lives in three places. Pick one below.
        </p>
        <p class="hero-desc" data-lang="es">
          Todo sobre tu ecosistema digital de Lover Lips Yachts vive en tres lugares. Elige uno abajo.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         2. QUICK ACCESS — 3 CARDS (ARF-GRID)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section access-grid-section" id="access-grid" aria-label="Quick Access">
      <div class="container">
        <div class="access-grid">

          <a class="access-card access-card--pink col-xs-12" href="reportes.php">
            <div class="access-card-icon">📊</div>
            <h3>
              <span data-lang="en">Reports</span>
              <span data-lang="es">Reportes</span>
            </h3>
            <p>
              <span data-lang="en">Completed technical work, newest first.</span>
              <span data-lang="es">Trabajo técnico terminado, del más reciente al más antiguo.</span>
            </p>
            <span class="access-card-arrow">→</span>
          </a>

          <a class="access-card access-card--gold col-xs-12" href="alianzas.php">
            <div class="access-card-icon">🤝</div>
            <h3>
              <span data-lang="en">Alliance</span>
              <span data-lang="es">Alianzas</span>
            </h3>
            <p>
              <span data-lang="en">Your account statement — payments made to date.</span>
              <span data-lang="es">Tu estado de cuenta — pagos realizados a la fecha.</span>
            </p>
            <span class="access-card-arrow">→</span>
          </a>

          <a class="access-card access-card--navy col-xs-12" href="propuestas.php">
            <div class="access-card-icon">🚀</div>
            <h3>
              <span data-lang="en">Proposals &amp; Future</span>
              <span data-lang="es">Propuestas y Futuro</span>
            </h3>
            <p>
              <span data-lang="en">What's next — for your review and authorization.</span>
              <span data-lang="es">Lo que sigue — para tu revisión y autorización.</span>
            </p>
            <span class="access-card-arrow">→</span>
          </a>

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
        <span data-lang="en">Owner Dashboard · Confidential</span>
        <span data-lang="es">Panel de Propietarios · Confidencial</span>
      </p>
      <p class="u-mt-xs">
        <span data-lang="en">Prepared for Lester Keizer &amp; Wife &nbsp;·&nbsp; May 30, 2026</span>
        <span data-lang="es">Preparado para Lester Keizer y Esposa &nbsp;·&nbsp; 30 de Mayo, 2026</span>
      </p>
    </div>
  </footer>

  <!-- Floating "Back to Top" — hidden until scroll > 300px (see main.js) -->
  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js" defer></script>

</body>
</html>
