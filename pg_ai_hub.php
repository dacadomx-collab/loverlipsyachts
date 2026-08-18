<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — pg_ai_hub.php
 * PG-AI Pink Glove AI — dedicated Owner control center for day-to-day
 * operation. Direct deep link from dashboard.php's Card #1 (not an
 * include), so it validates its own session like strategy.php.
 *
 * Three operational sections + a link out to configuration:
 *   A. Omnichannel leads viewer   (omnichannel_* — core/OmnichannelRepository.php)
 *   B. Live chatbot testbed       (api/public/ai_widget_gateway.php — same
 *      endpoint the real site widget uses, so this exercises production code)
 *   D. PINK LIPS quote templates + Self-Destruct Links manager
 *      (core/pgai_templates.php, core/EphemeralLinkManager.php)
 *
 * Content/connection editing (fleet catalog, master prompt, notification
 * templates, and super_admin-only AURA/WhatsApp/OpenAI credentials) moved
 * to the dedicated pg_ai_config.php screen (2026-08-04 directive —
 * decouples configuration from this operational Hub). Section letters
 * (A/B/D) kept as-is rather than relettered, to avoid churning ids/anchors
 * that may already be bookmarked.
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';
require __DIR__ . '/core/dev_bypass.php';
require __DIR__ . '/core/pgai_templates.php';

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
  <meta name="description" content="Lover Lips Yachts — PG-AI Pink Glove AI Hub" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · PG-AI Hub</title>
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
            <span>PG-AI Hub · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="pg_ai_config.php" class="topbar-back-btn">
            <span data-lang="en">⚙️ Config</span>
            <span data-lang="es">⚙️ Configuración</span>
          </a>
          <a href="dashboard.php" class="topbar-back-btn">
            <span data-lang="en">⬅️ Back to Dashboard</span>
            <span data-lang="es">⬅️ Regresar al Dashboard</span>
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
    <section class="section section-white" aria-labelledby="pgai-hub-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">PG-AI Pink Glove AI · Owner Only</span>
          <span data-lang="es">PG-AI Pink Glove AI · Solo Propietario</span>
        </p>
        <h1 class="section-title" id="pgai-hub-title">
          <span data-lang="en">PG-AI <em>Hub</em></span>
          <span data-lang="es">Centro <em>PG-AI</em></span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          Live leads, chatbot testbed, AURA/WhatsApp connection settings, self-destructing quote links, and the fleet catalog the AI is allowed to cite — everything about the PG-AI module in one place.
        </p>
        <p class="section-subtitle" data-lang="es">
          Leads en vivo, banco de pruebas del chatbot, configuración de conexión AURA/WhatsApp, enlaces de cotización autodestruibles, y el catálogo de flota que la IA puede citar — todo el módulo PG-AI en un solo lugar.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION A — OMNICHANNEL LEADS VIEWER
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="pgai-section-a" aria-labelledby="pgai-leads-title">
      <div class="container">
        <h2 class="section-title" id="pgai-leads-title">
          <span data-lang="en">💬 A. Live Leads</span>
          <span data-lang="es">💬 A. Leads en Vivo</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Every conversation captured from the Website Widget and WhatsApp Business, in one unified thread per contact.</span>
          <span data-lang="es">Cada conversación capturada desde el Widget Web y WhatsApp Business, en un solo hilo unificado por contacto.</span>
        </p>
        <input type="hidden" id="leads-csrf-field" value="<?= $lly_csrf ?>">
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
         LEAD DETAIL DIALOG — opens from a Live Leads row's
         "👁️ Ver Resumen y Charla" button (Section A)
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

    <!-- ═══════════════════════════════════════════════════════════════
         CONFIG LINK — AURA/WhatsApp credentials, Master Prompt, Fleet
         Catalog, and Notification Templates moved to pg_ai_config.php
         (2026-08-04 directive — decoupled from this operational Hub).
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="pgai-section-config-link" aria-labelledby="pgai-config-link-title">
      <div class="container">
        <h2 class="section-title" id="pgai-config-link-title">
          <span data-lang="en">⚙️ Configuration</span>
          <span data-lang="es">⚙️ Configuración</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Fleet catalog, master prompt, lead notification templates, and (super_admin only) connection credentials all live in the dedicated Config screen now.</span>
          <span data-lang="es">El catálogo de flota, el prompt maestro, las plantillas de notificación de leads, y (solo super_admin) las credenciales de conexión ahora viven en la pantalla dedicada de Configuración.</span>
        </p>
        <a class="dash-card-btn" href="pg_ai_config.php">
          <span data-lang="en">⚙️ Open PG-AI Config</span>
          <span data-lang="es">⚙️ Abrir Configuración PG-AI</span>
        </a>
      </div>
    </section>
    <!-- ═══════════════════════════════════════════════════════════════
         SECTION D — TEMPLATES & SELF-DESTRUCT LINKS
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" id="pgai-section-d" aria-labelledby="pgai-links-title">
      <div class="container">
        <h2 class="section-title" id="pgai-links-title">
          <span data-lang="en">🔒 D. Quote Templates &amp; Private Links</span>
          <span data-lang="es">🔒 D. Plantillas de Cotización y Enlaces Privados</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Share a quote or itinerary with a link that self-destructs after a set number of views. Nobody can screenshot-forward it forever — once the view count runs out, the link dies.</span>
          <span data-lang="es">Comparte una cotización o itinerario con un enlace que se autodestruye después de un número fijo de vistas. Nadie puede reenviarlo para siempre — al agotarse las vistas, el enlace muere.</span>
        </p>

        <div class="ephemeral-panel">

          <!-- Global default -->
          <div class="ephemeral-default-row">
            <label for="ephemeral-default-max-views">
              <span data-lang="en">Default views before self-destruct (new links)</span>
              <span data-lang="es">Vistas por defecto antes de autodestruirse (enlaces nuevos)</span>
            </label>
            <input type="number" id="ephemeral-default-max-views" min="1" max="50" value="3" />
            <button type="button" class="dash-card-btn dash-card-btn--secondary" id="ephemeral-save-default-btn">
              <span data-lang="en">Save Default</span>
              <span data-lang="es">Guardar Predeterminado</span>
            </button>
          </div>

          <!-- Create form -->
          <form id="ephemeral-create-form" class="ephemeral-form">
            <input type="hidden" name="csrf_token" id="ephemeral-csrf-field" value="<?= $lly_csrf ?>">

            <div class="ephemeral-form-row">
              <label for="ephemeral-title">
                <span data-lang="en">Title (internal, e.g. client name)</span>
                <span data-lang="es">Título (interno, ej. nombre del cliente)</span>
              </label>
              <input type="text" id="ephemeral-title" name="title" required maxlength="190" />
            </div>

            <div class="ephemeral-form-row">
              <label for="ephemeral-resource-type">
                <span data-lang="en">Type</span><span data-lang="es">Tipo</span>
              </label>
              <select id="ephemeral-resource-type" name="resource_type">
                <option value="quote">Quote / Cotización</option>
                <option value="itinerary">Itinerary / Itinerario</option>
                <option value="custom" selected>Custom / Personalizado</option>
              </select>
            </div>

            <div class="ephemeral-form-row">
              <label for="ephemeral-quote-template">
                <span data-lang="en">PG-AI Quick Template — PINK LIPS Experience (edit after loading)</span>
                <span data-lang="es">Plantilla Rápida PG-AI — Experiencia PINK LIPS (edita después de cargar)</span>
              </label>
              <select id="ephemeral-quote-template">
                <option value="">— None / Ninguna —</option>
                <option value="balandra">Balandra Beach — $19,000 MXN</option>
                <option value="espiritu_santo">Isla Espíritu Santo — $24,000 MXN</option>
              </select>
            </div>

            <div class="ephemeral-form-row">
              <label for="ephemeral-payload">
                <span data-lang="en">Content shown on the link (or leave blank and set a URL below)</span>
                <span data-lang="es">Contenido a mostrar en el enlace (o déjalo vacío y define una URL abajo)</span>
              </label>
              <textarea id="ephemeral-payload" name="payload_html" rows="6"></textarea>
            </div>

            <div class="ephemeral-form-row">
              <label for="ephemeral-target-url">
                <span data-lang="en">— or — redirect to an existing page/PDF</span>
                <span data-lang="es">— o — redirigir a una página/PDF existente</span>
              </label>
              <input type="url" id="ephemeral-target-url" name="target_url" placeholder="https://..." />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="ephemeral-max-views">
                <span data-lang="en">Views before self-destruct (blank = default)</span>
                <span data-lang="es">Vistas antes de autodestruirse (vacío = predeterminado)</span>
              </label>
              <input type="number" id="ephemeral-max-views" name="max_views" min="1" max="50" />
              <button type="submit" class="dash-card-btn">
                <span data-lang="en">🔗 Generate Link</span>
                <span data-lang="es">🔗 Generar Enlace</span>
              </button>
            </div>
          </form>

          <script type="application/json" id="lly-pgai-quote-templates-data"><?= json_encode(lly_pgai_quote_templates(), JSON_UNESCAPED_UNICODE) ?></script>

          <p id="ephemeral-create-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>

          <!-- Active/recent links -->
          <div class="table-wrap">
            <table class="data-table" id="ephemeral-links-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Title</span><span data-lang="es">Título</span></th>
                  <th><span data-lang="en">Link</span><span data-lang="es">Enlace</span></th>
                  <th><span data-lang="en">Views</span><span data-lang="es">Vistas</span></th>
                  <th><span data-lang="en">Status</span><span data-lang="es">Estado</span></th>
                  <th><span data-lang="en">Actions</span><span data-lang="es">Acciones</span></th>
                </tr>
              </thead>
              <tbody id="ephemeral-links-tbody">
                <tr><td colspan="5"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></td></tr>
              </tbody>
            </table>
          </div>

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
        <span data-lang="en">PG-AI Hub · Confidential · Owner Only</span>
        <span data-lang="es">Centro PG-AI · Confidencial · Solo Propietario</span>
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
