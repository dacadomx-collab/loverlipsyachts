<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — reportes.php
 * Completed technical work, newest first. Standalone page — validates its
 * own session instead of relying on a gatekeeper-only constant.
 */

require_once __DIR__ . '/api/conexion.php';
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/dev_bypass.php';

if (!lly_is_authenticated()) {
    header('Location: index.php');
    exit;
}

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
  <meta name="description" content="Lover Lips Yachts — Technical Progress Reports" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Reports</title>
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
            <span>Reports · Confidential</span>
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
         HEADER — Intro + Status Legend
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="reports-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Work Completed</span>
          <span data-lang="es">Trabajo Realizado</span>
        </p>
        <h1 class="section-title" id="reports-title">
          <span data-lang="en">Technical Progress <em>Reports</em></span>
          <span data-lang="es">Informes de Progreso <em>Técnico</em></span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          This page lists every technical and operational job already completed, ordered by date — the most recent work first.
        </p>
        <p class="section-subtitle" data-lang="es">
          En esta página se encuentran todos los trabajos técnicos y operativos ya realizados, ordenados por fecha (mostrando el último trabajo realizado al principio).
        </p>

        <!-- ── Status Legend ─────────────────────────────────────────── -->
        <div class="status-legend" role="list" aria-label="Status legend">
          <div class="status-legend-item col-xs-12" role="listitem">
            <span class="pill pill-green">🟢
              <span data-lang="en">Account Settled</span>
              <span data-lang="es">Saldo Concluido</span>
            </span>
            <p>
              <span data-lang="en">Work finished and payment applied &amp; reconciled.</span>
              <span data-lang="es">Trabajo terminado y pago aplicado/conciliado.</span>
            </p>
          </div>
          <div class="status-legend-item col-xs-12" role="listitem">
            <span class="pill pill-orange">🟡
              <span data-lang="en">Awaiting Reconciliation</span>
              <span data-lang="es">Por Conciliar</span>
            </span>
            <p>
              <span data-lang="en">Work finished — payment still pending reflection in the Alliance statement.</span>
              <span data-lang="es">Trabajo terminado, pendiente de reflejar el pago en la Alianza.</span>
            </p>
          </div>
        </div>

        <p class="status-legend-note" data-lang="en">
          Note: this page only contains reports of finished work.
        </p>
        <p class="status-legend-note" data-lang="es">
          Nota: esta página solo contiene informes de trabajo terminado.
        </p>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         TECHNICAL PROGRESS REPORTS
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-truth" aria-label="Reports Grid">
      <div class="container">

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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
                  <span data-lang="es">Saldo Concluido</span>
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
