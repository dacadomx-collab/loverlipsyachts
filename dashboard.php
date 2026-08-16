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
    header('Location: index.php');
    exit;
}


/**
 * Static content arrays — Clear Luxury dashboard summary.
 * Same "no DB table yet" pattern used across reportes.php / alianzas.php:
 * plain PHP arrays, hand-edited, no SQL. Keys double as anchors that map
 * a payment batch to the exact reports it covers.
 */
$lly_reportes = [
    'i' => [
        'date_en' => 'July 1, 2026', 'date_es' => '1 de Julio, 2026',
        'title_en' => 'Edit Your Own Book Page', 'title_es' => 'Edita Tu Propia Página del Libro',
        'benefit_en' => 'You can now edit your book\'s text and cover yourself, anytime, with no developer needed.',
        'benefit_es' => 'Ahora puedes editar el texto y la portada de tu libro tú mismo, cuando quieras, sin ayuda técnica.',
    ],
    'h' => [
        'date_en' => 'June 29, 2026', 'date_es' => '29 de Junio, 2026',
        'title_en' => 'Book Menu Connected Live', 'title_es' => 'Menú del Libro Conectado en Vivo',
        'benefit_en' => 'Connected your book\'s language button to the live site and added it to the main menu.',
        'benefit_es' => 'Conectamos el botón de idioma de tu libro al sitio en vivo y lo añadimos al menú principal.',
    ],
    'g' => [
        'date_en' => 'June 27, 2026', 'date_es' => '27 de Junio, 2026',
        'title_en' => 'Faster Sunseeker 52 Photos', 'title_es' => 'Fotos del Sunseeker 52 Más Rápidas',
        'benefit_en' => 'Optimized 51 photos and 2 videos for the Sunseeker 52, so the page loads much faster.',
        'benefit_es' => 'Optimizamos 51 fotos y 2 videos del Sunseeker 52, logrando que la página cargue mucho más rápido.',
    ],
    'f' => [
        'date_en' => 'June 20, 2026', 'date_es' => '20 de Junio, 2026',
        'title_en' => 'New Book Landing Page', 'title_es' => 'Nueva Página del Libro',
        'benefit_en' => 'Built a beautiful landing page to promote your book and attract new customers for free.',
        'benefit_es' => 'Creamos una hermosa página para promocionar tu libro y atraer nuevos clientes sin gastar en publicidad.',
    ],
    'd' => [
        'date_en' => 'June 20, 2026', 'date_es' => '20 de Junio, 2026',
        'title_en' => 'Higher Google Score', 'title_es' => 'Mejor Calificación en Google',
        'benefit_en' => 'Improved your website\'s Google score to 91/100, helping more people find your yachts online.',
        'benefit_es' => 'Mejoramos la calificación de tu sitio en Google a 91/100, ayudando a que más gente encuentre tus yates.',
    ],
    'c' => [
        'date_en' => 'June 5, 2026', 'date_es' => '5 de Junio, 2026',
        'title_en' => 'Cleaner Photo Galleries', 'title_es' => 'Galerías de Fotos Más Limpias',
        'benefit_en' => 'Removed ugly technical text over your photos, making every gallery look clean and professional.',
        'benefit_es' => 'Eliminamos texto técnico feo que aparecía sobre tus fotos, dejando las galerías limpias y profesionales.',
    ],
    'b' => [
        'date_en' => 'June 5, 2026', 'date_es' => '5 de Junio, 2026',
        'title_en' => 'Two Booking Pages Rescued', 'title_es' => 'Dos Páginas de Reservas Rescatadas',
        'benefit_en' => 'Rescued two broken booking pages so guests can find and book your yachts again.',
        'benefit_es' => 'Rescatamos dos páginas de reservas caídas para que los clientes puedan encontrar y reservar tus yates de nuevo.',
    ],
    'a' => [
        'date_en' => 'June 5, 2026', 'date_es' => '5 de Junio, 2026',
        'title_en' => 'Instant, Polished Homepage', 'title_es' => 'Página de Inicio Instantánea y Pulida',
        'benefit_en' => 'Fixed a visual glitch so your homepage always looks polished from the very first second.',
        'benefit_es' => 'Corregimos un destello visual para que tu página de inicio siempre luzca perfecta desde el primer segundo.',
    ],
];

$lly_reportes_recientes = ['i', 'h', 'g', 'f', 'd'];

$lly_pagos = [
    [
        'label_en' => 'Batch 2 — Book Launch, Fleet Pipeline &amp; CMS',
        'label_es' => 'Lote 2 — Libro, Pipeline de Flota y CMS',
        'date_en' => 'Settled: June 29, 2026', 'date_es' => 'Liquidado: 29 de Junio, 2026',
        'cash' => 1900, 'trade' => 1900,
        'reporte_ids' => ['d', 'f', 'g', 'h', 'i'],
    ],
    [
        'label_en' => 'Batch 1 — Emergency Recovery',
        'label_es' => 'Lote 1 — Rescate de Emergencia',
        'date_en' => 'Settled: June 20, 2026', 'date_es' => 'Liquidado: 20 de Junio, 2026',
        'cash' => 1000, 'trade' => 1000,
        'reporte_ids' => ['a', 'b', 'c'],
    ],
];

$lly_pagos_cash_total = array_sum(array_column($lly_pagos, 'cash'));
$lly_pagos_trade_total = array_sum(array_column($lly_pagos, 'trade'));

?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Owner Control Center" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Owner Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
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
         2. THE 4 MAIN CARDS (ARF-GRID) — PG-AI Hub · Book · Work Report · Payments
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section access-grid-section" id="access-grid" aria-label="Your 4 Main Cards">
      <div class="container">
        <div class="dash-grid">

          <!-- ── CARD 1 — 🎯 PG-AI LEADS & EPHEMERAL QUOTES ──────────── -->
          <article class="dash-card dash-card--pink">
            <div class="dash-card-icon">🎯</div>
            <h2 class="dash-card-title">
              <span data-lang="en">PG-AI Leads &amp; Ephemeral Quotes</span>
              <span data-lang="es">PG-AI · Leads y Cotizaciones Efímeras</span>
            </h2>
            <p class="dash-card-body">
              <span data-lang="en">Central hub for live WhatsApp &amp; Website leads, PG-AI chatbot configuration, official system prompt, and self-destructing quote templates.</span>
              <span data-lang="es">Centro de control para leads en vivo de WhatsApp y el sitio web, configuración del chatbot PG-AI, prompt oficial del sistema, y plantillas de cotización autodestruibles.</span>
            </p>
            <a class="dash-card-btn" href="pg_ai_hub.php">
              <span data-lang="en">🎯 Open PG-AI Hub</span>
              <span data-lang="es">🎯 Abrir Centro PG-AI</span>
            </a>
            <a class="dash-card-btn dash-card-btn--secondary" href="pg_ai_config.php">
              <span data-lang="en">⚙️ Open PG-AI Config</span>
              <span data-lang="es">⚙️ Abrir Configuración PG-AI</span>
            </a>
          </article>

          <!-- ── CARD 2 — 📘 THE BOOK ─────────────────────────────────── -->
          <article class="dash-card dash-card--gold">
            <div class="dash-card-icon">📘</div>
            <h2 class="dash-card-title">
              <span data-lang="en">"Nine Lives. One True Love."</span>
              <span data-lang="es">"Nine Lives. One True Love."</span>
            </h2>
            <p class="dash-card-body">
              <span data-lang="en">Launches September 2, 2026. Everything about your book lives here.</span>
              <span data-lang="es">Se lanza el 2 de Septiembre, 2026. Todo sobre tu libro está aquí.</span>
            </p>
            <a class="dash-card-btn" href="book_editor.php">
              <span data-lang="en">✏️ Edit Book Page</span>
              <span data-lang="es">✏️ Editar Página del Libro</span>
            </a>
            <a class="dash-card-btn dash-card-btn--secondary" href="strategy.php">
              <span data-lang="en">📈 View Marketing Plan</span>
              <span data-lang="es">📈 Ver Plan de Marketing</span>
            </a>
          </article>

          <!-- ── CARD 3 — 📑 WORK REPORT (plain language) ────────────── -->
          <article class="dash-card dash-card--navy">
            <div class="dash-card-icon">📑</div>
            <h2 class="dash-card-title">
              <span data-lang="en">Work Report</span>
              <span data-lang="es">Informe de Trabajo</span>
            </h2>
            <div class="dash-feed">
              <?php foreach ($lly_reportes_recientes as $rid): $r = $lly_reportes[$rid]; ?>
              <div class="dash-feed-item">
                <p class="dash-feed-item-date">
                  <span data-lang="en"><?= htmlspecialchars($r['date_en'], ENT_QUOTES) ?></span>
                  <span data-lang="es"><?= htmlspecialchars($r['date_es'], ENT_QUOTES) ?></span>
                </p>
                <p class="dash-feed-item-title">
                  <span data-lang="en"><?= htmlspecialchars($r['title_en'], ENT_QUOTES) ?></span>
                  <span data-lang="es"><?= htmlspecialchars($r['title_es'], ENT_QUOTES) ?></span>
                </p>
                <p class="dash-feed-item-benefit">
                  <span data-lang="en"><?= htmlspecialchars($r['benefit_en'], ENT_QUOTES) ?></span>
                  <span data-lang="es"><?= htmlspecialchars($r['benefit_es'], ENT_QUOTES) ?></span>
                </p>
              </div>
              <?php endforeach; ?>
            </div>
            <a class="dash-card-btn dash-card-btn--secondary" href="reportes.php">
              <span data-lang="en">See All Reports</span>
              <span data-lang="es">Ver Todos los Informes</span>
            </a>
          </article>

          <!-- ── CARD 4 — 💳 PAYMENTS & AGREEMENTS (50/50 Cash / Trade) ── -->
          <article class="dash-card dash-card--pink">
            <div class="dash-card-icon">💳</div>
            <h2 class="dash-card-title">
              <span data-lang="en">Payments &amp; Agreements</span>
              <span data-lang="es">Pagos y Convenios</span>
            </h2>
            <div class="dash-pay-totals">
              <div class="dash-pay-total dash-pay-total--cash">
                <p class="dash-pay-total-label">
                  <span data-lang="en">Cash Transferred</span>
                  <span data-lang="es">Monto Transferido (Efectivo)</span>
                </p>
                <p class="dash-pay-total-amount">$<?= number_format($lly_pagos_cash_total) ?> MXN</p>
              </div>
              <div class="dash-pay-total dash-pay-total--trade">
                <p class="dash-pay-total-label">
                  <span data-lang="en">Trade Credit Balance</span>
                  <span data-lang="es">Saldo a Cuenta (Intercambio)</span>
                </p>
                <p class="dash-pay-total-amount">$<?= number_format($lly_pagos_trade_total) ?> MXN</p>
              </div>
            </div>

            <p class="dash-card-body">
              <span data-lang="en">Tap any row below to see exactly what work it paid for.</span>
              <span data-lang="es">Toca cualquier renglón para ver exactamente qué trabajo pagó.</span>
            </p>

            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th><span data-lang="en">Batch</span><span data-lang="es">Lote</span></th>
                    <th><span data-lang="en">Cash</span><span data-lang="es">Efectivo</span></th>
                    <th><span data-lang="en">Trade</span><span data-lang="es">Intercambio</span></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lly_pagos as $p): ?>
                  <tr class="dash-pay-row" tabindex="0" role="button"
                      data-report-ids="<?= htmlspecialchars(implode(',', $p['reporte_ids']), ENT_QUOTES) ?>"
                      data-batch-label-en="<?= htmlspecialchars($p['label_en'], ENT_QUOTES) ?>"
                      data-batch-label-es="<?= htmlspecialchars($p['label_es'], ENT_QUOTES) ?>">
                    <td>
                      <span data-lang="en"><?= $p['label_en'] ?></span>
                      <span data-lang="es"><?= $p['label_es'] ?></span>
                    </td>
                    <td>$<?= number_format($p['cash']) ?> MXN</td>
                    <td>$<?= number_format($p['trade']) ?> MXN</td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <a class="dash-card-btn dash-card-btn--secondary" href="alianzas.php">
              <span data-lang="en">See Full History</span>
              <span data-lang="es">Ver Historial Completo</span>
            </a>
          </article>

        </div>
      </div>
    </section>

  </main>

  <!-- ═══════════════════════════════════════════════════════════════
       REPORT DIALOG — opens from a Payments row (Card 3)
  ═══════════════════════════════════════════════════════════════ -->
  <dialog class="chapter-dialog" id="lly-report-dialog" aria-labelledby="lly-report-dialog-title">
    <div class="chapter-dialog-inner">
      <button type="button" class="chapter-dialog-close" aria-label="Close" onclick="document.getElementById('lly-report-dialog').close()">✕</button>
      <p class="chapter-dialog-eyebrow" id="lly-report-dialog-eyebrow"></p>
      <h2 class="chapter-dialog-title" id="lly-report-dialog-title"></h2>
      <div class="chapter-dialog-body" id="lly-report-dialog-body"></div>
    </div>
  </dialog>

  <script type="application/json" id="lly-reportes-data"><?= json_encode($lly_reportes, JSON_UNESCAPED_UNICODE) ?></script>

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

  <!-- ══ AI WIDGET — Cognitive Omnichannel Operator (Web Widget) ═════
       Same markup/classes as book.php/index.php (Mandamiento 10 — no new
       CSS). dashboard.php is only ever reached via index.php's
       LLY_DASHBOARD_GATEKEEPER require (never a wrapper path), so the
       relative default gateway URL in pg_ai_widget.js is correct here
       without setting window.PGAI_WIDGET_GATEWAY_URL. Lets Lester test
       the live chatbot from the same screen he already uses daily,
       instead of only from the pg_ai_hub.php Section B testbed. ────── -->
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
  <script src="assets/js/pg_ai_widget.js" defer></script>

</body>
</html>
