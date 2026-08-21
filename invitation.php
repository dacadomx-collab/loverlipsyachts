<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — invitation.php
 * VIP invitation landing page — "Nine Lives. One True Love" book launch event.
 * Served from two URLs (same environment-adaptive trick as book.php):
 *   • /cockpit/invitation.php   (direct access inside the portal)
 *   • /my-book/invitation.php   (public-facing, no login wall)
 * Fully static — no DB read/write. One-night event, no CMS override needed.
 */

$_llyHost    = (string) ($_SERVER['HTTP_HOST']   ?? '');
$_llySrvAddr = (string) ($_SERVER['SERVER_ADDR'] ?? '');
$_llyRemAddr = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

$_llyLocal =
    in_array($_llyHost,    ['localhost', '127.0.0.1'], true) ||
    str_starts_with($_llyHost, 'localhost:') ||
    str_starts_with($_llyHost, '127.0.0.1:') ||
    in_array($_llySrvAddr, ['127.0.0.1', '::1'], true) ||
    in_array($_llyRemAddr, ['127.0.0.1', '::1'], true);

$baseAssetsUrl = $_llyLocal ? '/loverlipsyachts/assets/' : '/cockpit/assets/';
unset($_llyHost, $_llySrvAddr, $_llyRemAddr, $_llyLocal);

/* ── RSVP — WhatsApp deep link (published business number, wa.me format,
 * same LLY_WHATSAPP_CONTACT used in api/public/l.php) ─────────────────── */
const LLY_INVITE_WHATSAPP = '17022048894';
$rsvpText = "Hi Fabiola & Lester! ✅ YES — I'd love to attend the Nine Lives launch on Sept 2. Party of: ___"
    . "\n¡Hola! Sí, me encantaría asistir. Número de personas: ___";
$rsvpHref = 'https://wa.me/' . LLY_INVITE_WHATSAPP . '?text=' . rawurlencode($rsvpText);

/* ── Video — YouTube ID extracted from https://youtu.be/e4Y9dNy83Dc ───── */
const LLY_INVITE_YT_ID = 'e4Y9dNy83Dc';
$ytThumb = 'https://img.youtube.com/vi/' . LLY_INVITE_YT_ID . '/hqdefault.jpg';
$ytEmbed = 'https://www.youtube-nocookie.com/embed/' . LLY_INVITE_YT_ID . '?autoplay=1&rel=0';
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Request your invitation — Fabiola & Lester Keizer celebrate the launch of Nine Lives. One True Love, September 2, 2026 at The Curvy Bean, La Paz." />
  <meta name="robots" content="index, follow" />
  <title>Request for Invitation — Nine Lives. One True Love | Lover Lips Yachts</title>
  <link rel="stylesheet" href="<?= $baseAssetsUrl ?>css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
  <link rel="icon" type="image/png" href="<?= $baseAssetsUrl ?>img/logo.png" />
  <script src="<?= $baseAssetsUrl ?>js/theme-init.js"></script>
</head>

<body data-active-lang="en">

  <header class="topbar" role="banner">
    <div class="container">
      <div class="topbar-inner">
        <a href="https://loverlipsyachts.com/" class="topbar-logo" aria-label="Lover Lips Yachts — Home">
          <img class="logo-day"   src="<?= $baseAssetsUrl ?>img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="<?= $baseAssetsUrl ?>img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>VIP Invitation</span>
          </div>
        </a>
        <div class="topbar-actions">
          <button class="theme-toggle" id="theme-toggle" aria-label="Switch to Night Mode" aria-pressed="false">
            <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <svg class="icon-sun"  xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 5a7 7 0 100 14A7 7 0 0012 5z"/></svg>
          </button>
          <div class="lang-toggle" role="group" aria-label="Language / Idioma">
            <button class="lang-btn active" id="btn-en" aria-pressed="true">EN</button>
            <button class="lang-btn"        id="btn-es" aria-pressed="false">ES</button>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main>

    <!-- ══ HERO — always-dark, black + gold, high-contrast VIP invitation ══ -->
    <section class="invite-hero" aria-labelledby="invite-hero-title">
      <div class="container">
        <p class="invite-eyebrow">
          <span data-lang="en">The Hottest Ticket in La Paz</span>
          <span data-lang="es">El Boleto Más Codiciado de La Paz</span>
        </p>
        <p class="invite-kicker">
          <span data-lang="en">One Night Only</span>
          <span data-lang="es">Una Sola Noche</span>
        </p>

        <h1 class="invite-title" id="invite-hero-title">
          <span data-lang="en">📚 Request for Invitation ❤️</span>
          <span data-lang="es">📚 Solicita tu Invitación ❤️</span>
        </h1>

        <p class="invite-hosts">
          <span data-lang="en">Fabiola &amp; Lester Keizer invite our La Paz friends to request an invitation to a very special evening celebrating the launch of:</span>
          <span data-lang="es">Fabiola y Lester Keizer invitan a nuestros amigos de La Paz a solicitar una invitación para una noche muy especial, celebrando el lanzamiento de:</span>
        </p>

        <p class="invite-booktitle">Nine Lives. One True Love.</p>

        <div class="book-authority-ribbon">
          <span class="book-authority-badge">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4h10l1.2 2.4H5.8L7 4zm-2.5 4h15a1 1 0 0 1 1 1v.6c0 .4-.3.7-.7.8-2.2.6-5.8 1.1-9.8 1.1s-7.6-.5-9.8-1.1c-.4-.1-.7-.4-.7-.8V9a1 1 0 0 1 1-1zM5 11.2c2.4.5 5.2.8 7.5.8s5.1-.3 7.5-.8V19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7.8z"/></svg>
            <span data-lang="en">🏆 Currently #1 on Amazon New Release — Organ Transplants</span>
            <span data-lang="es">🏆 Actualmente #1 en Nuevos Lanzamientos de Amazon — Trasplante de Órganos</span>
          </span>
        </div>

        <div class="invite-hero-frame">
          <img src="<?= $baseAssetsUrl ?>img/HotNewRelease.jpeg" alt="Nine Lives. One True Love — Amazon #1 New Release" loading="eager" />
        </div>
      </div>
    </section>

    <!-- ══ EVENT DETAILS — ticket stub ══════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="invite-details-title">
      <div class="container">
        <p class="section-label" id="invite-details-title">
          <span data-lang="en">The Details</span>
          <span data-lang="es">Los Detalles</span>
        </p>
        <div class="invite-ticket">
          <div class="invite-ticket-row">
            <span class="invite-ticket-icon">📅</span>
            <span data-lang="en">Wednesday, September 2, 2026</span>
            <span data-lang="es">Miércoles, 2 de Septiembre, 2026</span>
          </div>
          <div class="invite-ticket-row">
            <span class="invite-ticket-icon">🕡</span>
            <span>6:31 PM</span>
          </div>
          <div class="invite-ticket-row">
            <span class="invite-ticket-icon">📍</span>
            <span>The Curvy Bean — La Paz</span>
          </div>
          <div class="invite-ticket-row">
            <span class="invite-ticket-icon">🥂</span>
            <span data-lang="en">Adults Only (21+)</span>
            <span data-lang="es">Solo Adultos (21+)</span>
          </div>
          <p class="invite-ticket-tagline">
            <span data-lang="en">Champagne. Conversation. And Classics. 🎹🥂</span>
            <span data-lang="es">Champaña. Conversación. Y Clásicos. 🎹🥂</span>
          </p>
        </div>
      </div>
    </section>

    <!-- ══ MORE THAN A BOOK LAUNCH ═══════════════════════════════════════ -->
    <section class="section invite-celebration">
      <div class="container">
        <p>
          <span data-lang="en">This is more than a book launch. It's a celebration of life, second chances, friendship — and the wonderful people of La Paz who have become part of our lives.</span>
          <span data-lang="es">Esto es más que el lanzamiento de un libro. Es una celebración de la vida, las segundas oportunidades, la amistad — y la maravillosa gente de La Paz que se ha convertido en parte de nuestras vidas.</span>
        </p>
      </div>
    </section>

    <!-- ══ MINI CONCERT ═══════════════════════════════════════════════ -->
    <section class="section section-white invite-concert" aria-labelledby="invite-concert-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Plus</span>
          <span data-lang="es">Además</span>
        </p>
        <h2 class="section-title" id="invite-concert-title">
          <span data-lang="en">🎹 A Mini Concert by the <em>Author</em></span>
          <span data-lang="es">🎹 Un Mini Concierto del <em>Autor</em></span>
        </h2>
        <p class="invite-concert-bio">
          <span data-lang="en">From a rock band at 14 and a vinyl organ LP at 17, to classical pipe organ, and he even played at The Stardust Casino in Las Vegas — <span class="invite-concert-highlight">Lester will play a selection of Classics.</span></span>
          <span data-lang="es">Desde una banda de rock a los 14 años y un LP de órgano vinílico a los 17, hasta el órgano de tubos clásico — e incluso tocó en el Stardust Casino de Las Vegas — <span class="invite-concert-highlight">Lester interpretará una selección de Clásicos.</span></span>
        </p>

        <blockquote class="pull-quote-vip">
          <p data-lang="en">YODO™️<br>You Only Die Once.<br>You LIVE Every Day.</p>
          <p data-lang="es">YODO™️<br>Solo Se Muere Una Vez.<br>VIVE Cada Día.</p>
        </blockquote>
      </div>
    </section>

    <!-- ══ PODCAST / VIDEO ══════════════════════════════════════════════ -->
    <section class="section invite-podcast" aria-labelledby="invite-podcast-title">
      <div class="container">
        <p class="section-label" id="invite-podcast-title">
          <span data-lang="en">Learn More</span>
          <span data-lang="es">Conoce Más</span>
        </p>
        <h2 class="section-title">
          <span data-lang="en">Hear the Story <em>First</em></span>
          <span data-lang="es">Escucha la Historia <em>Primero</em></span>
        </h2>
        <p class="invite-rsvp-lead">
          <span data-lang="en">For more information on the book, listen to this recent podcast sent to over 53,000 subscribers on "Almost Retired in Mexico."</span>
          <span data-lang="es">Para más información sobre el libro, escucha este podcast reciente enviado a más de 53,000 suscriptores de "Almost Retired in Mexico."</span>
        </p>

        <button type="button" class="invite-video-card" id="invite-video-open" aria-label="Play podcast video">
          <span class="invite-video-thumb">
            <img src="<?= htmlspecialchars($ytThumb, ENT_QUOTES, 'UTF-8') ?>" alt="Almost Retired in Mexico — podcast preview" loading="lazy" />
            <span class="invite-video-play" aria-hidden="true">
              <span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
              </span>
            </span>
          </span>
          <span class="invite-video-caption">
            <strong data-lang="en">Almost Retired in Mexico</strong><strong data-lang="es">Almost Retired in Mexico</strong>
            <span data-lang="en"> — 53,000+ subscribers</span>
            <span data-lang="es"> — más de 53,000 suscriptores</span>
          </span>
        </button>
      </div>
    </section>

    <!-- ══ RSVP ══════════════════════════════════════════════════════════ -->
    <section class="section section-white invite-rsvp" id="rsvp" aria-labelledby="invite-rsvp-title">
      <div class="container">
        <p class="section-label" id="invite-rsvp-title">RSVP</p>
        <p class="invite-rsvp-lead">
          <span data-lang="en">If you want to attend just reply yes and for how many people.</span>
          <span data-lang="es">Si deseas asistir, solo responde "sí" y para cuántas personas.</span>
        </p>

        <a href="<?= htmlspecialchars($rsvpHref, ENT_QUOTES, 'UTF-8') ?>" class="invite-rsvp-btn" target="_blank" rel="noopener noreferrer">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-1.746-.872-2.888-1.556-4.035-3.528-.305-.526.305-.489.873-1.627.099-.198.05-.371-.05-.52-.099-.149-.668-1.61-.916-2.207-.242-.579-.487-.5-.668-.51-.173-.01-.371-.012-.57-.012-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.064 2.875 1.213 3.074.148.198 2.04 3.112 4.94 4.236 2.901 1.124 2.901.749 3.422.701.52-.05 1.758-.718 2.005-1.413.247-.694.247-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12.05 2C6.498 2 2 6.498 2 12.05c0 1.949.546 3.766 1.49 5.32L2 22l4.738-1.453a9.96 9.96 0 0 0 5.312 1.503h.004c5.552 0 10.05-4.498 10.05-10.05C22.104 6.498 17.606 2 12.05 2zm0 18.18a8.12 8.12 0 0 1-4.13-1.13l-.296-.176-3.07.941.951-3.083-.193-.317a8.12 8.12 0 0 1-1.222-4.365c0-4.502 3.658-8.16 8.16-8.16 4.502 0 8.16 3.658 8.16 8.16 0 4.502-3.658 8.13-8.36 8.13z"/></svg>
          <span data-lang="en">RSVP — Reply YES</span>
          <span data-lang="es">RSVP — Responder SÍ</span>
        </a>

        <p class="invite-signoff">
          <span data-lang="en">Thanks and hope to see you there.</span>
          <span data-lang="es">Gracias, esperamos verte ahí.</span>
          <br><strong>Fabiola &amp; Lester Keizer</strong>
        </p>
      </div>
    </section>

  </main>

  <!-- ══ VIDEO DIALOG — lazy iframe, injected only on open ══════════════ -->
  <dialog class="chapter-dialog invite-video-dialog" id="invite-video-dialog" aria-labelledby="invite-video-dialog-title">
    <div class="chapter-dialog-inner">
      <button class="chapter-dialog-close" type="button" id="invite-video-close" aria-label="Close">✕</button>
      <p class="chapter-dialog-eyebrow" id="invite-video-dialog-title">
        <span data-lang="en">Podcast — Almost Retired in Mexico</span>
        <span data-lang="es">Podcast — Almost Retired in Mexico</span>
      </p>
      <div class="invite-video-frame" id="invite-video-frame"></div>
    </div>
  </dialog>

  <footer class="footer" role="contentinfo">
    <div class="container">
      <div class="footer-logo">
        <img class="logo-day"   src="<?= $baseAssetsUrl ?>img/logo.png"  alt="Lover Lips Yachts" />
        <img class="logo-night" src="<?= $baseAssetsUrl ?>img/logo2.png" alt="Lover Lips Yachts" />
      </div>
      <p>
        <strong>Lover Lips Yachts</strong> &nbsp;·&nbsp;
        <span data-lang="en">Nine Lives. One True Love — VIP Launch Invitation</span>
        <span data-lang="es">Nine Lives. One True Love — Invitación VIP de Lanzamiento</span>
      </p>
      <p class="u-mt-xs">
        <span data-lang="en">By Lester Keizer &nbsp;·&nbsp; Wednesday, September 2, 2026 &nbsp;·&nbsp; The Curvy Bean, La Paz</span>
        <span data-lang="es">Por Lester Keizer &nbsp;·&nbsp; Miércoles, 2 de Septiembre, 2026 &nbsp;·&nbsp; The Curvy Bean, La Paz</span>
      </p>
    </div>
  </footer>

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="<?= $baseAssetsUrl ?>js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>

  <!-- Page-specific script — same convention as agenda.php/chat-lab.php:
       small inline behaviors never joined into the shared main.js bundle. -->
  <script>
  (function () {
    var openBtn  = document.getElementById('invite-video-open');
    var dialog   = document.getElementById('invite-video-dialog');
    var closeBtn = document.getElementById('invite-video-close');
    var frame    = document.getElementById('invite-video-frame');
    if (!openBtn || !dialog || !closeBtn || !frame) return;

    var embedSrc = <?= json_encode($ytEmbed, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    openBtn.addEventListener('click', function () {
      var iframe = document.createElement('iframe');
      iframe.src = embedSrc;
      iframe.title = 'Almost Retired in Mexico — podcast';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      frame.innerHTML = '';
      frame.appendChild(iframe);
      dialog.showModal();
    });

    function closeDialog() {
      dialog.close();
      frame.innerHTML = ''; /* stop playback immediately on close */
    }
    closeBtn.addEventListener('click', closeDialog);
    dialog.addEventListener('click', function (e) {
      if (e.target === dialog) closeDialog(); /* click on ::backdrop area */
    });
  }());
  </script>

</body>
</html>
