<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — alianzas.php
 * Account statement — purely financial. Only payments made for reports
 * already executed. No future phases or proposals live here (see
 * propuestas.php for that). Standalone page — validates its own session.
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
  <meta name="description" content="Lover Lips Yachts — Alliance Account Statement" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Alliance</title>
  <link rel="stylesheet" href="assets/css/style.css" />
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

        <a href="index.php" class="topbar-logo" aria-label="Lover Lips Yachts — Owner Dashboard">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Alliance · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
          <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Switch to Night Mode" aria-pressed="false">
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
    <section class="section section-white" aria-labelledby="alliance-title">
      <div class="container">

        <nav class="book-feature-editorial-nav strategy-back-nav" aria-label="Back to dashboard">
          <a href="index.php">
            <span data-lang="en">← Back to Dashboard</span>
            <span data-lang="es">← Volver al Panel</span>
          </a>
        </nav>

        <p class="section-label">
          <span data-lang="en">Account Statement</span>
          <span data-lang="es">Estado de Cuenta</span>
        </p>
        <h1 class="section-title" id="alliance-title">
          <span data-lang="en">Win-Win <em>Alliance</em> — Account Statement</span>
          <span data-lang="es">Alianza <em>Ganar-Ganar</em> — Estado de Cuenta</span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          This page shows only the payments made for reports already executed, split under our technology-partnership model: 50% Cash / 50% Trade Credits. Upcoming development phases and proposals are not included here — review those on the Proposals page.
        </p>
        <p class="section-subtitle" data-lang="es">
          Esta página muestra únicamente los pagos realizados por los reportes ya ejecutados, bajo nuestro modelo de sociedad tecnológica: 50% Efectivo / 50% Créditos de Intercambio. Las fases de desarrollo futuras y propuestas no se incluyen aquí — revísalas en la página de Propuestas.
        </p>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         VAULT + FINANCIAL BREAKDOWN
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-proposal" aria-label="Financial Statement">
      <div class="container">

        <!-- ══ WIN-WIN YACHT CREDIT VAULT ══════════════════════════════ -->
        <aside class="proposal-vault proposal-vault--pinned" aria-label="Yacht Credit Vault">
          <div class="proposal-vault-hero">
            <div class="proposal-vault-icon">⚓</div>
            <div>
              <p class="proposal-vault-label">
                <span data-lang="en">Win-Win Yacht Credit Vault</span>
                <span data-lang="es">Bóveda de Crédito Náutico</span>
              </p>
              <p class="proposal-vault-total">$2,900 <span class="proposal-vault-currency">MXN</span></p>
            </div>
          </div>
          <div class="proposal-vault-divider"></div>
          <ul class="proposal-vault-breakdown">
            <li>
              <span data-lang="en"><strong>Batch 1</strong> Recovery</span>
              <span data-lang="es"><strong>Lote 1</strong> Rescate</span>
              <span>$1,000 MXN</span>
            </li>
            <li>
              <span data-lang="en"><strong>Batch 2</strong> Optimization + CMS</span>
              <span data-lang="es"><strong>Lote 2</strong> Optimización + CMS</span>
              <span>$1,900 MXN</span>
            </li>
          </ul>
          <p class="proposal-vault-note">
            <span data-lang="en">This balance accumulates as secured credit redeemable toward future charter experiences on the Lover Lips Yachts fleet.</span>
            <span data-lang="es">Este saldo se acumula como crédito garantizado canjeable en futuras experiencias de chárter en la flota de Lover Lips Yachts.</span>
          </p>
        </aside>

        <!-- ══ BATCH 2 — newest first (settled June 29, 2026) ═════════ -->
        <div class="proposal-phase">

          <div class="proposal-phase-header proposal-phase-header--done">
            <div class="proposal-phase-num">2</div>
            <div class="proposal-phase-meta">
              <p class="proposal-phase-tag" data-lang="en">Batch 2 · Book Launch &amp; Fleet Pipeline Optimization</p>
              <p class="proposal-phase-tag" data-lang="es">Lote 2 · Lanzamiento del Libro y Optimización del Pipeline de Flota</p>
              <h3 class="proposal-phase-title" data-lang="en">Settled: June 29, 2026</h3>
              <h3 class="proposal-phase-title" data-lang="es">Liquidado: 29 de Junio, 2026</h3>
            </div>
            <span class="proposal-status-badge proposal-status-badge--done">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span data-lang="en">Account Settled</span>
              <span data-lang="es">Saldo Conciliado</span>
            </span>
          </div>

          <p class="proposal-phase-desc" data-lang="en">
            Covers Reports D, F, G, H and I — see the technical write-up for each on the <a href="reportes.php">Reports page</a>.
          </p>
          <p class="proposal-phase-desc" data-lang="es">
            Cubre los Informes D, F, G, H e I — consulta el detalle técnico de cada uno en la <a href="reportes.php">página de Reportes</a>.
          </p>

          <div class="table-wrap">
            <table class="data-table proposal-finance-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Concept</span><span data-lang="es">Concepto</span></th>
                  <th><span data-lang="en">Investment</span><span data-lang="es">Inversión</span></th>
                  <th><span data-lang="en">Cash Payment (50%)</span><span data-lang="es">Pago en Efectivo (50%)</span></th>
                  <th><span data-lang="en">Trade Exchange (50%)</span><span data-lang="es">Intercambio Comercial (50%)</span></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <strong>
                      <span data-lang="en">Batch 2 — Book Launch, Fleet Pipeline &amp; CMS (Reports D, F, G, H, I)</span>
                      <span data-lang="es">Lote 2 — Libro, Pipeline de Flota &amp; CMS (Informes D, F, G, H, I)</span>
                    </strong>
                  </td>
                  <td><span class="proposal-amount">$3,800 MXN</span></td>
                  <td><span class="proposal-cash">$1,900 MXN</span></td>
                  <td><span class="proposal-trade">$1,900 MXN</span></td>
                </tr>
              </tbody>
            </table>
          </div>

        </div><!-- /Batch 2 -->

        <!-- ══ BATCH 1 — older (settled June 20, 2026) ════════════════ -->
        <div class="proposal-phase">

          <div class="proposal-phase-header proposal-phase-header--done">
            <div class="proposal-phase-num">1</div>
            <div class="proposal-phase-meta">
              <p class="proposal-phase-tag" data-lang="en">Batch 1 · Emergency Intervention &amp; Platform Recovery</p>
              <p class="proposal-phase-tag" data-lang="es">Lote 1 · Intervención de Emergencia y Recuperación de la Plataforma</p>
              <h3 class="proposal-phase-title" data-lang="en">Settled: June 20, 2026</h3>
              <h3 class="proposal-phase-title" data-lang="es">Liquidado: 20 de Junio, 2026</h3>
            </div>
            <span class="proposal-status-badge proposal-status-badge--done">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span data-lang="en">Account Settled</span>
              <span data-lang="es">Saldo Conciliado</span>
            </span>
          </div>

          <p class="proposal-phase-desc" data-lang="en">
            Covers Reports A, B and C — see the technical write-up for each on the <a href="reportes.php">Reports page</a>.
          </p>
          <p class="proposal-phase-desc" data-lang="es">
            Cubre los Informes A, B y C — consulta el detalle técnico de cada uno en la <a href="reportes.php">página de Reportes</a>.
          </p>

          <div class="table-wrap">
            <table class="data-table proposal-finance-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Concept</span><span data-lang="es">Concepto</span></th>
                  <th><span data-lang="en">Investment</span><span data-lang="es">Inversión</span></th>
                  <th><span data-lang="en">Cash Payment (50%)</span><span data-lang="es">Pago en Efectivo (50%)</span></th>
                  <th><span data-lang="en">Trade Exchange (50%)</span><span data-lang="es">Intercambio Comercial (50%)</span></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <strong>
                      <span data-lang="en">Batch 1 — Emergency Recovery (Reports A, B, C)</span>
                      <span data-lang="es">Lote 1 — Rescate de Emergencia (Informes A, B, C)</span>
                    </strong>
                  </td>
                  <td><span class="proposal-amount">$2,000 MXN</span></td>
                  <td><span class="proposal-cash">$1,000 MXN</span></td>
                  <td><span class="proposal-trade">$1,000 MXN</span></td>
                </tr>
              </tbody>
            </table>
          </div>

        </div><!-- /Batch 1 -->

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
    </div>
  </footer>

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js" defer></script>

</body>
</html>
