<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — propuestas.php
 * The future & revisions: content validation, fleet data, roadmap and
 * the win-win alliance proposal. Standalone page — validates its own
 * session instead of relying on a gatekeeper-only constant.
 */

require_once __DIR__ . '/api/conexion.php';
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/dev_bypass.php';
require_once __DIR__ . '/core/FleetCatalogRepository.php';

if (!lly_is_authenticated()) {
    header('Location: index.php');
    exit;
}

/**
 * Fleet Catalog rows (ll_fleet_catalog — sql/005_create_ll_fleet_catalog.sql),
 * same table core/ProxyBridge.php reads for the AI's system prompt §2.
 * Degrades to the last-known-good static rows (never a blank accordion) if
 * the table isn't provisioned yet on this environment.
 */
$lly_fleet_rows = [];
try {
    $lly_fleet_rows = FleetCatalogRepository::listVerified(Conexion::getConnection());
} catch (\Throwable $e) {
    error_log('[PG-AI · propuestas] Fleet catalog unavailable: ' . $e->getMessage());
}

if ($lly_fleet_rows === []) {
    $lly_fleet_rows = [
        ['vessel_name' => 'CNR Maranatha 120', 'vessel_slug' => '/maranatha-120/', 'role_label_en' => 'Flagship', 'role_label_es' => 'Insignia', 'max_pax' => 50, 'rate_note_en' => '$TBC — Pending Review', 'rate_note_es' => '$POR DEFINIR — En revisión', 'status_pill' => 'pill-pink'],
        ['vessel_name' => 'Pink Lips', 'vessel_slug' => '/pink-lips/', 'role_label_en' => 'Signature', 'role_label_es' => 'Estrella', 'max_pax' => 20, 'rate_note_en' => '$TBC — Pending Review', 'rate_note_es' => '$POR DEFINIR — En revisión', 'status_pill' => 'pill-gold'],
        ['vessel_name' => 'Most Affordable Luxury', 'vessel_slug' => '/most-affordable-luxury-yacht-5/', 'role_label_en' => 'Available', 'role_label_es' => 'Disponible', 'max_pax' => 13, 'rate_note_en' => '$TBC — Pending Review', 'rate_note_es' => '$POR DEFINIR — En revisión', 'status_pill' => 'pill-green'],
    ];
}
$lly_fleet_pending = FleetCatalogRepository::TOTAL_FLEET_SIZE - count($lly_fleet_rows);
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Proposals: Content Validation, Fleet, Roadmap &amp; Alliance" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Proposals</title>
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

        <a href="index.php" class="topbar-logo" aria-label="Lover Lips Yachts — Owner Dashboard">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Proposals · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="index.php" class="topbar-back-btn">
            <span data-lang="en">⬅️ Back to Main Dashboard</span>
            <span data-lang="es">⬅️ Regresar al Dashboard Principal</span>
          </a>
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
         1. SOURCE OF TRUTH — CONTENT VALIDATION
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-truth" aria-labelledby="truth-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Content Validation</span>
          <span data-lang="es">Validación de Contenido</span>
        </p>
        <h1 class="section-title" id="truth-title">
          <span data-lang="en">Source of <em>Truth</em> — Master Knowledge Bases</span>
          <span data-lang="es">Archivo de la <em>Verdad</em> — Bases de Conocimiento Maestras</span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          Two living documents power your entire digital ecosystem. Review, edit and authorize the content below — once confirmed, this data trains your AI Chatbot and structures your new website, ensuring zero-deviation accuracy across web, WhatsApp and social media.
        </p>
        <p class="section-subtitle" data-lang="es">
          Dos documentos vivos impulsan todo tu ecosistema digital. Revisa, edita y autoriza el contenido a continuación — una vez confirmado, estos datos entrenan al Chatbot de IA y estructuran tu nueva web, garantizando precisión absoluta en web, WhatsApp y redes sociales.
        </p>

        <!-- ── Dual Knowledge CTA Cards ─────────────────────────────────── -->
        <div class="kbase-grid">

          <!-- Card 1: AI Chatbot Brain -->
          <div class="kbase-card kbase-card--pink">
            <div class="kbase-card-head">
              <div class="kbase-card-icon">🤖</div>
              <div>
                <p class="kbase-card-eyebrow" data-lang="en">AI Chatbot · Active Brain</p>
                <p class="kbase-card-eyebrow" data-lang="es">Chatbot IA · Cerebro Activo</p>
                <p class="kbase-card-label" data-lang="en">Chatbot Knowledge Base</p>
                <p class="kbase-card-label" data-lang="es">Base de Conocimiento del Chatbot</p>
              </div>
            </div>

            <h3 class="kbase-card-title" data-lang="en">AI Chatbot Knowledge Base</h3>
            <h3 class="kbase-card-title" data-lang="es">Base de Conocimiento del Chatbot IA</h3>

            <p class="kbase-card-desc" data-lang="en">
              This is the active brain of your AI Chatbot. <strong>Everything written here directly trains the AI</strong> to answer bookings, pricing, and policies autonomously on WhatsApp and your website — 24 hours a day, without human intervention.
            </p>
            <p class="kbase-card-desc" data-lang="es">
              Este es el cerebro activo de tu Chatbot de IA. <strong>Todo lo que se escriba aquí entrena directamente a la IA</strong> para responder reservas, precios y políticas de forma autónoma en WhatsApp y tu sitio web — las 24 horas, sin intervención humana.
            </p>

            <a
              href="https://docs.google.com/document/d/1R_j0Gg4_schjj_y5XJ42JXYzhken-FsO16N9o-3YlSk/edit?usp=sharing"
              class="kbase-btn kbase-btn--pink"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Open AI Chatbot Knowledge Base in Google Docs"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span data-lang="en">Open &amp; Authorize Chatbot Doc</span>
              <span data-lang="es">Abrir y Autorizar Doc. Chatbot</span>
            </a>

            <div class="kbase-card-note">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">Add comments, edit text, and mark items as approved. Once confirmed, we lock the document and load it into the AI system. No technical knowledge required.</span>
              <span data-lang="es">Agrega comentarios, edita el texto y marca elementos como aprobados. Una vez confirmado, bloqueamos el documento y lo cargamos al sistema de IA. No se requiere conocimiento técnico.</span>
            </div>
          </div>

          <!-- Card 2: New Website Architecture -->
          <div class="kbase-card kbase-card--gold">
            <div class="kbase-card-head">
              <div class="kbase-card-icon">🌐</div>
              <div>
                <p class="kbase-card-eyebrow" data-lang="en">New Website · Architecture &amp; Content</p>
                <p class="kbase-card-eyebrow" data-lang="es">Nueva Web · Arquitectura y Contenido</p>
                <p class="kbase-card-label" data-lang="en">Website Content Blueprint</p>
                <p class="kbase-card-label" data-lang="es">Plano de Contenido Web</p>
              </div>
            </div>

            <h3 class="kbase-card-title" data-lang="en">New Website Architecture &amp; Content</h3>
            <h3 class="kbase-card-title" data-lang="es">Arquitectura y Contenido de la Nueva Web</h3>

            <p class="kbase-card-desc" data-lang="en">
              Review, edit, and authorize <strong>the layout and information for your upcoming high-performance website upgrade</strong> here. This document defines every section, copy block, and design decision before development begins — your stamp of approval sets the build in motion.
            </p>
            <p class="kbase-card-desc" data-lang="es">
              Revisa, edita y autoriza <strong>el diseño y la información para tu próxima actualización web de alto rendimiento</strong> aquí. Este documento define cada sección, bloque de texto y decisión de diseño antes de que comience el desarrollo — tu sello de aprobación pone en marcha la construcción.
            </p>

            <a
              href="https://docs.google.com/document/d/1tKLcwzXrotltWXHCiXTqX_n_hq6bUfseGVD7fKtrYlE/edit?usp=sharing"
              class="kbase-btn kbase-btn--gold"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="Open New Website Architecture document in Google Docs"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span data-lang="en">Open &amp; Authorize Website Doc</span>
              <span data-lang="es">Abrir y Autorizar Doc. Web</span>
            </a>

            <div class="kbase-card-note">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">Your approvals on this document are the green light for the new website build. Simply read through, suggest changes, and signal approval — we handle all the technical execution.</span>
              <span data-lang="es">Tus aprobaciones en este documento son la luz verde para construir el nuevo sitio web. Simplemente léelo, sugiere cambios y da tu aprobación — nosotros nos encargamos de toda la ejecución técnica.</span>
            </div>
          </div>

        </div><!-- /kbase-grid -->

        <!-- ── NEW: WhatsApp Master Doc Callout ─────────────────────────── -->
        <div class="whatsapp-callout-grid">
          <div class="whatsapp-callout col-xs-12">
            <div class="whatsapp-callout-icon">💬</div>
            <div class="whatsapp-callout-body">
              <p class="whatsapp-callout-eyebrow" data-lang="en">New · Fill This In Next</p>
              <p class="whatsapp-callout-eyebrow" data-lang="es">Nuevo · Llena Esto a Continuación</p>
              <h3 class="whatsapp-callout-title" data-lang="en">WhatsApp Master Answer Document</h3>
              <h3 class="whatsapp-callout-title" data-lang="es">Documento Maestro de Respuestas WhatsApp</h3>
              <p class="whatsapp-callout-desc" data-lang="en">
                To configure your AI Chatbot for WhatsApp, we need your answers in one master document. Fill it in whenever you have a few minutes — there's no deadline pressure, just fill it in as you go.
              </p>
              <p class="whatsapp-callout-desc" data-lang="es">
                Para configurar tu Chatbot de IA en WhatsApp, necesitamos tus respuestas en un documento maestro. Llénalo cuando tengas unos minutos — no hay presión de fecha límite, solo ve completándolo poco a poco.
              </p>
              <a
                href="https://docs.google.com/document/d/1LM_0XNY3iVb48OamUWcWrk4PkvI31A_fB6Mt-cDU8TU/edit?usp=sharing"
                class="kbase-btn kbase-btn--pink"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="Open the WhatsApp Master Answer Document in Google Docs"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span data-lang="en">📝 Set Up Your WhatsApp Chatbot Answers</span>
                <span data-lang="es">📝 Configurar Respuestas del Chatbot de WhatsApp</span>
              </a>
            </div>
          </div>
        </div><!-- /whatsapp-callout-grid -->

        <!-- ══ REPORT E — Organic Marketing Strategy (under review) ═══════ -->
        <article class="report-card report-card--strategic" id="report-e">
          <div class="report-card-head">
            <div class="report-number report-number--gold">E</div>
            <div class="report-card-meta">
              <p class="report-date">
                <span data-lang="en">June 22, 2026</span>
                <span data-lang="es">22 de Junio, 2026</span>
              </p>
              <span class="pill pill-orange">
                <span data-lang="en">Under Review</span>
                <span data-lang="es">En Revisión</span>
              </span>
            </div>
          </div>
          <p class="report-tag">
            <span data-lang="en">Business Strategy · Proposed Organic Marketing Setup</span>
            <span data-lang="es">Estrategia de Negocio · Estrategia Propuesta en Desarrollo</span>
          </p>
          <h3 class="report-title">
            <span data-lang="en">"Nine Lives" Organic Launch Campaign</span>
            <span data-lang="es">Campaña de Lanzamiento Orgánico de "Nine Lives"</span>
          </h3>

          <div class="report-strategic-inner">
            <div class="report-strategic-cover">
              <img src="assets/img/nine_live.png" alt="Nine Lives. One True Love — book cover" loading="lazy" />
            </div>
            <div class="report-body">
              <p data-lang="en">
                A 90-day, zero-paid-media guerrilla digital campaign for the September 2, 2026 book launch — aesthetic funnel, transactional SEO bridges, and three ready-to-publish bilingual copy templates. Full research, copy and AI conversion rules are detailed in the dedicated strategy presentation below, under your review and authorization.
              </p>
              <p data-lang="es">
                Una campaña de guerrilla digital de 90 días, sin pauta pagada, para el lanzamiento del libro el 2 de septiembre de 2026 — embudo estético, puentes de SEO transaccional y tres plantillas de copy bilingües listas para publicar. La investigación completa, el copy y las reglas de conversión de la IA están detalladas en la presentación dedicada de abajo, bajo tu revisión y autorización.
              </p>
              <a href="strategy.php" class="report-strategic-gold-btn">
                <span data-lang="en">Review Proposed Marketing Strategy</span>
                <span data-lang="es">Revisar Estrategia de Marketing Propuesta</span>
              </a>
            </div>
          </div>
        </article>

        <!-- ── AI Accuracy Note ─────────────────────────────────────────── -->
        <div class="truth-intro">
          <span class="truth-intro-icon">🤖</span>
          <p class="truth-intro-text" data-lang="en">
            <strong>Why these documents matter:</strong> The AI Chatbot can only be as accurate as the data it learns from. Every piece of information you approve in these documents will be ingested as immutable facts — your virtual concierge will cite these figures and policies with zero deviation, autonomously across all channels.
          </p>
          <p class="truth-intro-text" data-lang="es">
            <strong>Por qué estos documentos importan:</strong> El Chatbot de IA solo puede ser tan preciso como los datos que aprende. Cada pieza de información que apruebes en estos documentos se ingresará como hechos inmutables — tu conserje virtual citará estas cifras y políticas con cero desviación, de forma autónoma en todos los canales.
          </p>
        </div>

        <!-- ── Data Accordions ──────────────────────────────────────────── -->
        <div class="accordion" role="list">

          <!-- Accordion 1: Flagship Fleet -->
          <div class="accordion-item open" role="listitem">
            <button class="accordion-trigger" aria-expanded="true">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">⚓</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Flagship Fleet — Key Vessels</p>
                  <p class="accordion-trigger-title" data-lang="es">Flota Insignia — Embarcaciones Clave</p>
                  <p class="accordion-trigger-sub" data-lang="en"><?= count($lly_fleet_rows) ?> of <?= FleetCatalogRepository::TOTAL_FLEET_SIZE ?> vessels · Rate structure · Capacity</p>
                  <p class="accordion-trigger-sub" data-lang="es"><?= count($lly_fleet_rows) ?> de <?= FleetCatalogRepository::TOTAL_FLEET_SIZE ?> embarcaciones · Estructura de tarifas · Capacidad</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <div class="table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th data-lang="en">Vessel</th>
                      <th data-lang="es">Embarcación</th>
                      <th data-lang="en">Capacity</th>
                      <th data-lang="es">Capacidad</th>
                      <th data-lang="en">Rate / Hour (approx.)</th>
                      <th data-lang="es">Tarifa / Hora (aprox.)</th>
                      <th data-lang="en">Status</th>
                      <th data-lang="es">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($lly_fleet_rows as $lly_vessel): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars((string) $lly_vessel['vessel_name'], ENT_QUOTES, 'UTF-8') ?></strong><?php if (!empty($lly_vessel['vessel_slug'])): ?><br><small><?= htmlspecialchars((string) $lly_vessel['vessel_slug'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></td>
                      <td data-lang="en"><?= $lly_vessel['max_pax'] !== null ? 'Up to ' . (int) $lly_vessel['max_pax'] . ' guests' : 'Capacity TBC' ?></td>
                      <td data-lang="es"><?= $lly_vessel['max_pax'] !== null ? 'Hasta ' . (int) $lly_vessel['max_pax'] . ' personas' : 'Capacidad por definir' ?></td>
                      <td data-lang="en"><?= htmlspecialchars((string) $lly_vessel['rate_note_en'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td data-lang="es"><?= htmlspecialchars((string) $lly_vessel['rate_note_es'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td data-lang="en"><span class="pill <?= htmlspecialchars((string) $lly_vessel['status_pill'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $lly_vessel['role_label_en'], ENT_QUOTES, 'UTF-8') ?></span></td>
                      <td data-lang="es"><span class="pill <?= htmlspecialchars((string) $lly_vessel['status_pill'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $lly_vessel['role_label_es'], ENT_QUOTES, 'UTF-8') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if ($lly_fleet_pending > 0): ?>
                    <tr>
                      <td colspan="2" data-lang="en" class="u-italic">+ <?= $lly_fleet_pending ?> additional vessels pending data lift from WordPress (Phase 2 deliverable)</td>
                      <td colspan="2" data-lang="es" class="u-italic">+ <?= $lly_fleet_pending ?> embarcaciones adicionales pendientes de extracción de WordPress (entregable Fase 2)</td>
                      <td></td><td></td>
                    </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Accordion 2: Rates & Packages -->
          <div class="accordion-item" role="listitem">
            <button class="accordion-trigger" aria-expanded="false">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">💰</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Rates, Packages &amp; Inclusions</p>
                  <p class="accordion-trigger-title" data-lang="es">Tarifas, Paquetes e Incluidos</p>
                  <p class="accordion-trigger-sub" data-lang="en">Pricing tiers · What's included · Extras</p>
                  <p class="accordion-trigger-sub" data-lang="es">Niveles de precio · Qué incluye · Extras</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <div class="table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th data-lang="en">Package</th>
                      <th data-lang="es">Paquete</th>
                      <th data-lang="en">Duration</th>
                      <th data-lang="es">Duración</th>
                      <th data-lang="en">Inclusions</th>
                      <th data-lang="es">Incluye</th>
                      <th data-lang="en">Approval</th>
                      <th data-lang="es">Aprobación</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td data-lang="en"><strong>Sunset Cruise</strong></td>
                      <td data-lang="es"><strong>Crucero al Atardecer</strong></td>
                      <td data-lang="en">3 hours</td>
                      <td data-lang="es">3 horas</td>
                      <td data-lang="en">Captain + First Mate, Water, Music</td>
                      <td data-lang="es">Capitán + Tripulación, Agua, Música</td>
                      <td data-lang="en"><span class="pill pill-orange">Pending</span></td>
                      <td data-lang="es"><span class="pill pill-orange">Pendiente</span></td>
                    </tr>
                    <tr>
                      <td data-lang="en"><strong>Full Day Charter</strong></td>
                      <td data-lang="es"><strong>Día Completo</strong></td>
                      <td data-lang="en">8 hours</td>
                      <td data-lang="es">8 horas</td>
                      <td data-lang="en">Captain, Crew, Snorkel gear, Catering optional</td>
                      <td data-lang="es">Capitán, Tripulación, Snorkel, Catering opcional</td>
                      <td data-lang="en"><span class="pill pill-orange">Pending</span></td>
                      <td data-lang="es"><span class="pill pill-orange">Pendiente</span></td>
                    </tr>
                    <tr>
                      <td data-lang="en"><strong>VIP Private Event</strong></td>
                      <td data-lang="es"><strong>Evento Privado VIP</strong></td>
                      <td data-lang="en">Custom</td>
                      <td data-lang="es">Personalizado</td>
                      <td data-lang="en">Full crew, DJ, Premium bar, Photography</td>
                      <td data-lang="es">Tripulación completa, DJ, Bar premium, Fotografía</td>
                      <td data-lang="en"><span class="pill pill-orange">Pending</span></td>
                      <td data-lang="es"><span class="pill pill-orange">Pendiente</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Accordion 3: Navigation Policies -->
          <div class="accordion-item" role="listitem">
            <button class="accordion-trigger" aria-expanded="false">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">📋</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Navigation Policies &amp; Rules</p>
                  <p class="accordion-trigger-title" data-lang="es">Políticas y Reglas de Navegación</p>
                  <p class="accordion-trigger-sub" data-lang="en">Booking terms · Cancellation · Safety rules</p>
                  <p class="accordion-trigger-sub" data-lang="es">Términos de reserva · Cancelación · Seguridad</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <ul class="policy-list">
                <li data-lang="en">Minimum booking: 3 hours for any vessel. Full-day and event charters require 30% deposit at confirmation.</li>
                <li data-lang="es">Reserva mínima: 3 horas para cualquier embarcación. Charters de día completo y eventos requieren 30% de anticipo al confirmar.</li>
                <li data-lang="en">Cancellation policy: 100% refund if cancelled 72+ hours before departure. 50% refund 24–72 hours prior. No refund under 24 hours.</li>
                <li data-lang="es">Política de cancelación: 100% de reembolso si se cancela con 72+ horas de anticipación. 50% entre 24–72 horas. Sin reembolso en menos de 24 horas.</li>
                <li data-lang="en">All charters depart from [MARINA — PENDING AUTHORIZATION]. Boarding 15 minutes before scheduled departure.</li>
                <li data-lang="es">Todos los charters parten desde [MARINA — PENDIENTE DE AUTORIZACIÓN]. Abordaje 15 minutos antes de la hora acordada.</li>
                <li data-lang="en">Alcohol is permitted on board. Outside catering allowed with prior notice. No glass bottles on deck.</li>
                <li data-lang="es">Se permite alcohol a bordo. Catering externo permitido con aviso previo. Sin botellas de vidrio en cubierta.</li>
                <li data-lang="en">Captain reserves the right to modify or cancel the route due to weather conditions — guest safety is always the priority.</li>
                <li data-lang="es">El capitán se reserva el derecho de modificar o cancelar la ruta por condiciones climáticas — la seguridad del pasajero es siempre la prioridad.</li>
              </ul>
              <p class="warning-note" data-lang="en">⚠️ All policies require your final written authorization before being loaded into the AI knowledge base.</p>
              <p class="warning-note" data-lang="es">⚠️ Todas las políticas requieren tu autorización escrita final antes de cargarse a la base de conocimiento de la IA.</p>
            </div>
          </div>

          <!-- Accordion 4: Brand Identity -->
          <div class="accordion-item" role="listitem">
            <button class="accordion-trigger" aria-expanded="false">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">🎨</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Brand Identity &amp; Tone of Voice</p>
                  <p class="accordion-trigger-title" data-lang="es">Identidad de Marca y Tono de Voz</p>
                  <p class="accordion-trigger-sub" data-lang="en">Visual standards · Language guidelines · AI persona</p>
                  <p class="accordion-trigger-sub" data-lang="es">Estándares visuales · Guías de lenguaje · Personalidad IA</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <div class="table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th data-lang="en">Attribute</th>
                      <th data-lang="es">Atributo</th>
                      <th data-lang="en">Definition</th>
                      <th data-lang="es">Definición</th>
                      <th data-lang="en">Status</th>
                      <th data-lang="es">Estado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td data-lang="en"><strong>Primary Color</strong></td>
                      <td data-lang="es"><strong>Color Principal</strong></td>
                      <td><span class="swatch-pink">#FF007F — Lover Pink</span></td>
                      <td><span class="swatch-pink">#FF007F — Lover Pink</span></td>
                      <td data-lang="en"><span class="pill pill-green">Confirmed</span></td>
                      <td data-lang="es"><span class="pill pill-green">Confirmado</span></td>
                    </tr>
                    <tr>
                      <td data-lang="en"><strong>Accent Color</strong></td>
                      <td data-lang="es"><strong>Color Acento</strong></td>
                      <td><span class="swatch-gold">#D4AF37 — Champagne Gold</span></td>
                      <td><span class="swatch-gold">#D4AF37 — Champagne Gold</span></td>
                      <td data-lang="en"><span class="pill pill-green">Confirmed</span></td>
                      <td data-lang="es"><span class="pill pill-green">Confirmado</span></td>
                    </tr>
                    <tr>
                      <td data-lang="en"><strong>Tone of Voice</strong></td>
                      <td data-lang="es"><strong>Tono de Voz</strong></td>
                      <td data-lang="en">Warm, sophisticated, aspirational — never cold or corporate</td>
                      <td data-lang="es">Cálido, sofisticado, aspiracional — nunca frío ni corporativo</td>
                      <td data-lang="en"><span class="pill pill-orange">Pending</span></td>
                      <td data-lang="es"><span class="pill pill-orange">Pendiente</span></td>
                    </tr>
                    <tr>
                      <td data-lang="en"><strong>AI Chatbot Name</strong></td>
                      <td data-lang="es"><strong>Nombre del Chatbot IA</strong></td>
                      <td data-lang="en">TBD — Pending owner decision</td>
                      <td data-lang="es">Por definir — Decisión pendiente del propietario</td>
                      <td data-lang="en"><span class="pill pill-orange">Pending</span></td>
                      <td data-lang="es"><span class="pill pill-orange">Pendiente</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

        </div><!-- /accordion -->
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         2. MASTER PROJECT TIMELINE — General Roadmap
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-timeline" aria-labelledby="timeline-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Strategic Roadmap</span>
          <span data-lang="es">Hoja de Ruta Estratégica</span>
        </p>
        <h2 class="section-title" id="timeline-title">
          <span data-lang="en">Master Project <em>Timeline</em></span>
          <span data-lang="es">Línea de Tiempo <em>Maestra</em></span>
        </h2>
        <p class="section-subtitle" data-lang="en">
          Six sequential phases that build the complete Lover Lips Yachts digital ecosystem — from stabilization to full omnichannel AI automation.
        </p>
        <p class="section-subtitle" data-lang="es">
          Seis fases secuenciales que construyen el ecosistema digital completo de Lover Lips Yachts — desde la estabilización hasta la automatización omnicanal con IA.
        </p>

        <div class="timeline" role="list">

          <div class="timeline-item active-phase" role="listitem">
            <div class="timeline-node" aria-label="Phase 1">1</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Phase 1 · In Progress</p>
              <p class="timeline-phase-tag" data-lang="es">Fase 1 · En Curso</p>
              <h3 class="timeline-title" data-lang="en">WordPress Optimization &amp; Pending Closure</h3>
              <h3 class="timeline-title" data-lang="es">Optimización de WordPress y Cierre de Pendientes</h3>
              <p class="timeline-desc" data-lang="en">Stabilization, deep cleanup and error correction on the live WordPress platform. This includes resolving all FOUC rendering issues, restoring down pages, cleaning gallery metadata, and ensuring 100% uptime and visual consistency across all 42 vessel pages before any new development begins.</p>
              <p class="timeline-desc" data-lang="es">Estabilización, limpieza profunda y corrección de errores en la plataforma WordPress actual en vivo. Incluye resolver todos los problemas de renderizado FOUC, restaurar páginas caídas, limpiar metadatos de galerías y garantizar disponibilidad y consistencia visual al 100% en las 42 páginas de embarcaciones antes de iniciar cualquier desarrollo nuevo.</p>
              <p class="timeline-status status-active" data-lang="en">● Active — Ongoing</p>
              <p class="timeline-status status-active" data-lang="es">● Activo — En ejecución</p>
            </div>
          </div>

          <div class="timeline-item active-phase" role="listitem">
            <div class="timeline-node" aria-label="Phase 2">2</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Phase 2 · In Progress</p>
              <p class="timeline-phase-tag" data-lang="es">Fase 2 · En Curso</p>
              <h3 class="timeline-title" data-lang="en">Information Lift &amp; Data Structuring</h3>
              <h3 class="timeline-title" data-lang="es">Levantamiento de Información y Estructuración de Datos</h3>
              <p class="timeline-desc" data-lang="en">Extraction and consolidation of all content from the current WordPress site: brand identity manual, complete catalog of all 42 fleet vessels (rates, capacities, photos, descriptions), navigation policies, and all API contracts needed to greenlight the new development. This phase produces the authorized "Source of Truth" that feeds the AI system.</p>
              <p class="timeline-desc" data-lang="es">Extracción y consolidación de todo el contenido del sitio WordPress actual: manual de identidad de marca, catálogo completo de las 42 embarcaciones de la flota (tarifas, capacidades, fotos, descripciones), políticas de navegación y todos los contratos de API necesarios para dar el banderazo al nuevo desarrollo. Esta fase produce el "Archivo de la Verdad" autorizado que alimenta al sistema de IA.</p>
              <p class="timeline-status status-active" data-lang="en">● Active — Data Extraction Underway</p>
              <p class="timeline-status status-active" data-lang="es">● Activo — Extracción de datos en curso</p>
            </div>
          </div>

          <div class="timeline-item" role="listitem">
            <div class="timeline-node" aria-label="Phase 3">3</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Phase 3 · Upcoming</p>
              <p class="timeline-phase-tag" data-lang="es">Fase 3 · Próxima</p>
              <h3 class="timeline-title" data-lang="en">New Website &amp; AI Chatbot Creation &amp; Deployment</h3>
              <h3 class="timeline-title" data-lang="es">Creación y Despliegue de la Nueva Web y Chatbot de IA</h3>
              <p class="timeline-desc" data-lang="en">Custom development of a lightweight, immersive architecture tailored exclusively to Lover Lips Yachts — no generic templates. Simultaneously, the AI Chatbot agent will be trained on the authorized Source of Truth data, enabling it to handle bookings, answer fleet questions and close leads autonomously across web, WhatsApp and social media.</p>
              <p class="timeline-desc" data-lang="es">Desarrollo a medida de una arquitectura ligera e inmersiva diseñada exclusivamente para Lover Lips Yachts — sin plantillas genéricas. Simultáneamente, el agente Chatbot de IA será entrenado con el Archivo de la Verdad autorizado, habilitándolo para gestionar reservas, responder preguntas de flota y cerrar leads de forma autónoma a través de web, WhatsApp y redes sociales.</p>
              <p class="timeline-status status-pending" data-lang="en">○ Pending Phase 2 Completion</p>
              <p class="timeline-status status-pending" data-lang="es">○ Pendiente — Espera Fase 2</p>
            </div>
          </div>

          <div class="timeline-item" role="listitem">
            <div class="timeline-node" aria-label="Phase 4">4</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Phase 4 · Upcoming</p>
              <p class="timeline-phase-tag" data-lang="es">Fase 4 · Próxima</p>
              <h3 class="timeline-title" data-lang="en">Integrated Audiovisual Content Production</h3>
              <h3 class="timeline-title" data-lang="es">Producción de Contenido Audiovisual Integrado</h3>
              <p class="timeline-desc" data-lang="en">Creation and editing of premium video content purpose-built for the new platform — yacht tours, testimonials, lifestyle reels and social media assets. All content will be engineered for both web performance (fast-loading, immersive) and social media traction.</p>
              <p class="timeline-desc" data-lang="es">Creación y edición de contenido de video premium diseñado específicamente para la nueva plataforma — recorridos por los yates, testimonios, reels de estilo de vida y material para redes sociales. Todo el contenido estará optimizado para rendimiento web y tracción en redes sociales.</p>
              <p class="timeline-status status-pending" data-lang="en">○ Pending Phase 3 Completion</p>
              <p class="timeline-status status-pending" data-lang="es">○ Pendiente — Espera Fase 3</p>
            </div>
          </div>

          <div class="timeline-item" role="listitem">
            <div class="timeline-node" aria-label="Phase 5">5</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Phase 5 · Upcoming</p>
              <p class="timeline-phase-tag" data-lang="es">Fase 5 · Próxima</p>
              <h3 class="timeline-title" data-lang="en">Internal Management &amp; Corporate Control System</h3>
              <h3 class="timeline-title" data-lang="es">Sistema Interno de Gestión y Control Corporativo</h3>
              <p class="timeline-desc" data-lang="en">Implementation of a bespoke software backend: appointment and charter scheduling, encrypted payment gateway integration (PCI-compliant), real-time fleet availability calendar, and advanced SEO tooling — all from a single private admin panel.</p>
              <p class="timeline-desc" data-lang="es">Implementación de un software backend a medida: gestión de citas y programación de charters, integración de pasarelas de pago encriptadas (PCI-compliant), calendario de disponibilidad en tiempo real y herramientas avanzadas de SEO — todo desde un único panel de administración privado.</p>
              <p class="timeline-status status-pending" data-lang="en">○ Scheduled — Post Phase 4</p>
              <p class="timeline-status status-pending" data-lang="es">○ Programado — Posterior a Fase 4</p>
            </div>
          </div>

          <div class="timeline-item" role="listitem">
            <div class="timeline-node" aria-label="Phase 6">6</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Phase 6 · Vision</p>
              <p class="timeline-phase-tag" data-lang="es">Fase 6 · Visión</p>
              <h3 class="timeline-title" data-lang="en">Omnichannel Marketing Automation Ecosystem</h3>
              <h3 class="timeline-title" data-lang="es">Ecosistema de Automatización de Marketing Omnicanal</h3>
              <p class="timeline-desc" data-lang="en">Total interconnection of all channels: website, Instagram, Facebook, TikTok, WhatsApp Business and Google Ads — unified under a single AI-powered intelligence layer. The AI will proactively qualify, nurture and close the majority of inbound leads autonomously, with human escalation protocols for VIP requests.</p>
              <p class="timeline-desc" data-lang="es">Interconexión total de todos los canales: sitio web, Instagram, Facebook, TikTok, WhatsApp Business y Google Ads — unificados bajo una única capa de inteligencia impulsada por IA. La IA calificará, nutrirá y cerrará de forma autónoma la mayoría de los leads entrantes, con protocolos de escalada humana para solicitudes VIP.</p>
              <p class="timeline-status status-pending" data-lang="en">○ Vision Phase — Full Ecosystem Launch</p>
              <p class="timeline-status status-pending" data-lang="es">○ Fase de Visión — Lanzamiento del Ecosistema Completo</p>
            </div>
          </div>

        </div><!-- /timeline -->
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         3. TECH EVOLUTION & WIN-WIN PARTNERSHIP PROPOSAL
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-proposal" aria-labelledby="proposal-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Tech Partnership</span>
          <span data-lang="es">Alianza Tecnológica</span>
        </p>
        <h2 class="section-title" id="proposal-title">
          <span data-lang="en">Tech Evolution &amp; Win-Win Partnership <em>Proposal</em></span>
          <span data-lang="es">Evolución Tecnológica y Alianza <em>Ganar-Ganar</em></span>
        </h2>
        <p class="section-subtitle" data-lang="en">
          A phased, custom-tailored digital re-engineering strategy — built exclusively for Lover Lips Yachts. Each phase delivers measurable results while protecting cash flow through a hybrid cash &amp; trade-exchange model. Completed and settled work lives on the <a href="alianzas.php">Alliance account statement</a>.
        </p>
        <p class="section-subtitle" data-lang="es">
          Una estrategia de re-ingeniería digital por fases, diseñada exclusivamente para Lover Lips Yachts. Cada fase entrega resultados medibles mientras protege el flujo de caja mediante un modelo híbrido de efectivo e intercambio comercial. El trabajo ya completado y liquidado vive en el <a href="alianzas.php">estado de cuenta de Alianza</a>.
        </p>

        <!-- ══ PHASE 1 ═════════════════════════════════════════════════════ -->
        <div class="proposal-phase">

          <div class="proposal-phase-header proposal-phase-header--active">
            <div class="proposal-phase-num proposal-phase-num--pink">1</div>
            <div class="proposal-phase-meta">
              <p class="proposal-phase-tag proposal-phase-tag--pink" data-lang="en">Phase 1 · AI Chatbot &amp; WhatsApp Dashboard</p>
              <p class="proposal-phase-tag proposal-phase-tag--pink" data-lang="es">Fase 1 · Chatbot IA &amp; Panel de WhatsApp</p>
              <h3 class="proposal-phase-title" data-lang="en">AI Chatbot "Active Brain" &amp; WhatsApp Command Center</h3>
              <h3 class="proposal-phase-title" data-lang="es">Chatbot IA "Cerebro Activo" y Panel de Control de WhatsApp</h3>
            </div>
            <span class="proposal-status-badge proposal-status-badge--active">
              <span data-lang="en">Awaiting Reconciliation</span>
              <span data-lang="es">Por Conciliar</span>
            </span>
          </div>

          <p class="proposal-phase-desc" data-lang="en">This module will be injected directly into your current WordPress site as a floating high-end concierge widget while the new ecosystem is being built, routing conversations straight into an intelligent WhatsApp pipeline. The AI operates under the strict corporate directive: <strong>NO_PRICE_WITHOUT_LEAD_DATA</strong> — answering complex operational FAQs but only delivering precise quotes after capturing: Desired Date, Guest Count, and Chosen Route. You receive only high-priority, qualified leads.</p>
          <p class="proposal-phase-desc" data-lang="es">Este módulo se inyectará directamente en tu WordPress actual como un widget flotante de conserjería de alto nivel mientras se construye el nuevo ecosistema, enrutando las conversaciones directamente a un pipeline inteligente de WhatsApp. La IA opera bajo la directriz corporativa estricta: <strong>SIN_PRECIO_SIN_DATOS_DEL_LEAD</strong> — respondiendo FAQs operativas complejas pero entregando cotizaciones precisas solo tras capturar: Fecha Deseada, Número de Invitados y Ruta Elegida.</p>

          <!-- Roadmap -->
          <p class="proposal-sub-label">
            <span data-lang="en">Development Roadmap</span>
            <span data-lang="es">Calendario de Trabajo</span>
          </p>
          <div class="proposal-roadmap">
            <div class="proposal-roadmap-step">
              <div class="proposal-roadmap-marker">
                <span class="proposal-roadmap-dot proposal-roadmap-dot--pink"></span>
                <span class="proposal-roadmap-line"></span>
              </div>
              <div class="proposal-roadmap-body">
                <p class="proposal-roadmap-period" data-lang="en">Week 1</p>
                <p class="proposal-roadmap-period" data-lang="es">Semana 1</p>
                <h4 class="proposal-roadmap-title" data-lang="en">Data Ingestion &amp; Core Training</h4>
                <h4 class="proposal-roadmap-title" data-lang="es">Ingesta de Datos y Entrenamiento Base</h4>
                <p class="proposal-roadmap-desc" data-lang="en">Feeding the AI engine with the official "Master Source of Truth" — 42 vessels specifications, transparent pricing grids, and regional regulations.</p>
                <p class="proposal-roadmap-desc" data-lang="es">Alimentación del motor de IA con el Documento Maestro — especificaciones de los 42 yates, tarifas transparentes y reglas operativas.</p>
              </div>
            </div>
            <div class="proposal-roadmap-step">
              <div class="proposal-roadmap-marker">
                <span class="proposal-roadmap-dot proposal-roadmap-dot--pink"></span>
                <span class="proposal-roadmap-line"></span>
              </div>
              <div class="proposal-roadmap-body">
                <p class="proposal-roadmap-period" data-lang="en">Week 2</p>
                <p class="proposal-roadmap-period" data-lang="es">Semana 2</p>
                <h4 class="proposal-roadmap-title" data-lang="en">ManyChat &amp; WordPress Integration</h4>
                <h4 class="proposal-roadmap-title" data-lang="es">Integración ManyChat y WordPress</h4>
                <p class="proposal-roadmap-desc" data-lang="en">Configuring the omni-channel conversational flows and embedding the live web widget into the active WordPress site.</p>
                <p class="proposal-roadmap-desc" data-lang="es">Configuración de flujos conversacionales en ManyChat y colocación del widget en el WordPress actual.</p>
              </div>
            </div>
            <div class="proposal-roadmap-step proposal-roadmap-step--last">
              <div class="proposal-roadmap-marker">
                <span class="proposal-roadmap-dot proposal-roadmap-dot--pink"></span>
              </div>
              <div class="proposal-roadmap-body">
                <p class="proposal-roadmap-period" data-lang="en">Week 3</p>
                <p class="proposal-roadmap-period" data-lang="es">Semana 3</p>
                <h4 class="proposal-roadmap-title" data-lang="en">Stress Testing &amp; Dashboard Launch</h4>
                <h4 class="proposal-roadmap-title" data-lang="es">Pruebas de Estrés y Lanzamiento del Panel</h4>
                <p class="proposal-roadmap-desc" data-lang="en">Running simulated client interactions and deploying the unified lead tracking dashboard for your sales team.</p>
                <p class="proposal-roadmap-desc" data-lang="es">Simulaciones de atención al cliente y despliegue del Dashboard de control para el equipo de ventas.</p>
              </div>
            </div>
          </div><!-- /roadmap -->

          <!-- Financial breakdown -->
          <p class="proposal-sub-label">
            <span data-lang="en">Financial Breakdown</span>
            <span data-lang="es">Desglose Financiero</span>
          </p>
          <div class="table-wrap">
            <table class="data-table proposal-finance-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Concept</span><span data-lang="es">Concepto</span></th>
                  <th><span data-lang="en">Total Investment</span><span data-lang="es">Inversión Total</span></th>
                  <th><span data-lang="en">Kickoff Cash (50%)</span><span data-lang="es">Anticipo en Efectivo (50%)</span></th>
                  <th><span data-lang="en">Trade Exchange (50%)</span><span data-lang="es">Intercambio Comercial (50%)</span></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <strong><span data-lang="en">AI Chatbot &amp; WhatsApp Core</span><span data-lang="es">Chatbot IA y Core de WhatsApp</span></strong>
                  </td>
                  <td><span class="proposal-amount">$10,000 MXN</span></td>
                  <td><span class="proposal-cash">$5,000 MXN</span></td>
                  <td><span class="proposal-trade">$5,000 MXN</span></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Win-Win note -->
          <div class="proposal-winwin-note">
            <span class="proposal-winwin-icon">🛥️</span>
            <div>
              <p class="proposal-winwin-label" data-lang="en">Win-Win — Yacht Charter Trade Credit</p>
              <p class="proposal-winwin-label" data-lang="es">Ganar-Ganar — Crédito de Chárter</p>
              <p class="proposal-winwin-text" data-lang="en">The $5,000 USD trade portion accumulates as corporate credit balance redeemable toward a shared charter experience on the fleet's premium vessels.</p>
              <p class="proposal-winwin-text" data-lang="es">El monto de intercambio de $5,000 USD se acumula como saldo corporativo canjeable para un chárter compartido en los yates premium de la flota.</p>
            </div>
          </div>

        </div><!-- /Phase 1 -->

        <!-- ══ PHASE 2 ═════════════════════════════════════════════════════ -->
        <div class="proposal-phase">

          <div class="proposal-phase-header proposal-phase-header--upcoming">
            <div class="proposal-phase-num proposal-phase-num--gold">2</div>
            <div class="proposal-phase-meta">
              <p class="proposal-phase-tag proposal-phase-tag--gold" data-lang="en">Phase 2 · Headless Architecture &amp; Automated SEO</p>
              <p class="proposal-phase-tag proposal-phase-tag--gold" data-lang="es">Fase 2 · Arquitectura Desacoplada y SEO Automatizado</p>
              <h3 class="proposal-phase-title" data-lang="en">Next-Gen Headless Web Architecture &amp; Automated SEO</h3>
              <h3 class="proposal-phase-title" data-lang="es">Arquitectura Web Desacoplada de Última Generación y SEO Automatizado</h3>
            </div>
            <span class="proposal-status-badge proposal-status-badge--upcoming">
              <span data-lang="en">○ Upcoming</span>
              <span data-lang="es">○ Próximo</span>
            </span>
          </div>

          <p class="proposal-phase-desc" data-lang="en">A complete migration away from traditional WordPress into a blazing-fast Headless Architecture — <strong>Next.js + Tailwind CSS + Vercel Edge Networks</strong> — paired with a specialized Headless CMS (Sanity/Strapi). The system automates SEO metadata generation and structural Google JSON-LD schemas, serving the complete database of 42 real vessels in under a second to mobile users.</p>
          <p class="proposal-phase-desc" data-lang="es">Una migración completa del WordPress tradicional hacia una Arquitectura Headless ultrarrápida — <strong>Next.js + Tailwind CSS + Vercel Edge Networks</strong> — combinada con un CMS Headless especializado (Sanity/Strapi). El sistema automatiza la generación de metadatos SEO y esquemas JSON-LD de Google, sirviendo el catálogo completo de 42 embarcaciones en menos de un segundo a usuarios móviles.</p>

          <!-- Roadmap -->
          <p class="proposal-sub-label">
            <span data-lang="en">Development Roadmap</span>
            <span data-lang="es">Calendario de Trabajo</span>
          </p>
          <ul class="phase-roadmap-list" data-lang="en">
            <li>Week 1: Luxury UI/UX Design &amp; PostgreSQL Schema</li>
            <li>Week 2: Headless CMS Infrastructure (Sanity / Strapi) &amp; Fleet Loading</li>
            <li>Week 3: Frontend Engineering (Next.js + Tailwind) &amp; AI Core Bridging</li>
            <li>Week 4: Automated SEO Clusters &amp; Google JSON-LD Schema</li>
            <li><strong>Week 5:</strong> Full Performance &amp; Mobile Optimization Audit &amp; Live Production Launch</li>
          </ul>
          <ul class="phase-roadmap-list" data-lang="es">
            <li>Semana 1: Diseño UI/UX Premium y Esquema PostgreSQL</li>
            <li>Semana 2: Infraestructura Headless CMS (Sanity / Strapi) y Carga de Flota</li>
            <li>Semana 3: Desarrollo Frontend (Next.js + Tailwind) e Integración IA</li>
            <li>Semana 4: Clústeres SEO Automatizados y Google JSON-LD</li>
            <li><strong>Semana 5:</strong> Auditoría de Rendimiento, Optimización Móvil y Despliegue en Vivo</li>
          </ul>

          <!-- Financial breakdown -->
          <p class="proposal-sub-label">
            <span data-lang="en">Financial Breakdown</span>
            <span data-lang="es">Desglose Financiero</span>
          </p>
          <div class="table-wrap">
            <table class="data-table proposal-finance-table">
              <thead>
                <tr>
                  <th><span data-lang="en">Concept</span><span data-lang="es">Concepto</span></th>
                  <th><span data-lang="en">Total Investment</span><span data-lang="es">Inversión Total</span></th>
                  <th><span data-lang="en">Kickoff Advance (30%)</span><span data-lang="es">Anticipo Arranque (30%)</span></th>
                  <th><span data-lang="en">Delivery Balance (40%)</span><span data-lang="es">Pago Contra Entrega (40%)</span></th>
                  <th><span data-lang="en">Trade Exchange (30%)</span><span data-lang="es">Intercambio Comercial (30%)</span></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <strong><span data-lang="en">Next-Gen Web Ecosystem</span><span data-lang="es">Ecosistema Web de Nueva Generación</span></strong>
                  </td>
                  <td><span class="proposal-amount">$20,000 MXN</span></td>
                  <td><span class="proposal-cash">$6,000 MXN</span></td>
                  <td><span class="proposal-cash">$8,000 MXN</span></td>
                  <td><span class="proposal-trade">$6,000 MXN</span></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Win-Win note -->
          <div class="proposal-winwin-note">
            <span class="proposal-winwin-icon">🚀</span>
            <div>
              <p class="proposal-winwin-label" data-lang="en">Win-Win — Premium Yacht Trade Credits</p>
              <p class="proposal-winwin-label" data-lang="es">Ganar-Ganar — Créditos en Chárteres Premium</p>
              <p class="proposal-winwin-text" data-lang="en">The $6,000 MXN trade portion converts to accumulated fleet charter credit — redeemable as a real luxury experience on Lover Lips Yachts' finest vessels.</p>
              <p class="proposal-winwin-text" data-lang="es">Los $6,000 MXN de intercambio se convierten en crédito de chárter en la flota — canjeable como una experiencia de lujo real en los mejores yates de Lover Lips Yachts.</p>
            </div>
          </div>

        </div><!-- /Phase 2 -->

        <!-- ══ GLOBAL ALLIANCE SUMMARY ════════════════════════════════════ -->
        <div class="proposal-alliance-card">
          <div class="proposal-alliance-badge">
            <span data-lang="en">Global Alliance Summary</span>
            <span data-lang="es">Resumen General de Alianza</span>
          </div>

          <h3 class="proposal-alliance-title" data-lang="en">
            Lester, we are not just developers — we are <em>tech partners</em> fully committed to scaling the operational efficiency and revenue of Lover Lips Yachts.
          </h3>
          <h3 class="proposal-alliance-title" data-lang="es">
            Lester, no somos solo desarrolladores — somos <em>socios tecnológicos</em> plenamente comprometidos con escalar la eficiencia operativa e ingresos de Lover Lips Yachts.
          </h3>

          <!-- Anchor Pricing — Crossed-Out International Market Reference -->
          <div class="market-anchor-pricing">
            <p class="anchor-price-line">
              <span data-lang="en">Standard International Market Value: $4,700 USD (Chatbot: $1,200 USD + Web Ecosystem: $3,500 USD)</span>
              <span data-lang="es">Valor Estándar de Mercado Internacional: $4,700 USD (Chatbot: $1,200 USD + Ecosistema Web: $3,500 USD)</span>
            </p>
            <p class="anchor-price-line anchor-price-line--sm">
              <span data-lang="en">Standard Exchange Rate Conversion: ~$84,000+ MXN (Completely Ignored for this Partnership)</span>
              <span data-lang="es">Conversión por Tipo de Cambio Estándar: ~$84,000+ MXN (Completamente Omitido para esta Alianza)</span>
            </p>
          </div>

          <!-- Strategic MXN Alliance Metrics -->
          <div class="proposal-alliance-totals">

            <div class="proposal-total-item">
              <p class="proposal-total-label" data-lang="en">Total Project Strategic Value</p>
              <p class="proposal-total-label" data-lang="es">Valor Total Estratégico del Proyecto</p>
              <p class="proposal-total-value proposal-total-value--gold">$35,800 MXN</p>
              <p class="proposal-total-note" data-lang="en">All phases + Batch 1, Batch 2 (×5 reports) integrated</p>
              <p class="proposal-total-note" data-lang="es">Todas las fases + Lote 1, Lote 2 (×5 informes) integradas</p>
            </div>

            <div class="proposal-total-item proposal-total-item--mid">
              <p class="proposal-total-label" data-lang="en">Total Real Cash Investment</p>
              <p class="proposal-total-label" data-lang="es">Inversión Total en Efectivo</p>
              <p class="proposal-total-value proposal-total-value--cash">$21,900 MXN</p>
              <p class="proposal-total-note proposal-total-note--pink">
                <span data-lang="en">Only 61.2% in Milestone-Based Payments</span>
                <span data-lang="es">Solo el 61.2% diferido en pagos conforme a entrega</span>
              </p>
            </div>

            <div class="proposal-total-item">
              <p class="proposal-total-label" data-lang="en">Capitalized Fleet Trade Alliance</p>
              <p class="proposal-total-label" data-lang="es">Alianza por Intercambio Comercial</p>
              <p class="proposal-total-value proposal-total-value--trade">$13,900 MXN</p>
              <p class="proposal-total-note proposal-total-note--gold">
                <span data-lang="en">38.8% Funded via Shared Vessel Experiences</span>
                <span data-lang="es">38.8% Financiado en uso de embarcaciones al concluir</span>
              </p>
            </div>

          </div>

          <p class="proposal-alliance-close" data-lang="en">We automate your business, eliminate your manual time constraints, and enjoy the Sea of Cortez on your magnificent fleet.</p>
          <p class="proposal-alliance-close" data-lang="es">Automatizamos tu negocio, eliminamos tus restricciones de tiempo manual y disfrutamos el Mar de Cortés en tu magnífica flota.</p>

          <div class="proposal-alliance-cta">
            <span class="proposal-alliance-seal">🩷</span>
            <strong data-lang="en">A True Pink Glove Win-Win Partnership.</strong>
            <strong data-lang="es">Una Verdadera Alianza Ganar-Ganar de Guante Rosa.</strong>
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
