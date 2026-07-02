<?php
declare(strict_types=1);

if (!defined('LLY_DASHBOARD_GATEKEEPER')) {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}

/* No book preload needed — editor lives in book_editor.php */

$lly_host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$lly_is_local = ($lly_host === 'localhost' || str_starts_with($lly_host, 'localhost:') || str_starts_with($lly_host, '127.0.0.1'));
$lly_public_book_url = $lly_is_local
    ? 'http://localhost/loverlipsyachts/my-book/'
    : 'https://loverlipsyachts.com/my-book/index.html';
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Owner Control Dashboard" />
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
        <a href="#" class="topbar-logo" aria-label="Lover Lips Yachts — Home">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Owner Dashboard</span>
          </div>
        </a>

        <!-- Controls -->
        <div class="topbar-actions">

          <!-- SPA Navigation — 3 tabs: Reports, Timeline, Alliance -->
          <nav class="topbar-nav" role="navigation" aria-label="Dashboard Views">
            <button type="button" class="topbar-nav-link active-nav" data-target="hub-reports">
              <span data-lang="en">Reports</span><span data-lang="es">Informes</span>
            </button>
            <button type="button" class="topbar-nav-link" data-target="hub-timeline">
              <span data-lang="en">Timeline</span><span data-lang="es">Línea de Tiempo</span>
            </button>
            <button type="button" class="topbar-nav-link" data-target="hub-alliance">
              <span data-lang="en">Alliance</span><span data-lang="es">Alianza</span>
            </button>
            <a class="topbar-nav-link topbar-nav-link--editor" href="book_editor.php">
              <span data-lang="en">✏️ Book Editor</span><span data-lang="es">✏️ Editor del Libro</span>
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
         1. HERO — HEADER & META INFO
    ═══════════════════════════════════════════════════════════════ -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="container">
        <div class="hero-inner">

          <!-- Left: Copy -->
          <div>
            <p class="hero-eyebrow">
              <span data-lang="en">Owner Control Center</span>
              <span data-lang="es">Centro de Control</span>
            </p>

            <h1 class="hero-title" id="hero-title">
              <span data-lang="en">Welcome,<br><span class="brand-name">Lester &amp; Family</span></span>
              <span data-lang="es">Bienvenido,<br><span class="brand-name">Lester y Familia</span></span>
            </h1>

            <p class="hero-desc" data-lang="en">
              This is your private control dashboard for the Lover Lips Yachts digital ecosystem — your single source of truth for project progress, fleet data and strategic milestones.
            </p>
            <p class="hero-desc" data-lang="es">
              Este es tu panel de control privado para el ecosistema digital de Lover Lips Yachts — tu fuente única de verdad sobre el avance del proyecto, datos de la flota e hitos estratégicos.
            </p>

            <a href="#hub-nav" class="hero-cta">
              <span data-lang="en">View Project Dashboard</span>
              <span data-lang="es">Ver Panel del Proyecto</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
          </div>

          <!-- Right: Meta Card -->
          <aside class="meta-card" aria-label="Project Metadata">
            <p class="meta-card-title">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              <span data-lang="en">Project Brief</span>
              <span data-lang="es">Ficha del Proyecto</span>
            </p>

            <div class="meta-row">
              <span class="meta-key" data-lang="en">Date</span>
              <span class="meta-key" data-lang="es">Fecha</span>
              <span class="meta-value">May 30, 2026</span>
            </div>
            <div class="meta-row">
              <span class="meta-key" data-lang="en">Prepared for</span>
              <span class="meta-key" data-lang="es">Preparado para</span>
              <span class="meta-value">Lester Keizer &amp; Wife</span>
            </div>
            <div class="meta-row">
              <span class="meta-key" data-lang="en">Status</span>
              <span class="meta-key" data-lang="es">Estado</span>
              <span class="meta-value active" data-lang="en">● Active</span>
              <span class="meta-value active" data-lang="es">● Activo</span>
            </div>
            <div class="meta-row">
              <span class="meta-key" data-lang="en">Current Phase</span>
              <span class="meta-key" data-lang="es">Fase Actual</span>
              <span class="meta-value phase" data-lang="en">Phase 1–2: Security Audit &amp; Database Mapping</span>
              <span class="meta-value phase" data-lang="es">Fases 1–2: Auditoría de Seguridad y Mapeo de Base de Datos</span>
            </div>
            <div class="meta-row update-highlight">
              <span class="meta-key" data-lang="en">Last Milestone</span>
              <span class="meta-key" data-lang="es">Último Hito</span>
              <span class="meta-value--highlight">
                <span data-lang="en">June 4: Core PGSQL Access Verified</span>
                <span data-lang="es">4 de Junio: Acceso Core PGSQL Verificado</span>
              </span>
            </div>
            <div class="meta-row">
              <span class="meta-key" data-lang="en">Fleet</span>
              <span class="meta-key" data-lang="es">Flota</span>
              <span class="meta-value">42 <span data-lang="en">vessels</span><span data-lang="es">embarcaciones</span></span>
            </div>
            <div class="meta-row">
              <span class="meta-key" data-lang="en">Platform</span>
              <span class="meta-key" data-lang="es">Plataforma</span>
              <span class="meta-value">WordPress + AI Chatbot</span>
            </div>
          </aside>

        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         EXECUTIVE SPA HUB NAVIGATION
    ═══════════════════════════════════════════════════════════════ -->
    <section class="hub-navigation-section" id="hub-nav" aria-label="Executive Navigation Hub">
      <div class="container">
        <div class="hub-navigation">

          <button type="button" class="hub-card active" data-target="hub-reports">
            <div class="hub-card-icon">📊</div>
            <h3>
              <span data-lang="en">Progress Reports</span>
              <span data-lang="es">Informes de Progreso</span>
            </h3>
            <p>
              <span data-lang="en">Technical achievements and completed work.</span>
              <span data-lang="es">Logros técnicos y trabajo completado.</span>
            </p>
          </button>

          <button type="button" class="hub-card" data-target="hub-timeline">
            <div class="hub-card-icon">🗓️</div>
            <h3>
              <span data-lang="en">Project Timeline</span>
              <span data-lang="es">Línea de Tiempo</span>
            </h3>
            <p>
              <span data-lang="en">Strategic roadmap and delivery milestones.</span>
              <span data-lang="es">Hoja de ruta estratégica e hitos.</span>
            </p>
          </button>

          <button type="button" class="hub-card" data-target="hub-alliance">
            <div class="hub-card-icon">🤝</div>
            <h3>
              <span data-lang="en">Win-Win Alliance</span>
              <span data-lang="es">Alianza Ganar-Ganar</span>
            </h3>
            <p>
              <span data-lang="en">Investment structure and partnership model.</span>
              <span data-lang="es">Estructura de inversión y alianza estratégica.</span>
            </p>
          </button>

          <a class="hub-card hub-card--editor" href="book_editor.php" role="button">
            <div class="hub-card-icon">✏️</div>
            <h3>
              <span data-lang="en">Book Editor Studio</span>
              <span data-lang="es">Estudio Editor del Libro</span>
            </h3>
            <p>
              <span data-lang="en">Edit and publish your book spotlight page live.</span>
              <span data-lang="es">Edita y publica tu página del libro en vivo.</span>
            </p>
          </a>

        </div>
      </div>
    </section>

    <!-- ─── Hub Panel: Progress Reports & Knowledge Base ──────────────── -->
    <div id="hub-reports" class="hub-panel active">

    <!-- ═══════════════════════════════════════════════════════════════
         2. TECHNICAL PROGRESS REPORTS
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="reports-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Work Completed</span>
          <span data-lang="es">Trabajo Realizado</span>
        </p>
        <h2 class="section-title" id="reports-title">
          <span data-lang="en">Technical Progress <em>Reports</em></span>
          <span data-lang="es">Informes de Progreso <em>Técnico</em></span>
        </h2>
        <p class="section-subtitle" data-lang="en">
          Every technical achievement explained in plain language — so you fully understand the value delivered, with no support required.
        </p>
        <p class="section-subtitle" data-lang="es">
          Cada logro técnico explicado en lenguaje claro — para que comprendas a la perfección el valor entregado, sin necesidad de asistencia adicional.
        </p>

        <div class="reports-grid">

          <!-- Report I: Database Integration & Book Editor Studio -->
          <article class="report-card report-card--featured" id="report-i">
            <div class="report-card-head">
              <div class="report-number report-number--gold">I</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">July 1, 2026</span>
                  <span data-lang="es">1 de Julio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">Report I · System Architecture · Database CMS &amp; Custom Studio Deployment</span>
              <span data-lang="es">Informe I · Arquitectura de Sistema · CMS en Base de Datos y Despliegue de Estudio Personalizado</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Database Integration &amp; Visual Book Editor Studio Deployment</span>
              <span data-lang="es">Integración de Base de Datos y Despliegue del Estudio Visual de Edición del Libro</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                Successfully migrated the static placeholder landing page into a fully dynamic MySQL schema (<strong>lly_book_content</strong>). The bilingual content infrastructure is now stored across <strong>content_en</strong> and <strong>content_es</strong> columns, completely neutralizing column-not-found errors (Error 1054) and regex parsing dependencies.
                <br><br>
                Deployed the secure, custom-built <strong>Book Editor Studio</strong> as a dedicated standalone administrative page (<code>book_editor.php</code>) — fully pre-populated from the live database on every load. Lester can autonomously update text, blog articles, the Amazon link, sample chapter, and upload covers automatically transformed to <strong>WebP</strong> at 80% quality, eliminating all developer downtime for content changes.
              </p>
              <p data-lang="es">
                Migración exitosa de la landing page estática a un esquema dinámico en MySQL (<strong>lly_book_content</strong>). La infraestructura de contenido bilingüe está ahora almacenada en las columnas <strong>content_en</strong> y <strong>content_es</strong>, neutralizando por completo los errores de columna no encontrada (Error 1054) y las dependencias de parsing por regex.
                <br><br>
                Se desplegó el panel seguro <strong>Book Editor Studio</strong> como página administrativa independiente (<code>book_editor.php</code>) — pre-cargada desde la base de datos activa en cada visita. Lester puede actualizar textos, artículos del blog, el enlace de Amazon, el capítulo de muestra y subir portadas optimizadas a <strong>WebP</strong> de forma completamente autónoma.
              </p>

              <!-- Book Editor Studio visual preview -->
              <div class="report-score-frame">
                <p class="report-score-frame-label">
                  <span data-lang="en">Book Editor Studio — Admin Interface Preview</span>
                  <span data-lang="es">Book Editor Studio — Vista Previa del Panel Administrativo</span>
                </p>
                <img
                  src="assets/img/Book_Editor.png"
                  alt="Book Editor Studio admin interface screenshot"
                  class="report-score-img"
                  loading="lazy"
                />
              </div>

              <a href="book_editor.php" class="report-strategic-gold-btn">
                <span data-lang="en">Open Book Editor Studio</span>
                <span data-lang="es">Abrir Estudio Editor del Libro</span>
              </a>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">DB Migrated · Standalone Studio Live &amp; Verified</span>
              <span data-lang="es">BD Migrada · Estudio Independiente Activo y Verificado</span>
            </p>
          </article>

          <!-- Report H: Navigation Menu & Live Server Deployment -->
          <article class="report-card report-card--featured" id="report-h">
            <div class="report-card-head">
              <div class="report-number report-number--gold">H</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 29, 2026</span>
                  <span data-lang="es">29 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">Infrastructure · Navigation Menu &amp; Live Server Deployment</span>
              <span data-lang="es">Infraestructura · Menú de Navegación y Despliegue en Servidor</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Public Navigation &amp; Server Infrastructure Deployment</span>
              <span data-lang="es">Despliegue de Navegación Pública e Infraestructura de Servidor</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                We generated a lightweight, uncompressed standalone <strong>HTML/CSS/JS framework</strong> for the book spotlight — bypassing WordPress database queries entirely so this page renders at <strong>5-star mobile speed</strong> regardless of any load on the main site.
              </p>
              <p data-lang="es">
                Generamos un framework <strong>HTML/CSS/JS</strong> autónomo, ligero y sin compresión adicional para la página del libro — evitando por completo las consultas a la base de datos de WordPress, logrando una velocidad de carga móvil de <strong>5 estrellas</strong> sin depender de la carga del sitio principal.
              </p>
              <p data-lang="en">
                We also mapped the native bicultural language toggle (EN/ES) directly to <code>https://loverlipsyachts.com/my-book/</code>, and cleanly integrated a new navigation menu button inside the live WordPress header — with zero structural drift to the existing menu, theme, or layout.
              </p>
              <p data-lang="es">
                Además, conectamos el selector de idioma bicultural nativo (EN/ES) directamente con <code>https://loverlipsyachts.com/my-book/</code>, e integramos limpiamente un nuevo botón de navegación dentro del encabezado activo de WordPress — sin ninguna alteración estructural al menú, tema o diseño existente.
              </p>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">Live in Production &amp; Verified</span>
              <span data-lang="es">En Producción y Verificado</span>
            </p>
          </article>

          <!-- Report G: Sunseeker 52 Retro Media Pipeline -->
          <article class="report-card report-card--featured" id="report-g">
            <div class="report-card-head">
              <div class="report-number report-number--gold">G</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 27, 2026</span>
                  <span data-lang="es">27 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">Media Engineering · Sunseeker 52 Retro Rebrand</span>
              <span data-lang="es">Ingeniería de Medios · Rebranding Sunseeker 52 Retro</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Sunseeker 52 Retro — Full Media Pipeline Optimization</span>
              <span data-lang="es">Sunseeker 52 Retro — Optimización Completa del Pipeline de Medios</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                We executed the full digital rebrand of the media library for the vessel formerly listed as <strong>"Host 50,"</strong> now relaunched as the <strong>Sunseeker 52 Retro</strong>. The original raw folder — roughly <strong>2 GB</strong> of unprocessed drone footage, photography, and video — was fully reprocessed into a lean, web-ready package of just <strong>45 MB</strong>, a <strong>97% size reduction</strong>, with zero perceptible loss in visual quality.
              </p>
              <p data-lang="es">
                Ejecutamos el rebranding digital completo de la biblioteca de medios para la embarcación antes listada como <strong>"Host 50,"</strong> ahora relanzada como <strong>Sunseeker 52 Retro</strong>. La carpeta original sin procesar — aproximadamente <strong>2 GB</strong> de metraje de dron, fotografía y video en bruto — fue reprocesada por completo en un paquete liviano y listo para web de solo <strong>45 MB</strong>, una <strong>reducción del 97%</strong> en peso, sin pérdida perceptible de calidad visual.
              </p>
              <p data-lang="en">
                All <strong>51 photographs</strong> were individually reviewed and sorted into <strong>Interior</strong> and <strong>Exterior</strong> categories, then converted to the next-generation <strong>WebP format</strong> (<em>the modern image standard used by Google, Amazon, and other top-tier sites to load photos significantly faster on mobile without sacrificing sharpness</em>). Every file was renamed under the new commercial identity for clean, professional organization on the live site.
              </p>
              <p data-lang="es">
                Las <strong>51 fotografías</strong> fueron revisadas y clasificadas individualmente en categorías de <strong>Interior</strong> y <strong>Exterior</strong>, y luego convertidas al formato de nueva generación <strong>WebP</strong> (<em>el estándar moderno de imagen que usan Google, Amazon y otros sitios de primer nivel para cargar fotos mucho más rápido en móvil sin sacrificar nitidez</em>). Cada archivo fue renombrado bajo la nueva identidad comercial para una organización limpia y profesional en el sitio en vivo.
              </p>
              <p data-lang="en">
                The two raw video files (<strong>47 MB</strong> and <strong>103 MB</strong>) were trimmed into aesthetic <strong>10-to-14 second loops</strong> and re-encoded using <strong>H.264 compression</strong> (<em>the most widely compatible video format for web and mobile browsers</em>), bringing both clips to a combined weight <strong>under 15 MB total</strong> — purpose-built for smooth, instant-loading autoplay headers on mobile devices.
              </p>
              <p data-lang="es">
                Los dos archivos de video originales (<strong>47 MB</strong> y <strong>103 MB</strong>) fueron recortados en <strong>loops estéticos de 10 a 14 segundos</strong> y recodificados con <strong>compresión H.264</strong> (<em>el formato de video más compatible con navegadores web y móviles</em>), llevando ambos clips a un peso combinado <strong>menor a 15 MB en total</strong> — diseñados específicamente para encabezados de reproducción automática fluidos e instantáneos en dispositivos móviles.
              </p>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">51 Images + 2 Videos Optimized &amp; Verified</span>
              <span data-lang="es">51 Imágenes + 2 Videos Optimizados y Verificados</span>
            </p>
          </article>

          <!-- Report F: Public Book Landing Page (book.html) -->
          <article class="report-card" id="report-f">
            <div class="report-card-head">
              <div class="report-number">F</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 20, 2026</span>
                  <span data-lang="es">20 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">Frontend &amp; Marketing · Lead Magnet Landing Page</span>
              <span data-lang="es">Frontend y Marketing · Landing Page Imán de Leads</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Public Book Landing Page — Organic Marketing</span>
              <span data-lang="es">Landing Page Pública del Libro — Marketing Orgánico</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                We designed and deployed a high-conversion, ultra-luxury landing page for your upcoming memoir. This page acts as the ultimate Bottom-of-Funnel (BofU) lead magnet, designed to capture qualified prospects from social media without spending on paid ads. It includes aesthetic storytelling, mobile-first design, and seamless WhatsApp integration.
              </p>
              <p data-lang="es">
                Diseñamos y desplegamos una landing page de ultra-lujo y alta conversión para tus próximas memorias. Esta página actúa como el imán de prospectos definitivo (BofU), diseñada para capturar clientes calificados desde redes sociales sin gastar en pautas publicitarias. Incluye storytelling estético, diseño mobile-first e integración directa con WhatsApp.
              </p>

              <!-- Landing Page Screenshot Showcase -->
              <div class="report-score-frame">
                <p class="report-score-frame-label">
                  <span data-lang="en">Landing Page Preview</span>
                  <span data-lang="es">Vista Previa de la Landing Page</span>
                </p>
                <img
                  src="assets/img/LandingPage.png"
                  alt="Nine Lives. One True Love — public landing page preview"
                  class="report-score-img"
                  loading="lazy"
                />
              </div>

              <a href="<?= htmlspecialchars($lly_public_book_url, ENT_QUOTES) ?>" class="report-strategic-gold-btn" target="_blank" rel="noopener noreferrer">
                <span data-lang="en">View Public Book Page</span>
                <span data-lang="es">Ver Página Pública del Libro</span>
              </a>
            </div>
          </article>

          <!-- Report E: Organic Marketing Strategy (business plan, not a dev report) -->
          <article class="report-card report-card--strategic" id="report-e">
            <div class="report-card-head">
              <div class="report-number report-number--gold">E</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 22, 2026</span>
                  <span data-lang="es">22 de Junio, 2026</span>
                </p>
                <span class="pill pill-orange">
                  <span data-lang="en">Awaiting Reconciliation</span>
                  <span data-lang="es">Por Conciliar</span>
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
                  A 90-day, zero-paid-media guerrilla digital campaign for the September 2, 2026 book launch — aesthetic funnel, transactional SEO bridges, and three ready-to-publish bilingual copy templates. Full research, copy and AI conversion rules are detailed in the dedicated strategy presentation below for your review and authorization.
                </p>
                <p data-lang="es">
                  Una campaña de guerrilla digital de 90 días, sin pauta pagada, para el lanzamiento del libro el 2 de septiembre de 2026 — embudo estético, puentes de SEO transaccional y tres plantillas de copy bilingües listas para publicar. La investigación completa, el copy y las reglas de conversión de la IA están detalladas en la presentación dedicada de abajo, para tu revisión y autorización.
                </p>
                <a href="strategy.php" class="report-strategic-gold-btn">
                  <span data-lang="en">Review Proposed Marketing Strategy</span>
                  <span data-lang="es">Revisar Estrategia de Marketing Propuesta</span>
                </a>
              </div>
            </div>
          </article>

          <!-- Report D: Global SEO & Performance Architecture -->
          <article class="report-card report-card--featured" id="report-d">
            <div class="report-card-head">
              <div class="report-number report-number--gold">D</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 20, 2026</span>
                  <span data-lang="es">20 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">SEO &amp; Performance · Global Architecture Overhaul</span>
              <span data-lang="es">SEO y Rendimiento · Reingeniería de Arquitectura Global</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Global SEO &amp; Performance Architecture</span>
              <span data-lang="es">Arquitectura Global de SEO y Rendimiento</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                We executed a full structural SEO and performance reengineering across the entire WordPress platform, raising the official <strong>AIOSEO</strong> (the site's leading SEO audit plugin) score to a historic <strong>91/100 — Excellent</strong> rating.
                <br><br>
                Two critical penalties were resolved at the root. First, a <strong>duplicate H1 heading conflict</strong> (<em>when a page accidentally declares two "main titles" instead of one, confusing Google about what the page is actually about and diluting its ranking power</em>) was identified and permanently corrected across affected templates. Second, we activated <strong>server-side Object Caching via Redis/Memcached</strong> (<em>a high-speed memory layer that stores frequently requested database results, so the server stops repeating the same expensive queries on every visit</em>), which directly reduced database load and overall page weight.
              </p>
              <p data-lang="es">
                Ejecutamos una reingeniería estructural completa de SEO y rendimiento en toda la plataforma WordPress, elevando la calificación oficial de <strong>AIOSEO</strong> (el plugin líder de auditoría SEO del sitio) a un histórico <strong>91/100 — Excelente</strong>.
                <br><br>
                Se resolvieron de raíz dos penalizaciones críticas. Primero, un <strong>conflicto de encabezado H1 duplicado</strong> (<em>cuando una página declara accidentalmente dos "títulos principales" en lugar de uno, confundiendo a Google sobre el tema real de la página y diluyendo su fuerza de posicionamiento</em>), corregido de forma permanente en las plantillas afectadas. Segundo, activamos <strong>caché de objetos del lado del servidor vía Redis/Memcached</strong> (<em>una capa de memoria de alta velocidad que guarda los resultados de consultas frecuentes a la base de datos, evitando que el servidor repita las mismas consultas costosas en cada visita</em>), lo cual redujo directamente la carga de base de datos y el peso total de la página.
              </p>

              <!-- Score Screenshot Showcase -->
              <div class="report-score-frame">
                <p class="report-score-frame-label">
                  <span data-lang="en">AIOSEO Official Audit Score</span>
                  <span data-lang="es">Puntaje Oficial de Auditoría AIOSEO</span>
                </p>
                <img
                  src="assets/img/91_score.png"
                  alt="AIOSEO audit score of 91 out of 100 — Excellent rating"
                  class="report-score-img"
                  loading="lazy"
                />
                <p class="report-score-frame-caption" data-lang="en">91 / 100 — Excellent</p>
                <p class="report-score-frame-caption" data-lang="es">91 / 100 — Excelente</p>
              </div>

              <p data-lang="en">
                <strong>A note on the remaining minor flags:</strong> AIOSEO still lists 3 small residual issues — references to inline base64 scripts and a ~2KB DOM surplus. These are <strong>normal, expected behaviors inherent to any Elementor-powered site</strong> (Elementor encodes certain visual effects directly into the page for instant rendering). They carry no measurable impact on speed, rankings, or commercial performance — the 91/100 result already fully accounts for them.
              </p>
              <p data-lang="es">
                <strong>Nota sobre los detalles menores restantes:</strong> AIOSEO todavía señala 3 pequeños issues residuales — referencias a scripts base64 incrustados y un excedente de ~2KB en el DOM. Estos son <strong>comportamientos normales y esperados, propios de cualquier sitio construido con Elementor</strong> (Elementor codifica ciertos efectos visuales directamente en la página para un renderizado instantáneo). No tienen ningún impacto medible en velocidad, posicionamiento ni éxito comercial — el resultado de 91/100 ya los contempla por completo.
              </p>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">Historic Score Achieved &amp; Verified</span>
              <span data-lang="es">Puntaje Histórico Alcanzado y Verificado</span>
            </p>
          </article>

          <!-- Report C: Gallery -->
          <article class="report-card" id="report-c">
            <div class="report-card-head">
              <div class="report-number">C</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 5, 2026</span>
                  <span data-lang="es">5 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">UI/UX · Gallery Polish</span>
              <span data-lang="es">UI/UX · Limpieza de Galerías</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Gallery UI/UX Polish</span>
              <span data-lang="es">Optimización Visual de Galerías</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                On multiple photo gallery pages — such as the <strong>Most Affordable Luxury Yacht</strong> album (<code>/most-affordable-luxury-yacht-5/</code>) — an annoying grey text overlay appeared whenever a visitor hovered over a photo. These were <strong>image metadata captions</strong>: raw technical file names and internal image titles that WordPress was accidentally exposing on hover.
                <br><br>
                We systematically removed these overlays via <strong>programmatic metadata cleanup</strong> (<em>an automated script that finds and strips all internal technical text fields from each image in the gallery database</em>) across the affected albums. Now, hovering over any photo delivers a clean, smooth, premium experience — exactly as expected from a luxury yacht brand.
              </p>
              <p data-lang="es">
                En múltiples álbumes de fotos — como el de la <strong>Most Affordable Luxury Yacht</strong> (<code>/most-affordable-luxury-yacht-5/</code>) — aparecía un molesto texto gris superpuesto cada vez que el visitante pasaba el mouse sobre una fotografía. Estos eran <strong>metadatos de imagen</strong>: nombres de archivo técnicos y títulos internos que WordPress mostraba accidentalmente al hacer hover.
                <br><br>
                Los eliminamos mediante <strong>limpieza programática de metadatos</strong> (<em>un script automatizado que localiza y elimina todos los campos de texto técnico interno de cada imagen en la base de datos de la galería</em>) en los álbumes afectados. Ahora, al pasar el mouse sobre cualquier foto, el usuario vive una experiencia limpia, fluida y de alto nivel — exactamente lo que se espera de una marca de yates de lujo.
              </p>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">All Albums Cleaned</span>
              <span data-lang="es">Todos los Álbumes Optimizados</span>
            </p>
          </article>

          <!-- Report B: Page Recovery -->
          <article class="report-card" id="report-b">
            <div class="report-card-head">
              <div class="report-number">B</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 5, 2026</span>
                  <span data-lang="es">5 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">Recovery · Database Route Restoration</span>
              <span data-lang="es">Recuperación · Restauración de Rutas</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Landing Page Recovery</span>
              <span data-lang="es">Recuperación de Páginas Caídas</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                Two of your flagship booking pages had gone offline — visitors landing on them received only a blank screen or a generic server error. We successfully rescued and reactivated both:
                <br><br>
                • <strong>Pink Lips</strong> (<code>/pink-lips/</code>) — your signature pink vessel page<br>
                • <strong>CNR Maranatha 120</strong> (<code>/maranatha-120/</code>) — your flagship superyacht page
                <br><br>
                The root cause was <strong>broken database permalink routes</strong> (<em>the internal "address book" that WordPress uses to find and serve the correct page when someone clicks a link</em>). We rebuilt these route mappings and revalidated the cache layers so that every link, booking button and search engine result pointing to these pages now resolves correctly and instantly.
              </p>
              <p data-lang="es">
                Dos de tus páginas principales de reservas habían dejado de funcionar — los visitantes que llegaban a ellas solo veían una pantalla en blanco o un error genérico del servidor. Rescatamos y reactivamos exitosamente ambas:
                <br><br>
                • <strong>Pink Lips</strong> (<code>/pink-lips/</code>) — la página de tu icónica embarcación rosa<br>
                • <strong>CNR Maranatha 120</strong> (<code>/maranatha-120/</code>) — la página de tu superyate insignia
                <br><br>
                La causa raíz fueron <strong>rutas de base de datos rotas</strong> (<em>el "directorio interno" que WordPress usa para encontrar y servir la página correcta cuando alguien hace clic en un enlace</em>). Reconstruimos los mapeos de rutas y revalidamos las capas de caché, de modo que cada enlace, botón de reserva y resultado de búsqueda que apunte a estas páginas se resuelva ahora de forma correcta e instantánea.
              </p>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">Both Pages Live &amp; Verified</span>
              <span data-lang="es">Ambas Páginas Activas y Verificadas</span>
            </p>
          </article>

          <!-- Report A: FOUC -->
          <article class="report-card" id="report-a">
            <div class="report-card-head">
              <div class="report-number">A</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 5, 2026</span>
                  <span data-lang="es">5 de Junio, 2026</span>
                </p>
                <span class="pill pill-green">
                  <span data-lang="en">Account Settled</span>
                  <span data-lang="es">Saldo Conciliado</span>
                </span>
              </div>
            </div>
            <p class="report-tag">
              <span data-lang="en">Performance · FOUC Remediation</span>
              <span data-lang="es">Rendimiento · Corrección de FOUC</span>
            </p>
            <h3 class="report-title">
              <span data-lang="en">Zero-Delay Premium Layout Rendering</span>
              <span data-lang="es">Renderizado Instantáneo del Diseño Premium</span>
            </h3>
            <div class="report-body">
              <p data-lang="en">
                We eliminated the visual "flash" that made the homepage look broken for a split second on every load. Technically, this is called a <strong>FOUC — Flash of Unstyled Content</strong> (a brief moment where the page loads raw, unstyled text before the design kicks in).
                <br><br>
                To fix it, we intervened inside the <strong>LiteSpeed Cache plugin</strong> (the tool that makes your site load fast by saving pre-built versions of each page) — specifically, we disabled background loops running in <strong>Guest Mode</strong> (anonymous visitor mode) that were stalling the queue, then flushed all stale data sitting in <strong>QUIC.cloud</strong> (<em>your global Content Delivery Network — a worldwide network of servers that replicates your website so every visitor gets a geographically fast copy</em>). The result: Elementor's visual design now loads in a single, instant pass — completely clean, with no flash of plain unstyled text.
              </p>
              <p data-lang="es">
                Eliminamos el "parpadeo" visual que hacía que la página de inicio se viera rota por una fracción de segundo en cada carga. Técnicamente esto se llama <strong>FOUC — Flash of Unstyled Content</strong> (un brevísimo instante en que la página muestra texto plano desacomodado antes de que el diseño cargue).
                <br><br>
                Para solucionarlo, intervenimos el <strong>plugin LiteSpeed Cache</strong> (la herramienta que acelera tu sitio guardando versiones pre-construidas de cada página) — desactivando los bucles en segundo plano del <strong>Modo Invitado</strong> (modo visitante anónimo) que atascaban la cola, y luego purgamos los datos obsoletos acumulados en <strong>QUIC.cloud</strong> (<em>tu Red de Distribución de Contenido global — una red de servidores en todo el mundo que replica tu web para que cada visitante reciba una copia geográficamente rápida</em>). Resultado: el diseño visual de Elementor ahora carga de forma instantánea y monolítica — sin parpadeo de texto plano.
              </p>
            </div>
            <p class="report-check">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span data-lang="en">Completed &amp; Verified</span>
              <span data-lang="es">Completado y Verificado</span>
            </p>
          </article>

        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         3. SOURCE OF TRUTH — KNOWLEDGE BASE
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-truth" aria-labelledby="truth-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Content Validation</span>
          <span data-lang="es">Validación de Contenido</span>
        </p>
        <h2 class="section-title" id="truth-title">
          <span data-lang="en">Source of <em>Truth</em> — Master Knowledge Bases</span>
          <span data-lang="es">Archivo de la <em>Verdad</em> — Bases de Conocimiento Maestras</span>
        </h2>
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
                  <p class="accordion-trigger-sub" data-lang="en">3 of 42 vessels · Rate structure · Capacity</p>
                  <p class="accordion-trigger-sub" data-lang="es">3 de 42 embarcaciones · Estructura de tarifas · Capacidad</p>
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
                    <tr>
                      <td><strong>CNR Maranatha 120</strong><br><small>/maranatha-120/</small></td>
                      <td data-lang="en">Up to 50 guests</td>
                      <td data-lang="es">Hasta 50 personas</td>
                      <td data-lang="en">$TBC — Pending Review</td>
                      <td data-lang="es">$POR DEFINIR — En revisión</td>
                      <td data-lang="en"><span class="pill pill-pink">Flagship</span></td>
                      <td data-lang="es"><span class="pill pill-pink">Insignia</span></td>
                    </tr>
                    <tr>
                      <td><strong>Pink Lips</strong><br><small>/pink-lips/</small></td>
                      <td data-lang="en">Up to 20 guests</td>
                      <td data-lang="es">Hasta 20 personas</td>
                      <td data-lang="en">$TBC — Pending Review</td>
                      <td data-lang="es">$POR DEFINIR — En revisión</td>
                      <td data-lang="en"><span class="pill pill-gold">Signature</span></td>
                      <td data-lang="es"><span class="pill pill-gold">Estrella</span></td>
                    </tr>
                    <tr>
                      <td><strong>Most Affordable Luxury</strong><br><small>/most-affordable-luxury-yacht-5/</small></td>
                      <td data-lang="en">Up to 13 guests</td>
                      <td data-lang="es">Hasta 13 personas</td>
                      <td data-lang="en">$TBC — Pending Review</td>
                      <td data-lang="es">$POR DEFINIR — En revisión</td>
                      <td data-lang="en"><span class="pill pill-green">Available</span></td>
                      <td data-lang="es"><span class="pill pill-green">Disponible</span></td>
                    </tr>
                    <tr>
                      <td colspan="2" data-lang="en" class="u-italic">+ 39 additional vessels pending data lift from WordPress (Phase 2 deliverable)</td>
                      <td colspan="2" data-lang="es" class="u-italic">+ 39 embarcaciones adicionales pendientes de extracción de WordPress (entregable Fase 2)</td>
                      <td></td><td></td>
                    </tr>
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

    </div><!-- /hub-reports -->

    <!-- ═══════════════════════════════════════════════════════════════
         4. MASTER INTERACTIVE TIMELINE
    ═══════════════════════════════════════════════════════════════ -->
    <section id="hub-timeline" class="section section-timeline hub-panel" aria-labelledby="timeline-title">
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
         5. TECH EVOLUTION & WIN-WIN PARTNERSHIP PROPOSAL
    ═══════════════════════════════════════════════════════════════ -->
    <section id="hub-alliance" class="section section-proposal hub-panel" aria-labelledby="proposal-title">
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
          A phased, custom-tailored digital re-engineering strategy — built exclusively for Lover Lips Yachts. Each phase delivers measurable results while protecting cash flow through a hybrid cash &amp; trade-exchange model.
        </p>
        <p class="section-subtitle" data-lang="es">
          Una estrategia de re-ingeniería digital por fases, diseñada exclusivamente para Lover Lips Yachts. Cada fase entrega resultados medibles mientras protege el flujo de caja mediante un modelo híbrido de efectivo e intercambio comercial.
        </p>

        <!-- ══ WIN-WIN YACHT CREDIT VAULT — pinned at section level ══════ -->
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

        <!-- ══ PHASE 0 ═════════════════════════════════════════════════════ -->
        <div class="proposal-phase">

          <div class="proposal-phase-header proposal-phase-header--done">
            <div class="proposal-phase-num">0</div>
            <div class="proposal-phase-meta">
              <p class="proposal-phase-tag" data-lang="en">Batch 1 &amp; 2 · Account Settled · Emergency Recovery &amp; Book Launch Pipeline</p>
              <p class="proposal-phase-tag" data-lang="es">Lotes 1 &amp; 2 · Saldo Conciliado · Rescate de Emergencia y Pipeline de Lanzamiento del Libro</p>
              <h3 class="proposal-phase-title" data-lang="en">Completed with Excellence</h3>
              <h3 class="proposal-phase-title" data-lang="es">Completado con Excelencia</h3>
            </div>
            <span class="proposal-status-badge proposal-status-badge--done">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span data-lang="en">Account Settled</span>
              <span data-lang="es">Saldo Conciliado</span>
            </span>
          </div>

          <p class="proposal-phase-desc" data-lang="en">We successfully restored operational stability and visual performance to the current WordPress ecosystem to prevent any loss of high-intent traffic while we engineer the future of the brand.</p>
          <p class="proposal-phase-desc" data-lang="es">Restauramos con éxito la estabilidad operativa y el rendimiento visual del ecosistema WordPress actual para evitar pérdidas de tráfico de alta intención mientras ingenierizamos el futuro de la marca.</p>

          <!-- ── Batch History ─────────────────────────────────────────── -->
          <p class="proposal-sub-label">
            <span data-lang="en">Batch History — Completed &amp; Settled Work</span>
            <span data-lang="es">Historial de Lotes — Trabajo Completado y Conciliado</span>
          </p>

          <!-- ── BATCH 1 — newest first (June 29, 2026) ───────────────── -->
          <div class="proposal-batch-block proposal-batch-block--batch1">

            <div class="proposal-batch-stamp">
              <span class="proposal-batch-stamp-icon">✅</span>
              <div>
                <p class="proposal-batch-stamp-label">
                  <span data-lang="en">Batch 2 · Account Settled</span>
                  <span data-lang="es">Lote 2 · Saldo Conciliado</span>
                </p>
                <p class="proposal-batch-stamp-title">
                  <span data-lang="en">Book Launch &amp; Fleet Pipeline Optimization</span>
                  <span data-lang="es">Lanzamiento del Libro y Optimización del Pipeline de Flota</span>
                </p>
              </div>
              <span class="proposal-batch-stamp-date">
                <span data-lang="en">Settled: June 29, 2026</span>
                <span data-lang="es">Liquidado: 29 de Junio, 2026</span>
              </span>
            </div>

            <div class="proposal-modules proposal-modules--batch1">

              <div class="proposal-module-card proposal-module-card--batch1">
                <div class="proposal-module-icon">📈</div>
                <p class="proposal-module-tag" data-lang="en">Report D · SEO &amp; Performance Architecture</p>
                <p class="proposal-module-tag" data-lang="es">Informe D · SEO y Arquitectura de Rendimiento</p>
                <h4 class="proposal-module-title" data-lang="en">Global SEO &amp; Performance Architecture Overhaul</h4>
                <h4 class="proposal-module-title" data-lang="es">Reingeniería Global de SEO y Rendimiento</h4>
                <p class="proposal-module-desc" data-lang="en">Raised the official AIOSEO audit score to a historic 91/100 — Excellent, by eliminating a duplicate H1 heading conflict and activating server-side Redis/Memcached object caching across the entire platform. Zero residual penalties affect commercial performance.</p>
                <p class="proposal-module-desc" data-lang="es">Elevamos la puntuación oficial de AIOSEO a un histórico 91/100 — Excelente, eliminando conflictos de H1 duplicado y activando caché de objetos Redis/Memcached. Cero penalizaciones residuales afectan el rendimiento comercial.</p>
                <a href="#report-d" class="proposal-module-log-link" aria-label="View Report D technical log">
                  <span data-lang="en">Technical Log D →</span>
                  <span data-lang="es">Registro Técnico D →</span>
                </a>
              </div>

              <div class="proposal-module-card proposal-module-card--batch1">
                <div class="proposal-module-icon">📖</div>
                <p class="proposal-module-tag" data-lang="en">Report F · Frontend · Lead Magnet Landing Page</p>
                <p class="proposal-module-tag" data-lang="es">Informe F · Frontend · Landing Page Imán de Leads</p>
                <h4 class="proposal-module-title" data-lang="en">Lead Magnet Landing Page Design — Public Book Funnel</h4>
                <h4 class="proposal-module-title" data-lang="es">Diseño de Landing Page Imán — Embudo Público del Libro</h4>
                <p class="proposal-module-desc" data-lang="en">Designed and deployed a bilingual, ultra-luxury landing page for the "Nine Lives. One True Love" memoir acting as a zero-paid-media Bottom-of-Funnel lead magnet. Includes aesthetic storytelling, mobile-first design, social sharing, and direct WhatsApp integration.</p>
                <p class="proposal-module-desc" data-lang="es">Diseñamos y desplegamos una landing page bilingüe ultra-premium para el libro "Nine Lives. One True Love" como imán BofU sin pauta pagada. Incluye storytelling, diseño mobile-first, botones de redes sociales e integración directa con WhatsApp.</p>
                <a href="#report-f" class="proposal-module-log-link" aria-label="View Report F technical log">
                  <span data-lang="en">Technical Log F →</span>
                  <span data-lang="es">Registro Técnico F →</span>
                </a>
              </div>

              <div class="proposal-module-card proposal-module-card--batch1">
                <div class="proposal-module-icon">🛥️</div>
                <p class="proposal-module-tag" data-lang="en">Report G · Media Engineering · Sunseeker 52 Retro</p>
                <p class="proposal-module-tag" data-lang="es">Informe G · Ingeniería de Medios · Sunseeker 52 Retro</p>
                <h4 class="proposal-module-title" data-lang="en">Sunseeker 52 Retro — Full Media Pipeline Optimization</h4>
                <h4 class="proposal-module-title" data-lang="es">Sunseeker 52 Retro — Optimización Completa del Pipeline de Medios</h4>
                <p class="proposal-module-desc" data-lang="en">Rebranded the former "Host 50" media library: 2 GB of raw files processed into 45 MB of web-ready assets — a 97% size reduction. Includes 51 AI-classified WebP photos (exterior/interior) and two H.264 video loops under 15 MB total for mobile autoplay headers.</p>
                <p class="proposal-module-desc" data-lang="es">Rebranding completo del archivo de "Host 50": 2 GB de archivos brutos procesados a 45 MB listos para web — 97% de reducción. Incluye 51 fotos WebP clasificadas (exterior/interior) y dos loops de video H.264 de menos de 15 MB para encabezados móviles.</p>
                <a href="#report-g" class="proposal-module-log-link" aria-label="View Report G technical log">
                  <span data-lang="en">Technical Log G →</span>
                  <span data-lang="es">Registro Técnico G →</span>
                </a>
              </div>

              <div class="proposal-module-card proposal-module-card--batch1">
                <div class="proposal-module-icon">🚀</div>
                <p class="proposal-module-tag" data-lang="en">Report H · Infrastructure · Server Deployment</p>
                <p class="proposal-module-tag" data-lang="es">Informe H · Infraestructura · Despliegue en Servidor</p>
                <h4 class="proposal-module-title" data-lang="en">Standalone Server Deployment &amp; Public Menu Integration</h4>
                <h4 class="proposal-module-title" data-lang="es">Despliegue Autónomo en Servidor e Integración del Menú Público</h4>
                <p class="proposal-module-desc" data-lang="en">Generated a lightweight standalone HTML/CSS/JS framework for the book spotlight, bypassing all WordPress database queries to deliver 5-star mobile rendering speed. Mapped the bilingual language engine to loverlipsyachts.com/my-book/ and integrated a new nav menu button into the live WordPress header with zero structural drift.</p>
                <p class="proposal-module-desc" data-lang="es">Generamos un framework HTML/CSS/JS autónomo para la página del libro que omite consultas a la base de datos de WordPress, logrando velocidad de carga 5 estrellas en móvil. Integramos el selector de idioma y un botón de navegación en el header de WordPress sin alterar la estructura.</p>
                <a href="#report-h" class="proposal-module-log-link" aria-label="View Report H technical log">
                  <span data-lang="en">Technical Log H →</span>
                  <span data-lang="es">Registro Técnico H →</span>
                </a>
              </div>


              <div class="proposal-module-card proposal-module-card--batch1">
                <div class="proposal-module-icon">🗄️</div>
                <p class="proposal-module-tag" data-lang="en">Report I · System Architecture · Database CMS</p>
                <p class="proposal-module-tag" data-lang="es">Informe I · Arquitectura de Sistema · CMS en Base de Datos</p>
                <h4 class="proposal-module-title" data-lang="en">Database Integration &amp; Visual Book Editor Studio Deployment</h4>
                <h4 class="proposal-module-title" data-lang="es">Integración de Base de Datos y Despliegue del Estudio Visual de Edición del Libro</h4>
                <p class="proposal-module-desc" data-lang="en">Migrated the static book spotlight into a fully dynamic MySQL schema (lly_book_content) eliminating regex parsing and Error 1054 failures. Deployed the standalone Book Editor Studio at book_editor.php — pre-populated from live DB, with WebP image compression, bilingual content management, and Amazon/blog field support.</p>
                <p class="proposal-module-desc" data-lang="es">Migración de la landing estática del libro a un esquema dinámico MySQL (lly_book_content) eliminando parsing por regex y errores 1054. Desplegamos el Book Editor Studio en book_editor.php — precargado desde la BD activa, con compresión WebP, gestión bilingüe de contenido y soporte para campos de Amazon y blog.</p>
                <a href="#report-i" class="proposal-module-log-link" aria-label="View Report I technical log">
                  <span data-lang="en">Technical Log I →</span>
                  <span data-lang="es">Registro Técnico I →</span>
                </a>
              </div>

            </div><!-- /modules batch 1 -->

            <p class="proposal-sub-label">
              <span data-lang="en">Financial Breakdown — Batch 2</span>
              <span data-lang="es">Desglose Financiero — Lote 2</span>
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

          </div><!-- /batch 1 -->

          <!-- ── BATCH 0 — older (June 20, 2026) ──────────────────────── -->
          <div class="proposal-batch-block">

            <div class="proposal-batch-stamp">
              <span class="proposal-batch-stamp-icon">✅</span>
              <div>
                <p class="proposal-batch-stamp-label">
                  <span data-lang="en">Batch 1 · Account Settled</span>
                  <span data-lang="es">Lote 1 · Saldo Conciliado</span>
                </p>
                <p class="proposal-batch-stamp-title">
                  <span data-lang="en">Emergency Intervention &amp; Platform Recovery</span>
                  <span data-lang="es">Intervención de Emergencia y Recuperación de la Plataforma</span>
                </p>
              </div>
              <span class="proposal-batch-stamp-date">
                <span data-lang="en">Settled: June 20, 2026</span>
                <span data-lang="es">Liquidado: 20 de Junio, 2026</span>
              </span>
            </div>

            <div class="proposal-modules">

              <div class="proposal-module-card">
                <div class="proposal-module-icon">⚡</div>
                <p class="proposal-module-tag" data-lang="en">Report A · Performance · FOUC Remediation</p>
                <p class="proposal-module-tag" data-lang="es">Informe A · Rendimiento · Corrección de FOUC</p>
                <h4 class="proposal-module-title" data-lang="en">Zero-Delay Premium Layout Rendering</h4>
                <h4 class="proposal-module-title" data-lang="es">Renderizado Premium Instantáneo Sin Parpadeos</h4>
                <p class="proposal-module-desc" data-lang="en">Eliminated the visual flash on every page load by disabling stale cache loops and flushing the QUIC.cloud CDN layer. Elementor now renders in a single, instant pass — fully clean on every device.</p>
                <p class="proposal-module-desc" data-lang="es">Eliminamos el parpadeo visual de carga purgando bucles de caché obsoletos y el CDN QUIC.cloud. Elementor ahora renderiza en un solo paso instantáneo en todos los dispositivos.</p>
                <a href="#report-a" class="proposal-module-log-link" aria-label="View Report A technical log">
                  <span data-lang="en">Technical Log A →</span>
                  <span data-lang="es">Registro Técnico A →</span>
                </a>
              </div>

              <div class="proposal-module-card">
                <div class="proposal-module-icon">🔧</div>
                <p class="proposal-module-tag" data-lang="en">Report B · Recovery · Database Route Restoration</p>
                <p class="proposal-module-tag" data-lang="es">Informe B · Recuperación · Restauración de Rutas de BD</p>
                <h4 class="proposal-module-title" data-lang="en">Landing Page Recovery &amp; Route Rebuild</h4>
                <h4 class="proposal-module-title" data-lang="es">Rescate de Páginas y Reconstrucción de Rutas</h4>
                <p class="proposal-module-desc" data-lang="en">Rescued two flagship booking pages (Pink Lips and CNR Maranatha 120) that had gone completely offline due to broken database permalink routes. Both pages are live, indexed, and verified.</p>
                <p class="proposal-module-desc" data-lang="es">Rescatamos dos páginas clave (Pink Lips y CNR Maranatha 120) que estaban fuera de línea por rutas de base de datos rotas. Ambas están activas, indexadas y verificadas.</p>
                <a href="#report-b" class="proposal-module-log-link" aria-label="View Report B technical log">
                  <span data-lang="en">Technical Log B →</span>
                  <span data-lang="es">Registro Técnico B →</span>
                </a>
              </div>

              <div class="proposal-module-card">
                <div class="proposal-module-icon">🖼️</div>
                <p class="proposal-module-tag" data-lang="en">Report C · UI/UX · Gallery Polish</p>
                <p class="proposal-module-tag" data-lang="es">Informe C · UI/UX · Pulido de Galerías</p>
                <h4 class="proposal-module-title" data-lang="en">Gallery Asset &amp; Container Modernization</h4>
                <h4 class="proposal-module-title" data-lang="es">Modernización de Activos y Contenedores</h4>
                <p class="proposal-module-desc" data-lang="en">Removed the grey metadata text overlays appearing on photo hover across all fleet gallery albums via programmatic database cleanup. Every album now delivers a clean, premium browsing experience.</p>
                <p class="proposal-module-desc" data-lang="es">Eliminamos los textos de metadatos grises al hacer hover en los álbumes de fotos mediante limpieza automatizada de la base de datos. Todos los álbumes ofrecen ahora una experiencia visual limpia y premium.</p>
                <a href="#report-c" class="proposal-module-log-link" aria-label="View Report C technical log">
                  <span data-lang="en">Technical Log C →</span>
                  <span data-lang="es">Registro Técnico C →</span>
                </a>
              </div>

            </div><!-- /modules batch 0 -->

            <p class="proposal-sub-label">
              <span data-lang="en">Financial Breakdown — Batch 1</span>
              <span data-lang="es">Desglose Financiero — Lote 1</span>
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

          </div><!-- /batch 0 -->

        </div><!-- /Phase 0 -->

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

    <!-- Book Editor lives at book_editor.php — panel left empty, hidden by CSS -->
    <div id="hub-editor" class="hub-panel"></div>

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
