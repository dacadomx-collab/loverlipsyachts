<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — pg_ai_config.php
 * PG-AI Pink Glove AI — dedicated Configuration screen, decoupled from
 * pg_ai_hub.php (2026-08-04 directive). pg_ai_hub.php stays the
 * day-to-day operational Hub (Live Leads, Chatbot Testbed); this screen
 * is where the content and connections that feed the chatbot get edited.
 * Direct deep link (not an include), validates its own session like
 * pg_ai_hub.php/strategy.php.
 *
 * Access tiers:
 *   owner + super_admin — Fleet Catalog editor, Master Prompt editor,
 *     Lead Notification Templates editor.
 *   super_admin ONLY    — Credentials Vault (AURA/WhatsApp/OpenAI
 *     fallback), Knowledge Module (Santuario_Genesis blueprint) editor,
 *     M2M Handshake test panel.
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

/** Same role gate as pg_ai_hub.php Section C (sql/007_add_role_to_lly_users.sql). */
$lly_is_super_admin = ($_SESSION['lly_role'] ?? '') === 'super_admin';
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Concierge IA Configuration" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Concierge IA Config</title>
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
            <span>Concierge IA Lover Lips · Config</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="pg_ai_hub.php" class="topbar-back-btn">
            <span data-lang="en">⬅️ Back to PG-AI Hub</span>
            <span data-lang="es">⬅️ Regresar al Hub PG-AI</span>
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
    <section class="section section-white" aria-labelledby="pgai-config-hub-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">PG-AI Pink Glove AI · Configuration</span>
          <span data-lang="es">PG-AI Pink Glove AI · Configuración</span>
        </p>
        <h1 class="section-title" id="pgai-config-hub-title">
          <span data-lang="en">PG-AI <em>Config</em></span>
          <span data-lang="es"><em>Configuración</em> PG-AI</span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          Everything that feeds the chatbot's content lives here — fleet catalog, master prompt, and lead notification templates. Connection credentials and the reusable blueprint editor are super_admin only.
        </p>
        <p class="section-subtitle" data-lang="es">
          Todo lo que alimenta el contenido del chatbot vive aquí — catálogo de flota, prompt maestro, y plantillas de notificación de leads. Las credenciales de conexión y el editor del molde reutilizable son exclusivos de super_admin.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION 1 — FLEET CATALOG EDITOR (owner + super_admin)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="pgcfg-section-fleet" aria-labelledby="pgcfg-fleet-title">
      <div class="container">
        <h2 class="section-title" id="pgcfg-fleet-title">
          <span data-lang="en">⚓ Fleet Catalog Editor</span>
          <span data-lang="es">⚓ Editor del Catálogo de Flota</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Add, edit, or remove vessels here — this is the exact same table both the AI chatbot (fleet facts it's allowed to cite) and the private Proposals report read from. A new vessel starts as "Pending" and stays invisible to the AI until you mark it "Verified" — the chatbot never guesses capacity or rate on its own.</span>
          <span data-lang="es">Agrega, edita o elimina embarcaciones aquí — es exactamente la misma tabla que leen tanto el chatbot de IA (hechos de flota que puede citar) como el reporte privado de Propuestas. Una embarcación nueva empieza como "Pendiente" e invisible para la IA hasta que la marques "Verificada" — el chatbot nunca adivina capacidad ni tarifa por su cuenta.</span>
        </p>

        <div class="ephemeral-panel">

          <form id="fleet-form" class="ephemeral-form">
            <input type="hidden" name="csrf_token" id="fleet-csrf-field" value="<?= $lly_csrf ?>">
            <input type="hidden" id="fleet-id" name="id" value="">

            <div class="ephemeral-form-row">
              <label for="fleet-vessel-name">
                <span data-lang="en">Vessel name</span><span data-lang="es">Nombre de la embarcación</span>
              </label>
              <input type="text" id="fleet-vessel-name" name="vessel_name" required maxlength="120" />
            </div>

            <div class="ephemeral-form-row">
              <label for="fleet-vessel-slug">
                <span data-lang="en">WordPress path (optional, e.g. /falcon-86/)</span>
                <span data-lang="es">Ruta de WordPress (opcional, ej. /falcon-86/)</span>
              </label>
              <input type="text" id="fleet-vessel-slug" name="vessel_slug" maxlength="160" />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-max-pax">
                <span data-lang="en">Max PAX</span><span data-lang="es">PAX Máximo</span>
              </label>
              <input type="number" id="fleet-max-pax" name="max_pax" min="0" max="500" />
              <label for="fleet-length-ft">
                <span data-lang="en">Length (ft)</span><span data-lang="es">Eslora (pies)</span>
              </label>
              <input type="number" id="fleet-length-ft" name="length_ft" min="0" max="500" />
              <label for="fleet-beam-ft">
                <span data-lang="en">Beam (ft)</span><span data-lang="es">Manga (pies)</span>
              </label>
              <input type="number" id="fleet-beam-ft" name="beam_ft" min="0" max="100" />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-cabins">
                <span data-lang="en">Cabins</span><span data-lang="es">Camarotes</span>
              </label>
              <input type="number" id="fleet-cabins" name="cabins_count" min="0" max="20" />
              <label for="fleet-bathrooms">
                <span data-lang="en">Bathrooms</span><span data-lang="es">Baños</span>
              </label>
              <input type="number" id="fleet-bathrooms" name="bathrooms_count" min="0" max="20" />
              <label for="fleet-crew-capacity">
                <span data-lang="en">Crew Capacity</span><span data-lang="es">Capacidad de Tripulación</span>
              </label>
              <input type="number" id="fleet-crew-capacity" name="crew_capacity" min="0" max="50" />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-year-built">
                <span data-lang="en">Year Built</span><span data-lang="es">Año de Fabricación</span>
              </label>
              <input type="number" id="fleet-year-built" name="year_built" min="1950" max="2100" />
              <label for="fleet-fuel-capacity">
                <span data-lang="en">Fuel Capacity (gal)</span><span data-lang="es">Capacidad de Combustible (gal)</span>
              </label>
              <input type="number" id="fleet-fuel-capacity" name="fuel_capacity_gal" min="0" max="20000" />
              <label for="fleet-water-capacity">
                <span data-lang="en">Water Capacity (gal)</span><span data-lang="es">Capacidad de Agua (gal)</span>
              </label>
              <input type="number" id="fleet-water-capacity" name="water_capacity_gal" min="0" max="20000" />
            </div>

            <div class="ephemeral-form-row">
              <label for="fleet-engine-notes">
                <span data-lang="en">Engine Notes (make / model / count / HP)</span>
                <span data-lang="es">Notas del Motor (marca / modelo / cantidad / HP)</span>
              </label>
              <input type="text" id="fleet-engine-notes" name="engine_notes" maxlength="255" placeholder="e.g. Twin Volvo Penta D6, 2×440 HP" />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-home-marina">
                <span data-lang="en">Home Marina / Berth</span><span data-lang="es">Marina / Atraque Base</span>
              </label>
              <input type="text" id="fleet-home-marina" name="home_marina" maxlength="120" />
              <label for="fleet-registration">
                <span data-lang="en">Registration / Hull Number</span><span data-lang="es">Matrícula / Número de Casco</span>
              </label>
              <input type="text" id="fleet-registration" name="registration_number" maxlength="60" />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-role-en">
                <span data-lang="en">Role tag (EN)</span><span data-lang="es">Etiqueta de rol (EN)</span>
              </label>
              <input type="text" id="fleet-role-en" name="role_label_en" maxlength="60" placeholder="Flagship / Signature / Available / Pending" />
              <label for="fleet-role-es">
                <span data-lang="en">Role tag (ES)</span><span data-lang="es">Etiqueta de rol (ES)</span>
              </label>
              <input type="text" id="fleet-role-es" name="role_label_es" maxlength="60" placeholder="Insignia / Estrella / Disponible / Pendiente" />
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-status-pill">
                <span data-lang="en">Badge color</span><span data-lang="es">Color de insignia</span>
              </label>
              <select id="fleet-status-pill" name="status_pill">
                <option value="pill-pink">Pink</option>
                <option value="pill-gold">Gold</option>
                <option value="pill-green">Green</option>
                <option value="pill-orange" selected>Orange</option>
              </select>
              <label for="fleet-verification-status">
                <span data-lang="en">Status (AI can only cite "Verified")</span>
                <span data-lang="es">Estado (la IA solo puede citar "Verificado")</span>
              </label>
              <select id="fleet-verification-status" name="verification_status">
                <option value="pending" selected>Pending / Pendiente</option>
                <option value="verified">Verified / Verificado</option>
              </select>
            </div>

            <div class="ephemeral-form-row ephemeral-form-row--inline">
              <label for="fleet-submit-btn"></label>
              <button type="submit" id="fleet-submit-btn" class="dash-card-btn">
                <span data-lang="en">💾 Save Vessel</span>
                <span data-lang="es">💾 Guardar Embarcación</span>
              </button>
              <button type="button" id="fleet-cancel-edit-btn" class="dash-card-btn dash-card-btn--secondary" hidden>
                <span data-lang="en">Cancel Edit</span>
                <span data-lang="es">Cancelar Edición</span>
              </button>
            </div>
          </form>

          <p id="fleet-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>

          <div class="table-wrap">
            <table class="data-table" id="fleet-catalog-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Vessel</span><span data-lang="es">Embarcación</span></th>
                  <th><span data-lang="en">PAX</span><span data-lang="es">PAX</span></th>
                  <th><span data-lang="en">Length</span><span data-lang="es">Eslora</span></th>
                  <th><span data-lang="en">Specs</span><span data-lang="es">Specs</span></th>
                  <th><span data-lang="en">Status</span><span data-lang="es">Estado</span></th>
                  <th><span data-lang="en">Actions</span><span data-lang="es">Acciones</span></th>
                </tr>
              </thead>
              <tbody id="fleet-catalog-tbody">
                <tr><td colspan="6"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></td></tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION 2 — MASTER PROMPT EDITOR (owner + super_admin)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" id="pgcfg-section-prompt" aria-labelledby="pgcfg-prompt-title">
      <div class="container">
        <h2 class="section-title" id="pgcfg-prompt-title">
          <span data-lang="en">📜 Master Prompt Editor</span>
          <span data-lang="es">📜 Editor del Prompt Maestro</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">This is the exact file the AI reads on every request — Lester's persona, hospitality tone, fleet facts, and the commercial locks (NO_PRICE_WITHOUT_LEAD_DATA, White-Glove Escalation). Saving here changes what the live chatbot says on the next message.</span>
          <span data-lang="es">Este es el archivo exacto que la IA lee en cada solicitud — la personalidad de Lester, el tono de hospitalidad, los hechos de flota, y los cerrojos comerciales (NO_PRICE_WITHOUT_LEAD_DATA, Escalación de Guante Blanco). Guardar aquí cambia lo que dice el chatbot en vivo desde el siguiente mensaje.</span>
        </p>
        <div class="ephemeral-panel">
          <textarea id="prompt-editor-textarea" class="editor-textarea editor-textarea--chapter" rows="24" spellcheck="false"></textarea>
          <input type="hidden" id="prompt-editor-csrf-field" value="<?= $lly_csrf ?>">
          <div class="ephemeral-form-row ephemeral-form-row--inline">
            <button type="button" id="prompt-editor-save-btn" class="dash-card-btn">
              <span data-lang="en">💾 Save Master Prompt</span>
              <span data-lang="es">💾 Guardar Prompt Maestro</span>
            </button>
          </div>
          <p id="prompt-editor-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION 3 — LEAD NOTIFICATION TEMPLATES (owner + super_admin)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="pgcfg-section-templates" aria-labelledby="pgcfg-templates-title">
      <div class="container">
        <h2 class="section-title" id="pgcfg-templates-title">
          <span data-lang="en">✉️ Lead Notification Templates</span>
          <span data-lang="es">✉️ Plantillas de Notificación de Leads</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Content for the internal "new lead captured" alert (Email/WhatsApp) — not the guest-facing chatbot replies. Editable here; storage only for now, no automated send is wired to these yet.</span>
          <span data-lang="es">Contenido de la alerta interna de "nuevo lead capturado" (Email/WhatsApp) — no son las respuestas del chatbot al huésped. Editable aquí; por ahora solo almacenamiento, todavía no hay un envío automatizado conectado.</span>
        </p>
        <input type="hidden" id="templates-csrf-field" value="<?= $lly_csrf ?>">
        <div id="templates-list" class="ephemeral-panel">
          <p><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></p>
        </div>
      </div>
    </section>

    <?php if ($lly_is_super_admin): ?>
    <!-- ═══════════════════════════════════════════════════════════════
         SECTION 4 — CREDENTIALS VAULT (super_admin ONLY)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" id="pgcfg-section-vault" aria-labelledby="pgcfg-vault-title">
      <div class="container">
        <h2 class="section-title" id="pgcfg-vault-title">
          <span data-lang="en">🔐 Credentials Vault (super_admin)</span>
          <span data-lang="es">🔐 Bóveda de Credenciales (super_admin)</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">Persisted in core/.env via a fixed whitelist — never a general config editor. Secret fields are masked after saving; they're never re-sent to the browser in full.</span>
          <span data-lang="es">Se guardan en core/.env vía una lista fija — nunca un editor de configuración general. Los campos secretos se enmascaran tras guardarse; nunca se reenvían completos al navegador.</span>
        </p>

        <div class="ephemeral-panel">
          <form id="pgai-settings-form" class="pgai-settings-grid" autocomplete="off">
            <input type="hidden" name="csrf_token" id="pgai-settings-csrf-field" value="<?= $lly_csrf ?>">

            <fieldset class="pgai-settings-fieldset">
              <legend><span data-lang="en">🎛️ Active AI Engine</span><span data-lang="es">🎛️ Motor de Inferencia Activo</span></legend>
              <p class="section-desc">
                <span data-lang="en">Which route the live chatbot tries first (core/ProxyBridge.php). The other one stays as the automatic fallback either way — this only reorders, it never disables a configured route.</span>
                <span data-lang="es">Qué ruta prueba primero el chatbot en vivo (core/ProxyBridge.php). La otra sigue como respaldo automático de cualquier forma — esto solo reordena, nunca deshabilita una ruta configurada.</span>
              </p>
              <div class="pgai-settings-field pgai-settings-field--wide">
                <label for="pgai-primary-engine">
                  <span data-lang="en">Primary AI Engine</span><span data-lang="es">Motor de IA Principal</span>
                </label>
                <select id="pgai-primary-engine" data-setting-key="PRIMARY_AI_PROVIDER">
                  <option value="openai">OpenAI Direct / OpenAI Directo (Recommended — speed &amp; memory / Recomendado — velocidad y memoria)</option>
                  <option value="aura">AURA Linux Core (private satellite server / servidor satélite privado)</option>
                </select>
              </div>
            </fieldset>

            <fieldset class="pgai-settings-fieldset">
              <legend><span data-lang="en">AURA Gateway (ACADEP)</span><span data-lang="es">Gateway AURA (ACADEP)</span></legend>

              <div class="pgai-settings-field">
                <label for="pgai-aura-base-url">Base URL</label>
                <input type="text" id="pgai-aura-base-url" data-setting-key="ACADEP_AURA_BASE_URL" placeholder="http://192.168.1.224:8090" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-aura-endpoint">Gateway Endpoint</label>
                <input type="text" id="pgai-aura-endpoint" data-setting-key="ACADEP_AURA_GATEWAY_ENDPOINT" placeholder="/api/v2/aura/gateway" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-aura-key">
                  <span data-lang="en">AURA Key</span><span data-lang="es">Llave AURA</span>
                  <span class="pgai-settings-badge" id="pgai-aura-key-badge"></span>
                </label>
                <input type="password" id="pgai-aura-key" data-setting-key="ACADEP_AURA_KEY" placeholder="•••• •••• ••••" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-aura-tenant">Tenant</label>
                <input type="text" id="pgai-aura-tenant" data-setting-key="ACADEP_AURA_TENANT" placeholder="LOVER_LIPS_YACHTS" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-aura-agent-id">Agent ID (UUID)</label>
                <input type="text" id="pgai-aura-agent-id" data-setting-key="ACADEP_AURA_AGENT_ID" placeholder="899fd35d-..." autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-aura-fallback">
                  <span data-lang="en">WAN Fallback URL</span><span data-lang="es">URL de Respaldo WAN</span>
                </label>
                <input type="text" id="pgai-aura-fallback" data-setting-key="ACADEP_AURA_FALLBACK_URL" placeholder="https://axon.acadep.com" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-aura-fallback-ip">
                  <span data-lang="en">WAN Fallback IP (optional, DNS failure only)</span><span data-lang="es">IP de Respaldo WAN (opcional, solo si falla DNS)</span>
                </label>
                <input type="text" id="pgai-aura-fallback-ip" data-setting-key="ACADEP_AURA_FALLBACK_IP" placeholder="203.0.113.10" autocomplete="off" />
              </div>

              <div class="pgai-settings-field pgai-settings-field--wide">
                <button type="button" id="handshake-test-btn" class="dash-card-btn dash-card-btn--secondary">
                  <span data-lang="en">🧪 Test AURA Linux Connection</span>
                  <span data-lang="es">🧪 Probar Conexión AURA Linux</span>
                </button>
                <div class="aura-diag-telemetry" id="handshake-telemetry" hidden>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Status</span><span data-lang="es">Estado</span></p>
                    <p class="aura-diag-tile-value" id="handshake-status">—</p>
                  </div>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Latency</span><span data-lang="es">Latencia</span></p>
                    <p class="aura-diag-tile-value" id="handshake-latency">—</p>
                  </div>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Tenant</span><span data-lang="es">Tenant</span></p>
                    <p class="aura-diag-tile-value" id="handshake-tenant">—</p>
                  </div>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Engine / Model</span><span data-lang="es">Motor / Modelo</span></p>
                    <p class="aura-diag-tile-value" id="handshake-engine">—</p>
                  </div>
                </div>
                <p id="handshake-result" class="ephemeral-feedback" role="status" aria-live="polite"></p>
              </div>
            </fieldset>

            <fieldset class="pgai-settings-fieldset">
              <legend><span data-lang="en">Meta WhatsApp Business API</span><span data-lang="es">API de WhatsApp Business de Meta</span></legend>

              <div class="pgai-settings-field">
                <label for="pgai-wa-phone-id">Phone Number ID</label>
                <input type="text" id="pgai-wa-phone-id" data-setting-key="WHATSAPP_PHONE_NUMBER_ID" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-wa-access-token">
                  <span data-lang="en">Access Token</span><span data-lang="es">Token de Acceso</span>
                  <span class="pgai-settings-badge" id="pgai-wa-access-token-badge"></span>
                </label>
                <input type="password" id="pgai-wa-access-token" data-setting-key="WHATSAPP_ACCESS_TOKEN" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-wa-verify-token">
                  <span data-lang="en">Verify Token</span><span data-lang="es">Token de Verificación</span>
                </label>
                <input type="text" id="pgai-wa-verify-token" data-setting-key="WHATSAPP_VERIFY_TOKEN" autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-wa-app-secret">
                  <span data-lang="en">App Secret</span><span data-lang="es">Secreto de la App</span>
                  <span class="pgai-settings-badge" id="pgai-wa-app-secret-badge"></span>
                </label>
                <input type="password" id="pgai-wa-app-secret" data-setting-key="WHATSAPP_APP_SECRET" autocomplete="off" />
              </div>
            </fieldset>

            <fieldset class="pgai-settings-fieldset">
              <legend><span data-lang="en">OpenAI Direct</span><span data-lang="es">OpenAI Directo</span></legend>
              <p class="section-desc">
                <span data-lang="en">Tried first when "Active AI Engine" above is set to OpenAI Direct and a key is configured; otherwise it's the automatic fallback if AURA fails. See docs/02_SYSTEM_CODEX_REGISTRY.md.</span>
                <span data-lang="es">Se prueba primero cuando "Motor de Inferencia Activo" arriba está en OpenAI Directo y hay una llave configurada; si no, es el respaldo automático si AURA falla. Ver docs/02_SYSTEM_CODEX_REGISTRY.md.</span>
              </p>

              <div class="pgai-settings-field">
                <label for="pgai-openai-key">
                  <span data-lang="en">OpenAI API Key</span><span data-lang="es">Llave de API de OpenAI</span>
                  <span class="pgai-settings-badge" id="pgai-openai-key-badge"></span>
                </label>
                <input type="password" id="pgai-openai-key" data-setting-key="FALLBACK_AI_PROVIDER_KEY" placeholder="sk-..." autocomplete="off" />
              </div>
              <div class="pgai-settings-field">
                <label for="pgai-openai-model">
                  <span data-lang="en">Model</span><span data-lang="es">Modelo</span>
                </label>
                <select id="pgai-openai-model" data-setting-key="FALLBACK_AI_PROVIDER_MODEL">
                  <option value="gpt-4o-mini">gpt-4o-mini (recommended — speed &amp; cost / recomendado — velocidad y costo)</option>
                  <option value="gpt-4o">gpt-4o (max reasoning / máximo razonamiento)</option>
                  <option value="gpt-4.1-mini">gpt-4.1-mini</option>
                </select>
              </div>

              <div class="pgai-settings-field pgai-settings-field--wide">
                <button type="button" id="openai-test-btn" class="dash-card-btn dash-card-btn--secondary">
                  <span data-lang="en">🧪 Test OpenAI Connection</span>
                  <span data-lang="es">🧪 Probar Conexión OpenAI</span>
                </button>
                <div class="aura-diag-telemetry" id="openai-test-telemetry" hidden>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Status</span><span data-lang="es">Estado</span></p>
                    <p class="aura-diag-tile-value" id="openai-test-status">—</p>
                  </div>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Latency</span><span data-lang="es">Latencia</span></p>
                    <p class="aura-diag-tile-value" id="openai-test-latency">—</p>
                  </div>
                  <div class="aura-diag-tile">
                    <p class="aura-diag-tile-label"><span data-lang="en">Active Model</span><span data-lang="es">Modelo Activo</span></p>
                    <p class="aura-diag-tile-value" id="openai-test-model">—</p>
                  </div>
                </div>
                <p id="openai-test-response" class="ephemeral-feedback" role="status" aria-live="polite"></p>
              </div>
            </fieldset>

            <button type="submit" class="dash-card-btn pgai-settings-save">
              <span data-lang="en">💾 Save Connection Settings</span>
              <span data-lang="es">💾 Guardar Configuración de Conexión</span>
            </button>
            <p id="pgai-settings-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>
          </form>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION 5 — M2M DIAGNOSTIC SANDBOX (super_admin ONLY)
         Quick tests moved into their own fieldsets in Section 4
         (2026-08-18) — this stays as the entry point to the full
         latency/token telemetry sandbox (aura_diagnostic.php).
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="pgcfg-section-handshake" aria-labelledby="pgcfg-handshake-title">
      <div class="container">
        <h2 class="section-title" id="pgcfg-handshake-title">
          <span data-lang="en">📡 Full Diagnostic Sandbox</span>
          <span data-lang="es">📡 Sandbox Completo de Diagnóstico</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">The quick "Test" buttons above (AURA and OpenAI fieldsets) cover a one-click connectivity check. For custom prompts and the complete latency/token telemetry view against AURA, open the dedicated sandbox.</span>
          <span data-lang="es">Los botones rápidos de "Probar" arriba (fieldsets de AURA y OpenAI) cubren una verificación de conectividad de un clic. Para prompts personalizados y la vista completa de telemetría de latencia/tokens contra AURA, abre el sandbox dedicado.</span>
        </p>
        <a class="dash-card-btn dash-card-btn--secondary" href="aura_diagnostic.php">
          <span data-lang="en">Open Full Diagnostic Sandbox</span>
          <span data-lang="es">Abrir Sandbox Completo de Diagnóstico</span>
        </a>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION 6 — KNOWLEDGE MODULE EDITOR (super_admin ONLY)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" id="pgcfg-section-moduledoc" aria-labelledby="pgcfg-moduledoc-title">
      <div class="container">
        <h2 class="section-title" id="pgcfg-moduledoc-title">
          <span data-lang="en">🧬 Knowledge Module Editor</span>
          <span data-lang="es">🧬 Editor del Módulo de Conocimiento</span>
        </h2>
        <p class="section-desc">
          <span data-lang="en">modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md — the agnostic Santuario_Genesis blueprint (now also including the portable prompt template layer). This is governance content about the reusable module itself, not Lover Lips Yachts business content — that's why it's restricted here, separate from the Master Prompt editor above.</span>
          <span data-lang="es">modulos/MOD_CONCIERGE_COGNITIVO_OMNICANAL.md — el molde agnóstico Santuario_Genesis (ahora también incluye la capa de plantilla de prompt portable). Es contenido de gobernanza sobre el módulo reutilizable, no contenido de negocio de Lover Lips Yachts — por eso está restringido aquí, separado del editor del Prompt Maestro de arriba.</span>
        </p>
        <div class="ephemeral-panel">
          <textarea id="moduledoc-editor-textarea" class="editor-textarea editor-textarea--chapter" rows="24" spellcheck="false"></textarea>
          <input type="hidden" id="moduledoc-editor-csrf-field" value="<?= $lly_csrf ?>">
          <div class="ephemeral-form-row ephemeral-form-row--inline">
            <button type="button" id="moduledoc-editor-save-btn" class="dash-card-btn">
              <span data-lang="en">💾 Save Module Doc</span>
              <span data-lang="es">💾 Guardar Documento del Módulo</span>
            </button>
          </div>
          <p id="moduledoc-editor-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>
        </div>
      </div>
    </section>
    <?php endif /* $lly_is_super_admin */ ?>

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
        <span data-lang="en">Concierge IA Lover Lips · Config · Confidential · Owner Only</span>
        <span data-lang="es">Concierge IA Lover Lips · Configuración · Confidencial · Solo Propietario</span>
      </p>
    </div>
  </footer>

  <!-- Floating "Back to Top" — hidden until scroll > 300px (see main.js) -->
  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>

</body>
</html>
