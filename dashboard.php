<?php
declare(strict_types=1);

if (!defined('LLY_DASHBOARD_GATEKEEPER')) {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — Owner Control Dashboard" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Owner Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css" />
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
            <button class="topbar-nav-link active-nav" data-target="hub-reports" onclick="activateHubFromTopbar(this)">
              <span data-lang="en">Reports</span><span data-lang="es">Informes</span>
            </button>
            <button class="topbar-nav-link" data-target="hub-timeline" onclick="activateHubFromTopbar(this)">
              <span data-lang="en">Timeline</span><span data-lang="es">Línea de Tiempo</span>
            </button>
            <button class="topbar-nav-link" data-target="hub-alliance" onclick="activateHubFromTopbar(this)">
              <span data-lang="en">Alliance</span><span data-lang="es">Alianza</span>
            </button>
          </nav>

          <!-- Theme toggle -->
          <button
            class="theme-toggle"
            id="theme-toggle"
            onclick="toggleTheme()"
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
            <button class="lang-btn active" id="btn-en" onclick="setLang('en')" aria-pressed="true">EN</button>
            <button class="lang-btn"        id="btn-es" onclick="setLang('es')" aria-pressed="false">ES</button>
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

          <button class="hub-card active" data-target="hub-reports" onclick="activateHub(this)">
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

          <button class="hub-card" data-target="hub-timeline" onclick="activateHub(this)">
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

          <button class="hub-card" data-target="hub-alliance" onclick="activateHub(this)">
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

          <!-- Report D: Global SEO & Performance Architecture -->
          <article class="report-card report-card--featured">
            <div class="report-card-head">
              <div class="report-number report-number--gold">D</div>
              <div class="report-card-meta">
                <p class="report-date">
                  <span data-lang="en">June 20, 2026</span>
                  <span data-lang="es">20 de Junio, 2026</span>
                </p>
                <span class="pill pill-orange">
                  <span data-lang="en">Awaiting Reconciliation</span>
                  <span data-lang="es">Por Conciliar</span>
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
          <article class="report-card">
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
          <article class="report-card">
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
          <article class="report-card">
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
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="true">
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
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="false">
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
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="false">
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
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="false">
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
