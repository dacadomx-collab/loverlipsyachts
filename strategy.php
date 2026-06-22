<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — strategy.php
 * Confidential business proposal: "Nine Lives" Organic Marketing Strategy.
 * Direct deep link from dashboard.php's Report E — requested as its own
 * URL (not an include like dashboard.php), so it validates the session
 * itself instead of relying on a gatekeeper-only constant.
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';

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
  <meta name="description" content="Lover Lips Yachts — Nine Lives Organic Marketing Strategy" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Marketing Strategy</title>
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
            <span>Marketing Strategy · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
          <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" aria-label="Switch to Night Mode" aria-pressed="false">
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/>
            </svg>
          </button>
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
         HEADER — Strategy Brief
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="strategy-title">
      <div class="container">
        <nav class="book-feature-editorial-nav strategy-back-nav" aria-label="Back to dashboard">
          <a href="index.php">
            <span data-lang="en">← Back to Dashboard</span>
            <span data-lang="es">← Volver al Panel</span>
          </a>
        </nav>
        <p class="section-label">
          <span data-lang="en">Business Strategy · Report E · Confidential</span>
          <span data-lang="es">Estrategia de Negocio · Reporte E · Confidencial</span>
        </p>
        <h1 class="section-title" id="strategy-title">
          <span data-lang="en">"Nine Lives" Organic Marketing <em>Strategy</em></span>
          <span data-lang="es">Estrategia de Marketing Orgánico <em>"Nine Lives"</em></span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          A 90-day zero-paid-media plan to launch the book on September 2, 2026 — built on guerrilla digital tactics, transactional SEO bridges, and ready-to-publish bilingual copy. For Lester's review and authorization.
        </p>
        <p class="section-subtitle" data-lang="es">
          Un plan de 90 días sin pauta pagada para lanzar el libro el 2 de septiembre de 2026 — construido sobre tácticas de guerrilla digital, puentes de SEO transaccional y copy bilingüe listo para publicar. Para revisión y autorización de Lester.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION A — AESTHETIC FUNNEL
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-truth" aria-labelledby="funnel-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Section A</span>
          <span data-lang="es">Sección A</span>
        </p>
        <h2 class="section-title" id="funnel-title">
          <span data-lang="en">Aesthetic <em>Funnel</em></span>
          <span data-lang="es"><em>Embudo</em> Estético</span>
        </h2>

        <div class="proposal-modules">

          <div class="proposal-module-card">
            <div class="proposal-module-icon">🎬</div>
            <p class="proposal-module-tag" data-lang="en">TofU · Top of Funnel</p>
            <p class="proposal-module-tag" data-lang="es">TofU · Tope de Embudo</p>
            <h4 class="proposal-module-title" data-lang="en">POV Reels — Borneo to the CNR Maranatha 120</h4>
            <h4 class="proposal-module-title" data-lang="es">Reels POV — De Borneo al CNR Maranatha 120</h4>
            <p class="proposal-module-desc" data-lang="en">A fast-cut video intercutting grainy archival jungle photos from New Guinea (Note: If personal archival photos are unavailable, copyright-free premium stock photos will be used to maintain the aesthetic intent) with 4K drone footage of the CNR Maranatha 120 cruising the Sea of Cortez. On-screen text: "They told me I wouldn't survive the jungle. Decades later, I built an empire on the ocean."</p>
            <p class="proposal-module-desc" data-lang="es">Un video de corte rápido que intercala fotos granuladas de archivo de la selva de Nueva Guinea (Nota: Si no se cuenta con fotos de archivo personales, se propondrá el uso de imágenes de stock gratis libres de derechos para mantener la intención estética) con tomas en 4K de drones del CNR Maranatha 120 navegando el Mar de Cortés. Texto en pantalla: "Me dijeron que no sobreviviría la selva. Décadas después, construí un imperio en el océano."</p>
          </div>

          <div class="proposal-module-card">
            <div class="proposal-module-icon">💬</div>
            <p class="proposal-module-tag" data-lang="en">MofU · Middle of Funnel</p>
            <p class="proposal-module-tag" data-lang="es">MofU · Medio de Embudo</p>
            <h4 class="proposal-module-title" data-lang="en">Storytelling — The Pink Glove Experience &amp; Recovery</h4>
            <h4 class="proposal-module-title" data-lang="es">Storytelling — La Experiencia Pink Glove y la Recuperación</h4>
            <p class="proposal-module-desc" data-lang="en">A carousel showing the 5-star service details (fresh ceviche, champagne, sunsets) combined with Lester's pull quotes about the sea as a process of physical and mental restoration after his transplant.</p>
            <p class="proposal-module-desc" data-lang="es">Un carrusel mostrando los detalles del servicio 5 estrellas (ceviche fresco, champagne, atardeceres) combinado con las "Pull Quotes" de Lester sobre cómo el mar es un proceso de restauración física y mental tras su trasplante.</p>
          </div>

          <div class="proposal-module-card">
            <div class="proposal-module-icon">✅</div>
            <p class="proposal-module-tag" data-lang="en">BofU · Bottom of Funnel</p>
            <p class="proposal-module-tag" data-lang="es">BofU · Fondo de Embudo</p>
            <h4 class="proposal-module-title" data-lang="en">ManyChat Conversion — Comment "VIVIR"</h4>
            <h4 class="proposal-module-title" data-lang="es">Conversión ManyChat — Comenta "VIVIR"</h4>
            <p class="proposal-module-desc" data-lang="en">Magnetic call to action: "Experience hospitality designed by a man who values every second of life. Comment 'VIVIR' to book your yacht and receive the first chapter of my memoir." The automated ManyChat chatbot takes over from here.</p>
            <p class="proposal-module-desc" data-lang="es">Llamado a la acción magnético: "Experimenta la hospitalidad diseñada por un hombre que valora cada segundo de vida. Comenta 'VIVIR' para reservar tu yate y recibir el primer capítulo de mis memorias." El Chatbot automatizado (ManyChat) toma el control aquí.</p>
            <p class="proposal-module-desc" data-lang="en">[ManyChat Sequence: User comments 'VIVIR' ➔ AI instantly delivers Chapter 1 of the memoir ➔ Flow seamlessly triggers the high-intent Yacht Charter availability engine].</p>
            <p class="proposal-module-desc" data-lang="es">[Secuencia ManyChat: El usuario comenta 'VIVIR' ➔ La IA entrega instantáneamente el Capítulo 1 de las memorias ➔ El flujo activa de inmediato el motor de disponibilidad del Chárter de Yates].</p>
          </div>

        </div><!-- /proposal-modules -->
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION B — SEO LOCAL BRIDGE CONTENT
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="seo-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Section B</span>
          <span data-lang="es">Sección B</span>
        </p>
        <h2 class="section-title" id="seo-title">
          <span data-lang="en">SEO Local &amp; <em>Bridge Content</em></span>
          <span data-lang="es">SEO Local y <em>Bridge Content</em></span>
        </h2>
        <p class="section-subtitle" data-lang="en">
          Blog articles attract traffic searching these golden keywords, deliver a stunning experience, and end with a subtle redirect to Lester's book to validate the brand's authority.
        </p>
        <p class="section-subtitle" data-lang="es">
          Los artículos del blog atraen tráfico buscando estas keywords de oro, presentan una experiencia deslumbrante y terminan con una redirección sutil al libro de Lester para validar la autoridad de la marca.
        </p>

        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th data-lang="en">Golden Keywords (Transactional)</th>
                <th data-lang="es">Keywords de Oro (Transaccionales)</th>
                <th data-lang="en">Intent</th>
                <th data-lang="es">Intención</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="2">Luxury Yacht Charter La Paz</td>
                <td data-lang="en"><span class="pill pill-pink">Booking-Ready</span></td>
                <td data-lang="es"><span class="pill pill-pink">Listo para Reservar</span></td>
              </tr>
              <tr>
                <td colspan="2">Private Boat Balandra Beach</td>
                <td data-lang="en"><span class="pill pill-pink">Booking-Ready</span></td>
                <td data-lang="es"><span class="pill pill-pink">Listo para Reservar</span></td>
              </tr>
              <tr>
                <td colspan="2">Espiritu Santo VIP Expedition</td>
                <td data-lang="en"><span class="pill pill-pink">Booking-Ready</span></td>
                <td data-lang="es"><span class="pill pill-pink">Listo para Reservar</span></td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="table-wrap u-mt-xs">
          <table class="data-table">
            <thead>
              <tr>
                <th data-lang="en">Experience Keywords</th>
                <th data-lang="es">Keywords de Experiencia</th>
                <th data-lang="en">Intent</th>
                <th data-lang="es">Intención</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="2">Swim with Whale Sharks Baja</td>
                <td data-lang="en"><span class="pill pill-gold">Top-of-Funnel</span></td>
                <td data-lang="es"><span class="pill pill-gold">Tope de Embudo</span></td>
              </tr>
              <tr>
                <td colspan="2">Baja California Sur Sunsets</td>
                <td data-lang="en"><span class="pill pill-gold">Top-of-Funnel</span></td>
                <td data-lang="es"><span class="pill pill-gold">Tope de Embudo</span></td>
              </tr>
              <tr>
                <td colspan="2">La Paz Expat Lifestyle</td>
                <td data-lang="en"><span class="pill pill-gold">Top-of-Funnel</span></td>
                <td data-lang="es"><span class="pill pill-gold">Tope de Embudo</span></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         SECTION C — COPY TEMPLATES (VERBATIM)
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-truth" aria-labelledby="copy-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">Section C</span>
          <span data-lang="es">Sección C</span>
        </p>
        <h2 class="section-title" id="copy-title">
          <span data-lang="en">Copy Templates — <em>Ready to Publish</em></span>
          <span data-lang="es">Plantillas de Copy — <em>Listas para Publicar</em></span>
        </h2>
        <p class="section-subtitle" data-lang="en">
          Designed to evade Facebook/LinkedIn spam filters through pure narrative — the book/charter link always goes in the first comment, never in the post body.
        </p>
        <p class="section-subtitle" data-lang="es">
          Diseñados para evadir los filtros de spam de Facebook/LinkedIn mediante narrativas puras — el enlace al libro/charter siempre va en el primer comentario, nunca en el cuerpo de la publicación.
        </p>

        <div class="accordion" role="list">

          <div class="accordion-item" role="listitem">
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="true">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">🩺</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Option 1 · Medical Survival &amp; Faith (Target: Resilience groups, Expats)</p>
                  <p class="accordion-trigger-title" data-lang="es">Opción 1 · Supervivencia Médica y Fe (Target: Grupos de resiliencia, Expats)</p>
                  <p class="accordion-trigger-sub" data-lang="en">Ready to publish · link in first comment</p>
                  <p class="accordion-trigger-sub" data-lang="es">Lista para publicar · enlace en el primer comentario</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <p data-lang="en">A few years ago, medical specialists looked at me and said I probably wouldn't survive. It wasn't the first time. As a child, I survived the unforgiving jungles of Borneo. Later, I survived open-heart surgery, liver cancer, and a liver transplant that statistics said was impossible. But the hardest part wasn't breathing; it was learning that surviving is not the same as living. I just finished writing the full story. Has anyone here gone through a health crisis that completely changed how they view a sunset? I'd love to read your stories below. 👇 (Link to book/charter in first comment)</p>
              <p data-lang="es">Hace algunos años, los médicos me miraron y dijeron que probablemente no sobreviviría. No era la primera vez. De niño sobreviví a la selva de Borneo. Más tarde, sobreviví a una cirugía de corazón abierto, cáncer y un trasplante de hígado estadísticamente inviable. Pero lo más difícil no fue seguir respirando; fue aprender que sobrevivir no es lo mismo que vivir. Acabo de terminar de escribir la historia completa. ¿Alguien aquí ha pasado por una crisis de salud que le haya cambiado por completo la forma de ver un atardecer? Me gustaría leer sus historias aquí abajo. 👇 (Enlace en el primer comentario)</p>
            </div>
          </div>

          <div class="accordion-item" role="listitem">
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="false">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">🌴</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Option 2 · Adventure &amp; Contrast (Target: Biography readers, Luxury travelers)</p>
                  <p class="accordion-trigger-title" data-lang="es">Opción 2 · Aventura y Contraste (Target: Lectores de biografías, Viajeros de lujo)</p>
                  <p class="accordion-trigger-sub" data-lang="en">Ready to publish · link in first comment</p>
                  <p class="accordion-trigger-sub" data-lang="es">Lista para publicar · enlace en el primer comentario</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <p data-lang="en">My story begins before I was even born. My father survived a Japanese WWII prison camp, and I grew up among remote tribes in New Guinea. I saw exploding bridges, heard whispers of poisoned arrows, and learned how fragile civilization truly is. Decades later, I ended up living a completely different life: CEO, yacht entrepreneur, and patient. The contrast between the savage and the sophisticated is the true bridge of resilience. What is the most incredible survival story your parents or grandparents told you? Let's talk. 👇 (Link in first comment)</p>
              <p data-lang="es">Mi historia comienza incluso antes de que yo naciera. Mi padre sobrevivió a un campo de prisioneros japonés en la Segunda Guerra Mundial y yo crecí entre tribus remotas de Nueva Guinea. Vi puentes explotar, escuché rumores de flechas envenenadas y aprendí lo frágil que es realmente la civilización. Décadas después, terminé viviendo una vida completamente distinta: CEO, empresario de yates y paciente. El contraste entre lo salvaje y lo sofisticado es el verdadero puente de nuestra resiliencia. ¿Cuál es la historia de supervivencia más increíble que te contaron tus padres o abuelos? Hablemos. 👇 (Enlace en el primer comentario)</p>
            </div>
          </div>

          <div class="accordion-item" role="listitem">
            <button class="accordion-trigger" onclick="toggleAccordion(this)" aria-expanded="false">
              <div class="accordion-trigger-left">
                <div class="accordion-icon-wrap">💼</div>
                <div>
                  <p class="accordion-trigger-title" data-lang="en">Option 3 · Business Success &amp; Rebuilding (Target: C-Level, LinkedIn Networking)</p>
                  <p class="accordion-trigger-title" data-lang="es">Opción 3 · Éxito Empresarial y Reconstrucción (Target: C-Level, LinkedIn Networking)</p>
                  <p class="accordion-trigger-sub" data-lang="en">Ready to publish · link in first comment</p>
                  <p class="accordion-trigger-sub" data-lang="es">Lista para publicar · enlace en el primer comentario</p>
                </div>
              </div>
              <svg class="accordion-chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="accordion-body">
              <p data-lang="en">I spent a large part of my life systematically chasing success. I built companies, sat in boardrooms, and shared moments with legends like Eric Clapton. Yet, while the business metrics pointed up, something inside was broken: three failed marriages, pride, and an ego that blocked introspection. People love talking about business success, but rarely about the price. This book is not the polished LinkedIn version of my life; it's the real one. Do you believe genuine professional success is possible if our personal life is in ruins? Open to debate. 👇 (Link in first comment)</p>
              <p data-lang="es">Pasé gran parte de mi vida persiguiendo sistemáticamente el éxito. Construí empresas, me senté en juntas directivas y compartí momentos con leyendas como Eric Clapton. Sin embargo, mientras las métricas comerciales subían, algo por dentro estaba roto: tres matrimonios fallidos, orgullo y un ego que bloqueaba la introspección. A la gente le encanta hablar del éxito empresarial, pero rara vez del precio a pagar. Este libro no es la versión pulida de LinkedIn; es la historia real. ¿Crees que es posible un éxito profesional genuino si nuestra vida personal está en ruinas? Abro el debate. 👇 (Enlace en el primer comentario)</p>
            </div>
          </div>

        </div><!-- /accordion -->

        <!-- ── 90-Day Organic Roadmap ──────────────────────────────────── -->
        <p class="proposal-sub-label u-mt-xs">
          <span data-lang="en">90-Day Organic Roadmap</span>
          <span data-lang="es">Cronograma Orgánico de 90 Días</span>
        </p>
        <div class="timeline" role="list">

          <div class="timeline-item active-phase" role="listitem">
            <div class="timeline-node" aria-label="Phase 1">1</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Days 1–30 · Setup &amp; Viral Attraction (TofU)</p>
              <p class="timeline-phase-tag" data-lang="es">Días 1–30 · Setup y Atracción Viral (TofU)</p>
              <h3 class="timeline-title" data-lang="en">Setup &amp; Viral Attraction</h3>
              <h3 class="timeline-title" data-lang="es">Setup y Atracción Viral</h3>
              <p class="timeline-desc" data-lang="en">Rollout of POV Reels and keyword automation.</p>
              <p class="timeline-desc" data-lang="es">Implementación de Reels POV y automatización de palabras clave.</p>
            </div>
          </div>

          <div class="timeline-item" role="listitem">
            <div class="timeline-node" aria-label="Phase 2">2</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Days 31–60 · Trust Nurturing (MofU)</p>
              <p class="timeline-phase-tag" data-lang="es">Días 31–60 · Nutrición de Confianza (MofU)</p>
              <h3 class="timeline-title" data-lang="en">Trust Nurturing</h3>
              <h3 class="timeline-title" data-lang="es">Nutrición de Confianza</h3>
              <p class="timeline-desc" data-lang="en">Rollout of carousels with Pink Glove Experience testimonials and resilience quotes.</p>
              <p class="timeline-desc" data-lang="es">Despliegue de carruseles con testimonios de la Pink Glove Experience y citas de resiliencia.</p>
            </div>
          </div>

          <div class="timeline-item" role="listitem">
            <div class="timeline-node" aria-label="Phase 3">3</div>
            <div class="timeline-card">
              <p class="timeline-phase-tag" data-lang="en">Days 61–90 · Absolute Conversion (BofU)</p>
              <p class="timeline-phase-tag" data-lang="es">Días 61–90 · Conversión Absoluta (BofU)</p>
              <h3 class="timeline-title" data-lang="en">Absolute Conversion</h3>
              <h3 class="timeline-title" data-lang="es">Conversión Absoluta</h3>
              <p class="timeline-desc" data-lang="en">Official book launch (September 2, 2026), qualified lead closing, and authority SEO redirects.</p>
              <p class="timeline-desc" data-lang="es">Lanzamiento oficial del libro (2 de septiembre, 2026), cierre de leads calificados y redirecciones SEO de autoridad.</p>
            </div>
          </div>

        </div><!-- /timeline -->

      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         AI CONTROL BLOCK — Conversion Directive
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="ai-control-title">
      <div class="container">

        <p class="section-label">
          <span data-lang="en">AI Conversion Directive</span>
          <span data-lang="es">Directriz de Conversión de la IA</span>
        </p>
        <h2 class="section-title" id="ai-control-title">
          <span data-lang="en">Operational <em>Lock</em></span>
          <span data-lang="es"><em>Cerrojo</em> Operativo</span>
        </h2>

        <div class="kbase-card-note u-mt-xs">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span data-lang="en">The chatbot operates under the unbreakable lock <strong>NO_PRICE_WITHOUT_LEAD_DATA</strong> — it answers complex operational FAQs but only delivers a precise quote after capturing: <strong>Date + Guest Count (PAX) + Route + Contact</strong>.</span>
          <span data-lang="es">El chatbot opera bajo el cerrojo inquebrantable <strong>NO_PRICE_WITHOUT_LEAD_DATA</strong> — responde FAQs operativas complejas pero solo entrega una cotización precisa tras capturar: <strong>Fecha + Número de Invitados (PAX) + Ruta + Contacto</strong>.</span>
        </div>

        <div class="kbase-card-note">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span data-lang="en">If the AI detects a C-Level or UHNWI (Ultra-High-Net-Worth Individual) profile, it triggers an immediate high-priority human escalation alert, routing that high-value lead directly to Lester's personal phone or the premium sales team for a personalized white-glove close, bypassing standard automated quoting.</span>
          <span data-lang="es">Si la IA detecta un perfil C-Level o UHNWI (individuo de patrimonio ultra alto), activa una alerta de escalación humana inmediata de alta prioridad, dirigiendo ese lead de alto valor directamente al teléfono personal de Lester o al equipo de ventas premium para un cierre personalizado de guante blanco, omitiendo la cotización automatizada estándar.</span>
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
        <span data-lang="en">Marketing Strategy · Confidential · Owner Authorization Required</span>
        <span data-lang="es">Estrategia de Marketing · Confidencial · Requiere Autorización del Propietario</span>
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
