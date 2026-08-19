<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — ai-showcase.php
 * Executive showcase / infographic for the owners (Lester, Fabiola,
 * Gladys) — presents the PG-AI Pink Glove AI system (chatbot, lead
 * capture, ephemeral quotes, booking calendar) as a single visual story.
 * Purely presentational — no data reads/writes, no forms. Direct deep
 * link (not an include), validates its own session like every other
 * Cockpit page.
 *
 * Content note: the AI concierge speaks in first person AS Lester
 * (core/prompts/pg_ai_lester_master.md, section 1 — "hablando en primera
 * persona en nombre de Lester Keizer"), not as a separate named persona —
 * this page's copy reflects that real identity, not an invented one.
 *
 * Links to the other Cockpit pages are relative (chat-lab.php, not
 * https://loverlipsyachts.com/cockpit/chat-lab.php) so this page works
 * identically on XAMPP and production — see core/PgAiActionProcessor.php
 * ::publicUrl()'s docblock for the hardcoded-domain bug this deliberately
 * avoids repeating.
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';
require __DIR__ . '/core/dev_bypass.php';

if (!lly_is_authenticated()) {
    header('Location: index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Concierge IA Lover Lips Executive Showcase" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Concierge IA Showcase</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <script src="assets/js/theme-init.js"></script>
</head>

<body class="showcase-page" data-active-lang="en">

  <!-- ═══════════════════════════════════════════════════════════════
       MICRO-HEADER — wayfinding only, deliberately minimal so the
       hero below reads as a flyer, not a dashboard utility screen
  ═══════════════════════════════════════════════════════════════ -->
  <header class="showcase-microheader">
    <a href="pg_ai_hub.php" class="showcase-microheader-back">
      <span data-lang="en">⬅️ Back to Hub</span>
      <span data-lang="es">⬅️ Regresar al Hub</span>
    </a>
    <div class="showcase-microheader-actions">
      <button class="theme-toggle" id="theme-toggle" aria-label="Switch to Night Mode" aria-pressed="false">
        <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
        </svg>
        <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
        </svg>
      </button>
      <div class="lang-toggle showcase-microheader-lang" role="group" aria-label="Language / Idioma">
        <button type="button" class="lang-btn active" id="btn-en" aria-pressed="true">EN</button>
        <button type="button" class="lang-btn"        id="btn-es" aria-pressed="false">ES</button>
      </div>
    </div>
  </header>

  <main class="showcase-main">

    <!-- ═══════════════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════════════ -->
    <section class="showcase-hero">
      <div class="showcase-hero-glow showcase-hero-glow--pink" aria-hidden="true"></div>
      <div class="showcase-hero-glow showcase-hero-glow--gold" aria-hidden="true"></div>

      <img class="showcase-hero-logo reveal" src="assets/img/logo.png" alt="Lover Lips Yachts" />

      <p class="showcase-live-badge reveal" style="--reveal-delay:.1s">
        <span class="showcase-live-dot" aria-hidden="true"></span>
        <span data-lang="en">Live — Direct High-Speed Engine + AURA Satellite Core</span>
        <span data-lang="es">En Vivo — Motor Directo de Alta Velocidad + Núcleo Satélite AURA</span>
      </p>

      <h1 class="showcase-hero-title reveal" style="--reveal-delay:.2s">
        <span data-lang="en">Meet the Concierge IA Lover Lips —<br /><em>Your 24/7 Digital Luxury Host</em></span>
        <span data-lang="es">Conoce al Concierge IA Lover Lips —<br /><em>Tu Anfitrión Digital de Lujo, 24/7</em></span>
      </h1>

      <p class="showcase-hero-subtitle reveal" style="--reveal-delay:.3s">
        <span data-lang="en">PG-AI answers every guest in Lester's own voice — the same Pink Glove Experience™ hospitality, instantly, in whichever language they write in, at any hour.</span>
        <span data-lang="es">PG-AI responde a cada huésped con la voz de Lester — la misma hospitalidad Pink Glove Experience™, al instante, en el idioma que escriban, a cualquier hora.</span>
      </p>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         METRICS STRIP
    ═══════════════════════════════════════════════════════════════ -->
    <section class="showcase-metrics">
      <div class="showcase-metric-card reveal" style="--reveal-delay:0s">
        <p class="showcase-metric-value">⚡ ~1.3s</p>
        <p class="showcase-metric-label"><span data-lang="en">Response Time</span><span data-lang="es">Tiempo de Respuesta</span></p>
      </div>
      <div class="showcase-metric-card reveal" style="--reveal-delay:.1s">
        <p class="showcase-metric-value">🌐 100%</p>
        <p class="showcase-metric-label"><span data-lang="en">Native Bilingual (EN/ES)</span><span data-lang="es">Bilingüe Nativo (EN/ES)</span></p>
      </div>
      <div class="showcase-metric-card reveal" style="--reveal-delay:.2s">
        <p class="showcase-metric-value">🔒 24/7</p>
        <p class="showcase-metric-label"><span data-lang="en">Zero-Friction Lead Capture</span><span data-lang="es">Captura de Leads Sin Fricción</span></p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         TIMELINE — 4-step conversion story
    ═══════════════════════════════════════════════════════════════ -->
    <section class="showcase-timeline-section">
      <h2 class="showcase-section-title reveal">
        <span data-lang="en">From "Hello" to Booked — In One Conversation</span>
        <span data-lang="es">De "Hola" a Reservado — En Una Sola Conversación</span>
      </h2>

      <div class="showcase-timeline">
        <div class="showcase-timeline-line" aria-hidden="true"></div>

        <div class="showcase-timeline-step reveal">
          <div class="showcase-timeline-node">1</div>
          <div class="showcase-timeline-card">
            <p class="showcase-timeline-icon">🤍</p>
            <h3><span data-lang="en">White-Glove Welcome</span><span data-lang="es">Bienvenida de Guante Blanco</span></h3>
            <p>
              <span data-lang="en">Instant bilingual replies (EN/ES, ~1.3s) with the warmth of real ultra-luxury hospitality — never a robotic script.</span>
              <span data-lang="es">Respuestas bilingües instantáneas (EN/ES, ~1.3s) con la calidez de la hospitalidad de ultra-lujo real — nunca un guion robótico.</span>
            </p>
          </div>
        </div>

        <div class="showcase-timeline-step reveal">
          <div class="showcase-timeline-node">2</div>
          <div class="showcase-timeline-card">
            <p class="showcase-timeline-icon">📝</p>
            <h3><span data-lang="en">Organic Lead Extraction</span><span data-lang="es">Extracción Orgánica de Leads</span></h3>
            <p>
              <span data-lang="en">No forms, ever. Name, WhatsApp, date, PAX, and destination are captured straight out of natural conversation.</span>
              <span data-lang="es">Sin formularios, nunca. Nombre, WhatsApp, fecha, PAX y destino se capturan directo de la conversación natural.</span>
            </p>
          </div>
        </div>

        <div class="showcase-timeline-step reveal">
          <div class="showcase-timeline-node">3</div>
          <div class="showcase-timeline-card">
            <p class="showcase-timeline-icon">🔗</p>
            <h3><span data-lang="en">Instant VIP Quote</span><span data-lang="es">Cotización VIP Instantánea</span></h3>
            <p>
              <span data-lang="en">A real quote link, generated on the spot — self-destructing after a set number of views, for exclusivity and security.</span>
              <span data-lang="es">Un enlace de cotización real, generado al instante — se autodestruye tras un número fijo de vistas, por exclusividad y seguridad.</span>
            </p>
          </div>
        </div>

        <div class="showcase-timeline-step reveal">
          <div class="showcase-timeline-node">4</div>
          <div class="showcase-timeline-card">
            <p class="showcase-timeline-icon">📅</p>
            <h3><span data-lang="en">Airbnb-Style Live Calendar</span><span data-lang="es">Calendario en Vivo Estilo Airbnb</span></h3>
            <p>
              <span data-lang="en">The lead lands on the Agenda in real time — 🟡 Interested next to 🟢 Confirmed, side by side on the same calendar.</span>
              <span data-lang="es">El lead aparece en la Agenda en tiempo real — 🟡 Interesados junto a 🟢 Confirmados, lado a lado en el mismo calendario.</span>
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         COMMAND CENTER — direct action buttons
    ═══════════════════════════════════════════════════════════════ -->
    <section class="showcase-cta-section">
      <h2 class="showcase-section-title reveal">
        <span data-lang="en">See It Live</span>
        <span data-lang="es">Véelo en Vivo</span>
      </h2>

      <div class="showcase-cta-grid">
        <a href="chat-lab.php" class="showcase-cta-card reveal">
          <p class="showcase-cta-icon">🛥️</p>
          <h3><span data-lang="en">Test Live AI Concierge</span><span data-lang="es">Probar el Concierge IA en Vivo</span></h3>
          <p class="showcase-cta-arrow">→</p>
        </a>
        <a href="pg_ai_hub.php" class="showcase-cta-card reveal" style="--reveal-delay:.1s">
          <p class="showcase-cta-icon">📋</p>
          <h3><span data-lang="en">Open Live Leads CRM</span><span data-lang="es">Abrir CRM de Leads en Vivo</span></h3>
          <p class="showcase-cta-arrow">→</p>
        </a>
        <a href="agenda.php" class="showcase-cta-card reveal" style="--reveal-delay:.2s">
          <p class="showcase-cta-icon">📅</p>
          <h3><span data-lang="en">Open Booking Calendar</span><span data-lang="es">Abrir Calendario de Reservas</span></h3>
          <p class="showcase-cta-arrow">→</p>
        </a>
      </div>
    </section>

  </main>

  <footer class="showcase-footer">
    <p>
      <strong>Lover Lips Yachts</strong> &nbsp;·&nbsp;
      <span data-lang="en">PG-AI Pink Glove AI · Confidential · Owner Only</span>
      <span data-lang="es">PG-AI Pink Glove AI · Confidencial · Solo Propietario</span>
    </p>
  </footer>

  <script src="assets/js/main.js" defer></script>

  <!-- Page-specific inline script — scroll-reveal only, same "independent
       and modular" convention as agenda.php/chat-lab.php (this animation
       has no reason to live in the shared main.js bundle). -->
  <script>
  (function () {
    var items = document.querySelectorAll('.reveal');
    if (!items.length) return;

    if (!('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('reveal--visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal--visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    items.forEach(function (el) { observer.observe(el); });
  }());
  </script>

</body>
</html>
