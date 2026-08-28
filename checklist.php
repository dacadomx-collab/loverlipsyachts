<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — checklist.php
 * Catamaran Inventory Checklist — digital pre/post-charter inspection for
 * NOMADA (extensible to the rest of the fleet via the Vessel field). Every
 * save is a new row in ll_inventory_checklists (sql/012) — the fleet's
 * digital bitácora — browsable/searchable from the Historial view below.
 * Direct deep link, validates its own session like every other Cockpit page.
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
  <meta name="description" content="Lover Lips Yachts — Catamaran Inventory Checklist" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Inventory Checklist</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <script src="assets/js/theme-init.js"></script>
  <style>
  /* Page-specific additions only — everything else (topbar, tables, pills,
     dialogs, toggles, dark theme) comes from assets/css/style.css so this
     module looks and themes exactly like the rest of the Cockpit. */

  .op-card { background: var(--surface); border: 1px solid var(--ink-10); border-radius: var(--r-lg); padding: 1.1rem; margin-bottom: 1.25rem; box-shadow: var(--sh-card); }
  .op-grid { display: grid; grid-template-columns: 1fr; gap: .85rem; }
  @media (min-width: 640px) { .op-grid { grid-template-columns: repeat(3, 1fr); } }
  .op-field label { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--ink-60); margin-bottom: .35rem; }
  .op-field input { width: 100%; padding: .6rem .7rem; border: 1px solid var(--ink-10); border-radius: var(--r-sm); font-size: .92rem; font-family: inherit; background: var(--surface-2); color: var(--ink); }
  .op-field input:focus { outline: 2px solid var(--gold); outline-offset: 1px; }

  .view-switch-wrap { display: flex; justify-content: center; margin-bottom: 1.25rem; }

  .editing-banner { display: flex; align-items: center; gap: .7rem; flex-wrap: wrap; background: var(--gold-10); border: 1px solid var(--gold-20); color: var(--ink); padding: .7rem 1rem; border-radius: var(--r-md); margin-bottom: 1rem; font-size: .85rem; font-weight: 600; }
  .editing-banner[hidden] { display: none; }

  .checklist-summary-bar { position: sticky; top: 0; z-index: 15; background: var(--topbar-bg); backdrop-filter: blur(8px); border-bottom: 1px solid var(--topbar-border); margin: 0 -1rem 1.25rem; padding: .6rem 1rem; }
  .summary-row { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
  .summary-pills { display: flex; gap: .4rem; overflow-x: auto; flex: 1 1 auto; }
  .summary-pill { flex: 0 0 auto; display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 700; padding: .3rem .6rem; border-radius: var(--r-full); }
  .summary-pill.total { background: var(--ink-10); color: var(--ink); }
  .summary-pill.good { background: rgba(16,185,129,.10); color: var(--success); }
  .summary-pill.damaged { background: rgba(245,158,11,.10); color: var(--warning); }
  .summary-pill.missing { background: var(--pink-10); color: var(--pink); }
  .summary-pill.replace { background: var(--navy-10); color: var(--navy); }
  .summary-actions { margin-left: auto; display: flex; gap: .5rem; flex: 0 0 auto; }

  .checklist-tabs { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .9rem; -webkit-overflow-scrolling: touch; }
  .checklist-tab { flex: 0 0 auto; display: flex; align-items: center; gap: .4rem; background: var(--surface); border: 1px solid var(--ink-10); color: var(--ink); font-size: .8rem; font-weight: 600; padding: .5rem .8rem; border-radius: var(--r-full); cursor: pointer; }
  .checklist-tab .n { background: var(--navy); color: var(--gold); border-radius: 50%; width: 1.2rem; height: 1.2rem; font-size: .66rem; display: inline-flex; align-items: center; justify-content: center; flex: 0 0 auto; }
  .checklist-tab.active { background: var(--pink); color: #fff; border-color: var(--pink); }
  .checklist-tab.active .n { background: rgba(255,255,255,.25); color: #fff; }
  .checklist-tab .badge { background: var(--pink); color: #fff; border-radius: 999px; padding: 0 .4rem; font-size: .66rem; }
  .checklist-tab.active .badge { background: rgba(255,255,255,.35); }

  .tab-panel { background: var(--surface); border: 1px solid var(--ink-10); border-radius: var(--r-lg); box-shadow: var(--sh-card); overflow: hidden; }
  .tab-panel-header { padding: 1rem 1.1rem; border-bottom: 1px solid var(--ink-10); }
  .tab-panel-header h2 { font-family: var(--font-display); font-size: 1.1rem; margin: 0; }
  .subgroup-title { font-family: var(--font-display); font-weight: 700; font-size: .92rem; color: var(--navy); padding: .9rem 1.1rem .2rem; }
  [data-theme="dark"] .subgroup-title { color: var(--gold); }
  .callout { margin: .8rem 1.1rem 0; padding: .7rem .9rem; border-radius: var(--r-md); font-size: .82rem; }
  .callout.info { background: var(--navy-10); color: var(--navy); }
  [data-theme="dark"] .callout.info { color: #cfe0ee; }
  .callout.warn { background: var(--pink-10); color: var(--pink); }

  .item-row { display: grid; grid-template-columns: 1fr; gap: .5rem; padding: .75rem 1.1rem; border-top: 1px solid var(--ink-10); }
  @media (min-width: 760px) { .item-row { grid-template-columns: minmax(180px,1.5fr) 2.3fr; align-items: center; } }
  .item-label { font-size: .88rem; font-weight: 600; }
  .item-controls { display: flex; flex-wrap: wrap; gap: .45rem; align-items: center; }
  .status-group { display: flex; flex-wrap: wrap; gap: .3rem; }
  .status-btn { flex: 1 1 auto; min-width: 78px; border: 1px solid var(--ink-10); background: var(--surface-2); border-radius: var(--r-sm); padding: .34rem .5rem; font-size: .72rem; font-weight: 700; cursor: pointer; color: var(--ink-60); }
  .status-btn.is-active[data-value="good"]    { background: rgba(16,185,129,.14); border-color: var(--success); color: var(--success); }
  .status-btn.is-active[data-value="damaged"] { background: rgba(245,158,11,.14); border-color: var(--warning); color: var(--warning); }
  .status-btn.is-active[data-value="missing"] { background: var(--pink-10); border-color: var(--pink); color: var(--pink); }
  .status-btn.is-active[data-value="replace"] { background: var(--navy-10); border-color: var(--navy); color: var(--navy); }
  .input-group { display: flex; flex-wrap: wrap; gap: .4rem; flex: 1 1 auto; }
  .item-count { width: 6.5rem; }
  .item-expiry { width: 9rem; }
  .item-notes { flex: 1 1 12rem; }
  .item-count, .item-expiry, .item-notes { padding: .4rem .55rem; border: 1px solid var(--ink-10); border-radius: var(--r-sm); font-size: .8rem; font-family: inherit; background: var(--surface-2); color: var(--ink); }

  .engine-row { display: grid; grid-template-columns: 1fr; gap: .5rem; padding: .8rem 1.1rem; border-top: 1px solid var(--ink-10); }
  @media (min-width: 820px) { .engine-row { grid-template-columns: 1.1fr 1fr 1fr; align-items: start; } }
  .engine-side-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: var(--ink-60); margin-bottom: .3rem; }
  .engine-side { background: var(--surface-2); border-radius: var(--r-md); padding: .6rem; }

  .tab-panel-nav { display: flex; justify-content: space-between; gap: .6rem; padding: 1rem 1.1rem; }

  .final-grid { display: grid; grid-template-columns: 1fr; gap: .8rem; padding: 1rem 1.1rem; }
  @media (min-width: 640px) { .final-grid { grid-template-columns: 1fr 1fr; } }
  .final-field label { display: block; font-size: .78rem; font-weight: 700; color: var(--ink-60); margin-bottom: .3rem; }
  .final-field textarea, .final-field input { width: 100%; padding: .6rem .65rem; border: 1px solid var(--ink-10); border-radius: var(--r-sm); font-family: inherit; font-size: .88rem; background: var(--surface-2); color: var(--ink); }
  .final-field textarea { min-height: 4.5rem; resize: vertical; }
  .final-field.full { grid-column: 1 / -1; }
  .sig-row { display: flex; gap: .8rem; flex-wrap: wrap; }
  #captain-signature.invalid, label.invalid { color: var(--pink); }
  #captain-signature.invalid { border-color: var(--pink); background: var(--pink-10); }

  #validation-alert { position: fixed; left: 1rem; right: 1rem; bottom: 1rem; z-index: 200; display: none; background: var(--pink); color: #fff; padding: .8rem 1rem; border-radius: var(--r-md); font-size: .85rem; font-weight: 600; text-align: center; box-shadow: var(--sh-card); max-width: 640px; margin: 0 auto; }
  #validation-alert.show { display: block; }

  #checklist-toast { top: 5.5rem; }

  /* ── History / Crew / Utensils views ──────────────────────────────── */
  #view-history, #view-crew, #view-inventory { display: none; }
  #view-history.active, #view-crew.active, #view-inventory.active { display: block; }
  #view-checklist.hidden-view { display: none; }

  .catalog-vessel-bar { display: flex; align-items: flex-end; gap: .7rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .catalog-vessel-bar .op-field { flex: 1 1 220px; max-width: 320px; margin: 0; }
  .catalog-subpanel { background: var(--surface-2); border: 1px solid var(--ink-10); border-radius: var(--r-md); padding: .9rem; margin-bottom: 1.1rem; }
  .catalog-subpanel h3 { font-family: var(--font-display); font-size: .92rem; margin: 0 0 .6rem; }
  .catalog-role-list { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .7rem; }
  .catalog-role-chip { display: inline-flex; align-items: center; gap: .35rem; background: var(--surface); border: 1px solid var(--ink-10); border-radius: var(--r-full); padding: .3rem .4rem .3rem .7rem; font-size: .78rem; font-weight: 600; }
  .catalog-role-chip button { background: none; border: none; cursor: pointer; font-size: .78rem; line-height: 1; padding: .15rem; color: var(--ink-60); }
  .catalog-role-chip button:hover { color: var(--pink); }
  .catalog-translate-btn { flex: 0 0 auto; background: var(--surface); border: 1px solid var(--gold); color: var(--gold); border-radius: var(--r-sm); padding: .4rem .6rem; font-size: .72rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
  .catalog-translate-btn:hover { background: var(--gold-10); }
  .catalog-translate-btn[disabled] { opacity: .6; cursor: wait; }
  .checklist-detail-block-title { font-family: var(--font-display); font-size: 1rem; font-weight: 700; color: var(--ink); margin: 1.4rem 0 .5rem; }
  .checklist-detail-block-title:first-of-type { margin-top: 0; }
  .checklist-detail-row { display: flex; justify-content: space-between; gap: .8rem; padding: .5rem 0; border-bottom: 1px solid var(--ink-10); font-size: .88rem; }
  .checklist-detail-row:last-child { border-bottom: none; }
  .checklist-detail-row .k { color: var(--ink-60); }
  .checklist-row-actions { display: flex; flex-wrap: wrap; gap: .35rem; }

  @media print {
    .topbar, .view-switch-wrap, .checklist-summary-bar, .checklist-tabs, .tab-panel-nav,
    #validation-alert, .back-to-top, footer.footer, #view-history { display: none !important; }
    .tab-panel[hidden] { display: block !important; }
    body { background: #fff; }
    .tab-panel { border: 1px solid #000; box-shadow: none; break-inside: avoid-page; }
    .item-row, .engine-row { break-inside: avoid; }
    .status-btn.is-active { font-weight: 800; text-decoration: underline; }
  }
  </style>
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
            <span>Inventory Checklist</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="dashboard.php" class="topbar-back-btn">
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
    <section class="section section-white" aria-labelledby="checklist-page-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Fleet Operations</span>
          <span data-lang="es">Operaciones de Flota</span>
        </p>
        <h1 class="section-title" id="checklist-page-title">
          <span data-lang="en">🛥️ Catamaran Inventory Checklist</span>
          <span data-lang="es">🛥️ Checklist de Inventario del Catamarán</span>
        </h1>
        <p class="section-subtitle" data-lang="en">Pre/Post-charter condition &amp; inventory record — every save becomes a permanent bitácora entry.</p>
        <p class="section-subtitle" data-lang="es">Registro de condición e inventario pre/post-charter — cada guardado queda como entrada permanente en la bitácora.</p>

        <input type="hidden" id="checklist-csrf-field" value="<?= $lly_csrf ?>">

        <div class="view-switch-wrap">
          <div class="lang-toggle" role="group" aria-label="View / Vista">
            <button type="button" class="lang-btn active" id="view-btn-checklist" data-view="checklist">📝 <span data-lang="en">Checklist</span><span data-lang="es">Checklist</span></button>
            <button type="button" class="lang-btn" id="view-btn-crew" data-view="crew">👥 <span data-lang="en">Crew</span><span data-lang="es">Tripulación</span></button>
            <button type="button" class="lang-btn" id="view-btn-inventory" data-view="inventory">🧰 <span data-lang="en">Utensils</span><span data-lang="es">Utensilios</span></button>
            <button type="button" class="lang-btn" id="view-btn-history" data-view="history">📜 <span data-lang="en">Historial</span><span data-lang="es">Historial</span></button>
          </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             VIEW — CHECKLIST (fill-out form)
        ═══════════════════════════════════════════════════════════ -->
        <div id="view-checklist">

          <section class="op-card" aria-label="Operative header">
            <div class="op-grid">
              <div class="op-field">
                <label><span data-lang="en">Vessel / Catamaran</span><span data-lang="es">Embarcación / Catamarán</span></label>
                <input type="text" id="op-vessel" value="NOMADA" />
              </div>
              <div class="op-field">
                <label><span data-lang="en">Charter Date</span><span data-lang="es">Fecha de Charter</span></label>
                <input type="date" id="op-date" />
              </div>
              <div class="op-field">
                <label><span data-lang="en">Guests Count</span><span data-lang="es">Número de Huéspedes</span></label>
                <input type="number" id="op-guests" min="0" placeholder="0" />
              </div>
              <div class="op-field">
                <label><span data-lang="en">Captain</span><span data-lang="es">Capitán</span></label>
                <input type="text" id="op-captain" placeholder="Full name" />
              </div>
              <div class="op-field">
                <label><span data-lang="en">Checked by</span><span data-lang="es">Revisado por</span></label>
                <input type="text" id="op-checkedby" placeholder="Crew member name" />
              </div>
              <div class="op-field">
                <label><span data-lang="en">Inspection Type</span><span data-lang="es">Tipo de Inspección</span></label>
                <div class="lang-toggle" role="radiogroup" aria-label="Before or After Charter" style="width:100%;">
                  <button type="button" class="lang-btn active" id="mode-before" data-value="before" style="flex:1;"><span data-lang="en">Before</span><span data-lang="es">Antes</span></button>
                  <button type="button" class="lang-btn" id="mode-after" data-value="after" style="flex:1;"><span data-lang="en">After</span><span data-lang="es">Después</span></button>
                </div>
              </div>
            </div>
          </section>

          <div class="editing-banner" id="checklist-editing-banner" hidden>
            ✏️ <span data-lang="en">Editing a saved bitácora entry — Save will update it in place.</span><span data-lang="es">Editando una entrada guardada de la bitácora — Guardar la actualizará en su lugar.</span>
            <button type="button" class="dash-card-btn dash-card-btn--secondary" id="btn-cancel-edit">✕ <span data-lang="en">Cancel / New Checklist</span><span data-lang="es">Cancelar / Nuevo Checklist</span></button>
          </div>

          <div class="checklist-summary-bar" role="status" aria-live="polite">
            <div class="summary-row">
              <div class="summary-pills" id="checklist-summary-pills"></div>
              <div class="summary-actions">
                <button type="button" class="dash-card-btn dash-card-btn--secondary" id="btn-print">🖨️ <span data-lang="en">Print</span><span data-lang="es">Imprimir</span></button>
                <button type="button" class="dash-card-btn" id="btn-save">💾 <span data-lang="en">Save to Log</span><span data-lang="es">Guardar en Bitácora</span></button>
              </div>
            </div>
          </div>

          <nav class="checklist-tabs" id="checklist-tabs" aria-label="Section jump menu"></nav>

          <div id="checklist-panels"></div>

        </div>

        <!-- ═══════════════════════════════════════════════════════════
             VIEW — HISTORIAL
        ═══════════════════════════════════════════════════════════ -->
        <div id="view-history">
          <div class="leads-filters">
            <input type="search" id="checklist-search-input" class="leads-filter-input leads-filter-input--search"
                   placeholder="Search vessel, captain, notes…" data-placeholder-en="Search vessel, captain, notes…" data-placeholder-es="Buscar embarcación, capitán, notas…" />
            <label class="leads-filter-label">
              <span data-lang="en">From</span><span data-lang="es">Desde</span>
              <input type="date" id="checklist-date-from" class="leads-filter-input" />
            </label>
            <label class="leads-filter-label">
              <span data-lang="en">To</span><span data-lang="es">Hasta</span>
              <input type="date" id="checklist-date-to" class="leads-filter-input" />
            </label>
            <button type="button" class="dash-card-btn dash-card-btn--secondary" id="checklist-filter-clear">
              <span data-lang="en">Clear</span><span data-lang="es">Limpiar</span>
            </button>
          </div>

          <div class="table-wrap">
            <table class="data-table" id="checklist-history-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Date</span><span data-lang="es">Fecha</span></th>
                  <th><span data-lang="en">Vessel</span><span data-lang="es">Embarcación</span></th>
                  <th><span data-lang="en">Mode</span><span data-lang="es">Tipo</span></th>
                  <th><span data-lang="en">Captain</span><span data-lang="es">Capitán</span></th>
                  <th><span data-lang="en">Guests</span><span data-lang="es">Huéspedes</span></th>
                  <th><span data-lang="en">Flagged</span><span data-lang="es">Marcados</span></th>
                  <th><span data-lang="en">Actions</span><span data-lang="es">Acciones</span></th>
                </tr>
              </thead>
              <tbody id="checklist-history-tbody">
                <tr><td colspan="7"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             VIEW — CREW (roster + positions catalog, per vessel)
        ═══════════════════════════════════════════════════════════ -->
        <div id="view-crew">

          <div class="catalog-vessel-bar">
            <div class="op-field">
              <label><span data-lang="en">Vessel / Catamaran</span><span data-lang="es">Embarcación / Catamarán</span></label>
              <input type="text" id="crew-vessel-input" value="NOMADA" list="crew-vessel-list" />
              <datalist id="crew-vessel-list"></datalist>
            </div>
            <button type="button" class="dash-card-btn dash-card-btn--secondary" id="crew-vessel-load">
              <span data-lang="en">Load Roster</span><span data-lang="es">Cargar Tripulación</span>
            </button>
          </div>

          <input type="hidden" id="crew-csrf-field" value="<?= $lly_csrf ?>">

          <div class="catalog-subpanel">
            <h3><span data-lang="en">⚙️ Manage Positions (shared across all vessels)</span><span data-lang="es">⚙️ Administrar Puestos (compartido entre todas las embarcaciones)</span></h3>
            <div class="catalog-role-list" id="crew-role-list"></div>
            <form id="crew-role-form" class="ephemeral-form-row ephemeral-form-row--inline" style="margin:0;">
              <input type="hidden" id="crew-role-id" value="">
              <input type="text" id="crew-role-en" placeholder="Position (EN)" maxlength="80" required style="flex:1 1 160px;">
              <input type="text" id="crew-role-es" placeholder="Puesto (ES)" maxlength="80" required style="flex:1 1 160px;">
              <button type="submit" class="dash-card-btn" style="flex:0 0 auto;">
                <span data-lang="en">+ Add Position</span><span data-lang="es">+ Agregar Puesto</span>
              </button>
              <button type="button" class="dash-card-btn dash-card-btn--secondary" id="crew-role-cancel-btn" hidden style="flex:0 0 auto;">
                <span data-lang="en">Cancel</span><span data-lang="es">Cancelar</span>
              </button>
            </form>
            <p id="crew-role-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>
          </div>

          <div class="op-card">
            <form id="crew-member-form" class="ephemeral-form">
              <input type="hidden" id="crew-member-id" value="">
              <div class="ephemeral-form-row ephemeral-form-row--inline">
                <label for="crew-member-role"><span data-lang="en">Position</span><span data-lang="es">Puesto</span></label>
                <select id="crew-member-role" required></select>
                <label for="crew-member-status"><span data-lang="en">Status</span><span data-lang="es">Estatus</span></label>
                <select id="crew-member-status">
                  <option value="active" selected>Active / Activo</option>
                  <option value="inactive">Inactive / Inactivo</option>
                </select>
              </div>
              <div class="ephemeral-form-row">
                <label for="crew-member-name"><span data-lang="en">Full name</span><span data-lang="es">Nombre completo</span></label>
                <input type="text" id="crew-member-name" maxlength="150" required>
              </div>
              <div class="ephemeral-form-row ephemeral-form-row--inline">
                <label for="crew-member-phone"><span data-lang="en">Phone</span><span data-lang="es">Teléfono</span></label>
                <input type="tel" id="crew-member-phone" maxlength="30">
                <label for="crew-member-whatsapp">WhatsApp</label>
                <input type="tel" id="crew-member-whatsapp" maxlength="30">
              </div>
              <div class="ephemeral-form-row">
                <label for="crew-member-email">Email</label>
                <input type="email" id="crew-member-email" maxlength="190">
              </div>
              <div class="ephemeral-form-row">
                <label for="crew-member-note"><span data-lang="en">Note / description</span><span data-lang="es">Nota / descripción</span></label>
                <textarea id="crew-member-note" rows="2" maxlength="2000"></textarea>
              </div>
              <div class="ephemeral-form-row ephemeral-form-row--inline">
                <button type="submit" id="crew-member-submit-btn" class="dash-card-btn">
                  <span data-lang="en">💾 Save Crew Member</span><span data-lang="es">💾 Guardar Tripulante</span>
                </button>
                <button type="button" id="crew-member-cancel-btn" class="dash-card-btn dash-card-btn--secondary" hidden>
                  <span data-lang="en">Cancel Edit</span><span data-lang="es">Cancelar Edición</span>
                </button>
              </div>
            </form>
            <p id="crew-member-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>
          </div>

          <div class="table-wrap">
            <table class="data-table" id="crew-members-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Position</span><span data-lang="es">Puesto</span></th>
                  <th><span data-lang="en">Name</span><span data-lang="es">Nombre</span></th>
                  <th><span data-lang="en">Contact</span><span data-lang="es">Contacto</span></th>
                  <th><span data-lang="en">Status</span><span data-lang="es">Estatus</span></th>
                  <th><span data-lang="en">Actions</span><span data-lang="es">Acciones</span></th>
                </tr>
              </thead>
              <tbody id="crew-members-tbody">
                <tr><td colspan="5"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             VIEW — KITCHEN UTENSILS (per-vessel inventory catalog)
        ═══════════════════════════════════════════════════════════ -->
        <div id="view-inventory">

          <div class="catalog-vessel-bar">
            <div class="op-field">
              <label><span data-lang="en">Vessel / Catamaran</span><span data-lang="es">Embarcación / Catamarán</span></label>
              <input type="text" id="inv-vessel-input" value="NOMADA" list="inv-vessel-list" />
              <datalist id="inv-vessel-list"></datalist>
            </div>
            <button type="button" class="dash-card-btn dash-card-btn--secondary" id="inv-vessel-load">
              <span data-lang="en">Load Utensils</span><span data-lang="es">Cargar Utensilios</span>
            </button>
          </div>

          <input type="hidden" id="inv-csrf-field" value="<?= $lly_csrf ?>">

          <div class="op-card">
            <form id="inv-item-form" class="ephemeral-form">
              <input type="hidden" id="inv-item-id" value="">
              <div class="ephemeral-form-row ephemeral-form-row--inline">
                <label for="inv-item-name-en"><span data-lang="en">Item name (EN)</span><span data-lang="es">Artículo (EN)</span></label>
                <input type="text" id="inv-item-name-en" maxlength="120" required style="flex:1 1 200px;">
                <label for="inv-item-name-es"><span data-lang="en">Item name (ES)</span><span data-lang="es">Artículo (ES)</span></label>
                <input type="text" id="inv-item-name-es" maxlength="120" required style="flex:1 1 200px;">
                <button type="button" class="catalog-translate-btn" id="inv-item-translate-btn" title="Auto-translate the empty field / Auto-traducir el campo vacío">🌐</button>
              </div>
              <div class="ephemeral-form-row ephemeral-form-row--inline">
                <label for="inv-item-qty"><span data-lang="en">Quantity</span><span data-lang="es">Cantidad</span></label>
                <input type="number" id="inv-item-qty" min="0" max="999" value="1">
                <label for="inv-item-condition"><span data-lang="en">Condition</span><span data-lang="es">Condición</span></label>
                <select id="inv-item-condition">
                  <option value="good" selected>Good / Bien</option>
                  <option value="fair">Fair / Regular</option>
                  <option value="damaged">Damaged / Dañado</option>
                  <option value="missing">Missing / Falta</option>
                </select>
              </div>
              <div class="ephemeral-form-row">
                <label for="inv-item-note"><span data-lang="en">Note</span><span data-lang="es">Nota</span></label>
                <input type="text" id="inv-item-note" maxlength="255">
              </div>
              <div class="ephemeral-form-row ephemeral-form-row--inline">
                <button type="submit" id="inv-item-submit-btn" class="dash-card-btn">
                  <span data-lang="en">💾 Save Utensil</span><span data-lang="es">💾 Guardar Utensilio</span>
                </button>
                <button type="button" id="inv-item-cancel-btn" class="dash-card-btn dash-card-btn--secondary" hidden>
                  <span data-lang="en">Cancel Edit</span><span data-lang="es">Cancelar Edición</span>
                </button>
              </div>
            </form>
            <p id="inv-item-feedback" class="ephemeral-feedback" role="status" aria-live="polite"></p>
          </div>

          <div class="table-wrap">
            <table class="data-table" id="inv-items-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Item</span><span data-lang="es">Artículo</span></th>
                  <th><span data-lang="en">Qty</span><span data-lang="es">Cant.</span></th>
                  <th><span data-lang="en">Condition</span><span data-lang="es">Condición</span></th>
                  <th><span data-lang="en">Note</span><span data-lang="es">Nota</span></th>
                  <th><span data-lang="en">Actions</span><span data-lang="es">Acciones</span></th>
                </tr>
              </thead>
              <tbody id="inv-items-tbody">
                <tr><td colspan="5"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></td></tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </main>

  <!-- ═══════════════════════════════════════════════════════════════
       HISTORIAL DETAIL DIALOG
  ═══════════════════════════════════════════════════════════════ -->
  <dialog class="chapter-dialog" id="checklist-detail-dialog" aria-labelledby="checklist-detail-dialog-title">
    <div class="chapter-dialog-inner">
      <button type="button" class="chapter-dialog-close" id="checklist-detail-close" aria-label="Close">✕</button>
      <p class="chapter-dialog-eyebrow" id="checklist-detail-eyebrow"></p>
      <h2 class="chapter-dialog-title" id="checklist-detail-dialog-title"></h2>
      <div class="chapter-dialog-body" id="checklist-detail-body"></div>
    </div>
  </dialog>

  <div id="checklist-toast" class="lly-toast" role="status"></div>
  <div id="validation-alert" role="alert"></div>

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
        <span data-lang="en">Inventory Checklist · Internal Operations · Confidential</span>
        <span data-lang="es">Checklist de Inventario · Operaciones Internas · Confidencial</span>
      </p>
    </div>
  </footer>

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <!-- ═══════════════════════════════════════════════════════════════
       CHECKLIST CONTENT DATA — item catalog + render engine.
       Page-specific content/templating (mirrors how dashboard.php keeps
       its $lly_reportes/$lly_pagos arrays inline). Interactive behavior
       (tabs, status clicks, save, historial, detail modal) lives in
       assets/js/main.js §9c, same split as every other Cockpit panel.
  ═══════════════════════════════════════════════════════════════ -->
  <script>
  (function () {
    "use strict";

    function bl(en, es) { return '<span data-lang="en">' + en + '</span><span data-lang="es">' + es + '</span>'; }
    function ph(en, es) { return 'placeholder="' + en + '" data-ph-en="' + en + '" data-ph-es="' + es + '"'; }

    var DATA = [
      { id: 'kitchen', icon: '🍽️', title: { en: 'Kitchen / Galley', es: 'Cocina / Galera' }, type: 'items', items: [
        { en: 'Dinner plates (set of 12)', es: 'Platos (set de 12)', qty: 12 },
        { en: 'Cutlery set — forks, knives, spoons', es: 'Cubiertos — tenedores, cuchillos, cucharas', qty: 12 },
        { en: 'Drinking glasses', es: 'Vasos', qty: 12 },
        { en: 'Wine glasses', es: 'Copas de vino', qty: 12 },
        { en: 'Pots &amp; pans', es: 'Ollas y sartenes' },
        { en: 'Small appliances (blender, kettle, toaster, coffee maker)', es: 'Electrodomésticos (licuadora, hervidor, tostadora, cafetera)' },
        { en: 'Consumables (paper towels, dish soap, sponges, trash bags)', es: 'Consumibles (toallas de papel, jabón, esponjas, bolsas de basura)' },
        { en: 'Fire extinguisher — galley', es: 'Extintor — cocina', expiry: true }
      ]},
      { id: 'cabins', icon: '🛏️', title: { en: 'Bedrooms / Cabins', es: 'Camarotes / Cabinas' }, type: 'repeat',
        note: { en: 'Recommend keeping 2 full spare sets of linens &amp; towels on board. Cross out cabins not applicable to this vessel.', es: 'Se recomienda mantener 2 sets completos de repuesto de sábanas y toallas a bordo. Tache las cabinas que no apliquen a esta embarcación.' },
        units: [ { en: 'Cabin 1', es: 'Cabina 1' }, { en: 'Cabin 2', es: 'Cabina 2' }, { en: 'Cabin 3', es: 'Cabina 3' }, { en: 'Cabin 4', es: 'Cabina 4' } ],
        items: [
          { en: 'Mattress protectors', es: 'Protectores de colchón' },
          { en: 'Bed sheets — full set', es: 'Sábanas — set completo' },
          { en: 'Pillows &amp; pillowcases', es: 'Almohadas y fundas' },
          { en: 'Towels (2 per guest)', es: 'Toallas (2 por huésped)' },
          { en: 'Air conditioning — working', es: 'Aire acondicionado — funcionando' },
          { en: 'Reading lights / cabin lighting', es: 'Luces de lectura / iluminación de cabina' },
          { en: 'Odor / stain control — fresh, no mildew', es: 'Control de olores / manchas — fresco, sin moho' }
        ]},
      { id: 'bathrooms', icon: '🚿', title: { en: 'Bathrooms / Heads', es: 'Baños' }, type: 'repeat',
        units: [ { en: 'Head 1', es: 'Baño 1' }, { en: 'Head 2', es: 'Baño 2' }, { en: 'Head 3', es: 'Baño 3' }, { en: 'Head 4', es: 'Baño 4' } ],
        items: [
          { en: 'Toilet / flush mechanism', es: 'Inodoro / mecanismo de descarga' },
          { en: 'Drain / sump pump', es: 'Bomba de achique / drenaje' },
          { en: 'Hot water', es: 'Agua caliente' },
          { en: 'Bathroom consumables (soap, paper, shampoo)', es: 'Consumibles de baño (jabón, papel, shampoo)' },
          { en: 'Leak check — no visible leaks', es: 'Revisión de fugas — sin fugas visibles' }
        ]},
      { id: 'salon', icon: '🛋️', title: { en: 'Salon, Cockpit &amp; Guest Areas', es: 'Salón, Cockpit y Áreas de Huéspedes' }, type: 'items', items: [
        { en: 'Interior cushions', es: 'Cojines interiores' },
        { en: 'Exterior / cockpit cushions', es: 'Cojines exteriores / cockpit' },
        { en: 'Bluetooth audio system', es: 'Sistema de audio Bluetooth' },
        { en: 'TV &amp; remote controls', es: 'TV y controles remotos' },
        { en: 'Air conditioning — salon', es: 'Aire acondicionado — salón' },
        { en: 'Ice chest / cooler', es: 'Hielera' },
        { en: 'Set of keys (companionway, lazarette, etc.)', es: 'Juego de llaves (escotilla, lazareto, etc.)' },
        { en: 'Emergency card / safety briefing card visible', es: 'Tarjeta de emergencia / briefing de seguridad visible' }
      ]},
      { id: 'engine', icon: '⚙️', title: { en: 'Engine Rooms &amp; Mechanical', es: 'Sala de Máquinas y Mecánica' }, type: 'engine',
        compare: [
          { en: 'Engine oil level', es: 'Nivel de aceite del motor' },
          { en: 'Coolant level', es: 'Nivel de refrigerante' },
          { en: 'Fuel level', es: 'Nivel de combustible' },
          { en: 'Bilge — dry check', es: 'Sentina — revisión de sequedad' },
          { en: 'Belts condition', es: 'Estado de las bandas' },
          { en: 'Spare filters onboard', es: 'Filtros de repuesto a bordo' }
        ],
        items: [
          { en: 'Generator — operational check', es: 'Generador — revisión operativa' },
          { en: 'Watermaker / desalinator — operational check', es: 'Desalinizadora — revisión operativa' },
          { en: 'Freshwater tank level', es: 'Nivel de tanque de agua dulce' },
          { en: 'Black water tank level', es: 'Nivel de tanque de aguas negras' },
          { en: 'Grey water tank level', es: 'Nivel de tanque de aguas grises' },
          { en: 'Shore power cable — condition &amp; length', es: 'Cable de tierra — estado y longitud' }
        ]},
      { id: 'watertoys', icon: '🏄', title: { en: 'Water Toys', es: 'Juguetes Acuáticos' }, type: 'items', items: [
        { en: 'Paddleboards', es: 'Tablas de paddle' },
        { en: 'Paddles', es: 'Remos de paddle' },
        { en: 'Floating mat / lily pad', es: 'Colchón flotante / lily pad' },
        { en: 'Kayaks', es: 'Kayaks' },
        { en: 'Snorkel gear (masks &amp; snorkels)', es: 'Equipo de snorkel (visores y tubos)' },
        { en: 'Fins', es: 'Aletas' },
        { en: 'Adult life vests (water toy use)', es: 'Chalecos salvavidas adultos (uso recreativo)' },
        { en: 'Kids life vests (water toy use)', es: 'Chalecos salvavidas niños (uso recreativo)' },
        { en: 'Air pump', es: 'Bomba de aire' },
        { en: 'Repair kit', es: 'Kit de reparación' }
      ]},
      { id: 'tender', icon: '🚤', title: { en: 'Tender', es: 'Tender / Bote Auxiliar' }, type: 'items', items: [
        { en: 'Outboard engine — starts &amp; runs', es: 'Motor fuera de borda — enciende y funciona' },
        { en: 'Fuel tank + spare fuel', es: 'Tanque de combustible + repuesto' },
        { en: 'Kill-switch lanyard', es: 'Cordón de apagado (kill-switch)' },
        { en: 'Oars / paddles', es: 'Remos' },
        { en: 'Anchor', es: 'Ancla' },
        { en: 'Tender life vests', es: 'Chalecos salvavidas del tender' }
      ]},
      { id: 'deck', icon: '⚓', title: { en: 'Deck &amp; Docking Equipment', es: 'Cubierta y Equipo de Atraque' }, type: 'items', items: [
        { en: 'Main anchor &amp; windlass', es: 'Ancla principal y molinete' },
        { en: 'Secondary anchor', es: 'Ancla secundaria' },
        { en: 'Docking lines (min. 6)', es: 'Líneas de atraque (mín. 6)', qty: 6 },
        { en: 'Fenders (min. 6)', es: 'Defensas (mín. 6)', qty: 6 },
        { en: 'Boat hooks', es: 'Bicheros', qty: 2 },
        { en: 'Buckets', es: 'Cubetas', qty: 2 },
        { en: 'Swim ladder', es: 'Escalera de baño' },
        { en: 'Trampoline net', es: 'Red de trampolín' },
        { en: 'Navigation lights', es: 'Luces de navegación' },
        { en: 'Sails (if applicable)', es: 'Velas (si aplica)' }
      ]},
      { id: 'safety', icon: '🦺', title: { en: 'Safety &amp; Emergency Equipment', es: 'Equipo de Seguridad y Emergencia' }, type: 'items',
        compliance: { en: 'Confirm all equipment meets Port Captain (Capitanía de Puerto) / USCG requirements before departure.', es: 'Confirme que todo el equipo cumple con los requisitos de la Capitanía de Puerto / USCG antes de zarpar.' },
        items: [
          { en: 'Adult life vests — must match guest count', es: 'Chalecos salvavidas adultos — debe igualar el número de huéspedes', qty: 'guests', rowId: 'safety-adult-vests' },
          { en: 'Children life vests', es: 'Chalecos salvavidas para niños' },
          { en: 'Life raft — current inspection', es: 'Balsa salvavidas — inspección vigente', expiry: true },
          { en: 'Fire extinguishers — all stations', es: 'Extintores — todas las estaciones', expiry: true },
          { en: 'Flares', es: 'Bengalas', expiry: true },
          { en: 'First aid kit', es: 'Botiquín de primeros auxilios' },
          { en: 'VHF radio — fixed', es: 'Radio VHF — fijo' },
          { en: 'VHF radios — handheld', es: 'Radios VHF — portátiles' },
          { en: 'EPIRB', es: 'EPIRB' },
          { en: 'Smoke / CO detectors', es: 'Detectores de humo / CO' }
        ]}
    ];

    var FIELD_LABELS = {};
    var TAB_ORDER = [];

    function statusGroup() {
      return '<div class="status-group">' +
        '<button type="button" class="status-btn" data-value="good">✅ ' + bl('Good', 'Bien') + '</button>' +
        '<button type="button" class="status-btn" data-value="damaged">⚠️ ' + bl('Damaged', 'Dañado') + '</button>' +
        '<button type="button" class="status-btn" data-value="missing">❌ ' + bl('Missing', 'Falta') + '</button>' +
        '<button type="button" class="status-btn" data-value="replace">🔄 ' + bl('Replace/Refill', 'Reemplazar') + '</button>' +
      '</div>';
    }

    /** Renders one item row and records its field-id → label mapping for the Historial detail view. */
    function itemRow(fieldId, item, sectionTitle, unit) {
      FIELD_LABELS[fieldId] = { section: sectionTitle, item: { en: item.en.replace(/&amp;/g, '&'), es: item.es.replace(/&amp;/g, '&') }, unit: unit || null };

      var qtyText = item.qty && item.qty !== 'guests' ? (' / ' + item.qty) : '';
      var countPhEn = item.qty === 'guests' ? 'Count' : ('Count' + qtyText);
      var countPhEs = item.qty === 'guests' ? 'Cant.' : ('Cant.' + qtyText);
      var expiryHtml = item.expiry ? '<input type="date" class="item-expiry" ' + ph('Expiry date', 'Fecha de vencimiento') + '>' : '';
      var rowIdAttr = item.rowId ? ' id="' + item.rowId + '"' : '';

      return '' +
      '<div class="item-row" data-field-id="' + fieldId + '" data-status=""' + rowIdAttr + '>' +
        '<div class="item-label">' + bl(item.en, item.es) + '</div>' +
        '<div class="item-controls">' +
          statusGroup() +
          '<div class="input-group">' +
            '<input type="text" class="item-count" ' + ph(countPhEn, countPhEs) + '>' +
            expiryHtml +
            '<input type="text" class="item-notes" ' + ph('Notes', 'Notas') + '>' +
          '</div>' +
        '</div>' +
      '</div>';
    }

    function engineRow(fieldPrefix, index, item, sectionTitle) {
      var portId = fieldPrefix + '-' + index + '-port';
      var stbdId = fieldPrefix + '-' + index + '-stbd';
      FIELD_LABELS[portId] = { section: sectionTitle, item: { en: item.en + ' (Port)', es: item.es + ' (Babor)' }, unit: null };
      FIELD_LABELS[stbdId] = { section: sectionTitle, item: { en: item.en + ' (Starboard)', es: item.es + ' (Estribor)' }, unit: null };

      return '' +
      '<div class="engine-row">' +
        '<div class="item-label">' + bl(item.en, item.es) + '</div>' +
        '<div class="engine-side" data-field-id="' + portId + '" data-status="">' +
          '<div class="engine-side-label">⚓ ' + bl('Port (Babor)', 'Babor') + '</div>' +
          statusGroup() +
          '<div class="input-group" style="margin-top:.4rem;"><input type="text" class="item-notes" ' + ph('Notes', 'Notas') + '></div>' +
        '</div>' +
        '<div class="engine-side" data-field-id="' + stbdId + '" data-status="">' +
          '<div class="engine-side-label">⚓ ' + bl('Starboard (Estribor)', 'Estribor') + '</div>' +
          statusGroup() +
          '<div class="input-group" style="margin-top:.4rem;"><input type="text" class="item-notes" ' + ph('Notes', 'Notas') + '></div>' +
        '</div>' +
      '</div>';
    }

    function renderTabPanel(section, num) {
      TAB_ORDER.push(section.id);
      var body = '';

      if (section.type === 'items') {
        if (section.compliance) { body += '<div class="callout warn">⚠️ ' + bl(section.compliance.en, section.compliance.es) + '</div>'; }
        body += section.items.map(function (item, i) { return itemRow(section.id + '-' + i, item, section.title); }).join('');
      } else if (section.type === 'repeat') {
        if (section.note) { body += '<div class="callout info">💡 ' + bl(section.note.en, section.note.es) + '</div>'; }
        section.units.forEach(function (unit) {
          var slug = unit.en.replace(/\s+/g, '');
          body += '<div class="subgroup-title">' + bl(unit.en, unit.es) + '</div>';
          body += section.items.map(function (item, i) { return itemRow(section.id + '-' + slug + '-' + i, item, section.title, unit); }).join('');
        });
      } else if (section.type === 'engine') {
        body += '<div class="subgroup-title">' + bl('Port / Starboard Comparison', 'Comparativa Babor / Estribor') + '</div>';
        body += section.compare.map(function (item, i) { return engineRow(section.id + '-cmp', i, item, section.title); }).join('');
        body += '<div class="subgroup-title">' + bl('Additional Mechanical Systems', 'Sistemas Mecánicos Adicionales') + '</div>';
        body += section.items.map(function (item, i) { return itemRow(section.id + '-x-' + i, item, section.title); }).join('');
      }

      return '' +
      '<section class="tab-panel" id="panel-' + section.id + '" data-section-id="' + section.id + '"' + (num === 1 ? '' : ' hidden') + '>' +
        '<div class="tab-panel-header"><h2>' + section.icon + ' ' + bl(section.title.en, section.title.es) + '</h2></div>' +
        body +
        '<div class="tab-panel-nav">' +
          '<button type="button" class="dash-card-btn dash-card-btn--secondary checklist-prev">◀ ' + bl('Prev', 'Anterior') + '</button>' +
          '<button type="button" class="dash-card-btn checklist-next">' + bl('Next', 'Siguiente') + ' ▶</button>' +
        '</div>' +
      '</section>';
    }

    function renderSignoffPanel(num) {
      TAB_ORDER.push('signoff');
      var body = '';
      body += itemRow('signoff-0', { en: 'Ice restocked', es: 'Hielo reabastecido' }, { en: 'Final Sign-Off', es: 'Cierre Final' });
      body += itemRow('signoff-1', { en: 'Beverages / bar restocked', es: 'Bebidas / bar reabastecido' }, { en: 'Final Sign-Off', es: 'Cierre Final' });
      body += itemRow('signoff-2', { en: 'Damage photos taken', es: 'Fotos de daños tomadas' }, { en: 'Final Sign-Off', es: 'Cierre Final' });

      return '' +
      '<section class="tab-panel" id="panel-signoff" data-section-id="signoff" hidden>' +
        '<div class="tab-panel-header"><h2>✍️ ' + bl('Final Charter Sign-Off', 'Cierre Final del Charter') + '</h2></div>' +
        body +
        '<div class="final-grid">' +
          '<div class="final-field full"><label>' + bl('Missing items report', 'Reporte de artículos faltantes') + '</label>' +
            '<textarea id="final-missing-report" ' + ph('List any missing or damaged items in detail…', 'Detalle cualquier artículo faltante o dañado…') + '></textarea></div>' +
          '<div class="final-field full"><label>' + bl('Required actions / notes', 'Acciones requeridas / notas') + '</label>' +
            '<textarea id="final-actions" ' + ph('Follow-up actions for maintenance/crew…', 'Acciones de seguimiento para mantenimiento/tripulación…') + '></textarea></div>' +
        '</div>' +
        '<div class="final-grid" style="padding-top:0;">' +
          '<div class="sig-row" style="grid-column:1/-1;">' +
            '<div class="final-field" style="flex:1 1 220px;"><label id="sig-label">' + bl('Captain Signature (type full name)', 'Firma del Capitán (escriba su nombre completo)') + '</label>' +
              '<input type="text" id="captain-signature" ' + ph('Full name = signature', 'Nombre completo = firma') + '></div>' +
            '<div class="final-field" style="flex:1 1 140px;"><label>' + bl('Date', 'Fecha') + '</label><input type="date" id="sig-date"></div>' +
            '<div class="final-field" style="flex:1 1 120px;"><label>' + bl('Time', 'Hora') + '</label><input type="time" id="sig-time"></div>' +
          '</div>' +
        '</div>' +
        '<div class="tab-panel-nav">' +
          '<button type="button" class="dash-card-btn dash-card-btn--secondary checklist-prev">◀ ' + bl('Prev', 'Anterior') + '</button>' +
          '<span></span>' +
        '</div>' +
      '</section>';
    }

    var panelsHtml = '';
    var navHtml = '';
    DATA.forEach(function (section, idx) {
      var num = idx + 1;
      panelsHtml += renderTabPanel(section, num);
      navHtml += '<button type="button" class="checklist-tab' + (num === 1 ? ' active' : '') + '" data-tab-target="' + section.id + '">' +
        '<span class="n">' + num + '</span> ' + bl(section.title.en, section.title.es) +
        '<span class="badge" hidden></span></button>';
    });
    panelsHtml += renderSignoffPanel(DATA.length + 1);
    navHtml += '<button type="button" class="checklist-tab" data-tab-target="signoff">' +
      '<span class="n">' + (DATA.length + 1) + '</span> ' + bl('Sign-Off', 'Cierre') +
      '<span class="badge" hidden></span></button>';

    document.getElementById('checklist-panels').innerHTML = panelsHtml;
    document.getElementById('checklist-tabs').innerHTML = navHtml;

    // Consumed by assets/js/main.js §9c (tab switching, save serialize/restore, Historial detail rendering).
    window.LLY_CHECKLIST_FIELD_LABELS = FIELD_LABELS;
    window.LLY_CHECKLIST_TAB_ORDER = TAB_ORDER;
  })();
  </script>

  <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>

</body>
</html>
