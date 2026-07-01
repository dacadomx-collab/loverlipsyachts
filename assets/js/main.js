/* =====================================================================
   LOVER LIPS YACHTS — assets/js/main.js
   Language · Theme · Accordion · SPA Hub · Smooth Scroll · Back-To-Top
   Arquitecto: DCD LABS | v2.4 — addEventListener Architecture | 2026-07-01

   Event model: ALL interactive behaviour is bound programmatically
   inside DOMContentLoaded via addEventListener. HTML is markup-only;
   zero onclick attributes remain on any element handled here.

   Defensive contract (every exported function):
   ① Non-null argument guard at function entry
   ② existence check (.length) before every querySelectorAll iteration
   ③ data-target presence check before SPA panel logic
   ④ localStorage / dataset writes wrapped in try/catch
   ===================================================================== */

/* ═══════════════════════════════════════════════════════════════════
   1. LANGUAGE ENGINE — decoupled from dashboard DOM
   ═══════════════════════════════════════════════════════════════════ */

function setLang(lang) {
  if (!document.body) return;
  if (lang !== 'en' && lang !== 'es') return;

  try { document.body.dataset.activeLang = lang; } catch (e) {}
  try { document.documentElement.lang    = lang; } catch (e) {}

  var btnEn = document.getElementById('btn-en');
  var btnEs = document.getElementById('btn-es');
  if (btnEn) {
    btnEn.classList.toggle('active', lang === 'en');
    btnEn.setAttribute('aria-pressed', lang === 'en' ? 'true' : 'false');
  }
  if (btnEs) {
    btnEs.classList.toggle('active', lang === 'es');
    btnEs.setAttribute('aria-pressed', lang === 'es' ? 'true' : 'false');
  }

  try { localStorage.setItem('llyCockpitLang', lang); } catch (e) {}
}

function restoreLang() {
  try {
    var saved = localStorage.getItem('llyCockpitLang');
    if (saved === 'es') setLang('es');
  } catch (e) {}
}

/* ═══════════════════════════════════════════════════════════════════
   2. THEME ENGINE
   ═══════════════════════════════════════════════════════════════════ */

function setTheme(theme) {
  if (theme !== 'light' && theme !== 'dark') return;
  try { document.documentElement.dataset.theme = theme; } catch (e) {}

  var btn = document.getElementById('theme-toggle');
  if (btn) {
    btn.setAttribute('aria-label',   theme === 'dark' ? 'Switch to Day Mode' : 'Switch to Night Mode');
    btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
  }

  try { localStorage.setItem('llyCockpitTheme', theme); } catch (e) {}
}

function toggleTheme() {
  var current = 'light';
  try { current = document.documentElement.dataset.theme || 'light'; } catch (e) {}
  setTheme(current === 'dark' ? 'light' : 'dark');
}

function restoreTheme() {
  try {
    var saved = localStorage.getItem('llyCockpitTheme');
    if (saved === 'dark') setTheme('dark');
  } catch (e) {}
}

/* ═══════════════════════════════════════════════════════════════════
   3. ACCORDION
   ═══════════════════════════════════════════════════════════════════ */

function toggleAccordion(triggerBtn) {
  if (!triggerBtn) return;
  var item = triggerBtn.closest('.accordion-item');
  if (!item) return;
  var isOpen = item.classList.contains('open');
  item.classList.toggle('open', !isOpen);
  triggerBtn.setAttribute('aria-expanded', String(!isOpen));
}

/* ═══════════════════════════════════════════════════════════════════
   4. SPA HUB CONTROLLER
   Only elements with a valid data-target attribute are treated as SPA
   tabs. External <a> links (e.g. Book Editor, Report log anchors) that
   lack data-target are silently skipped — browser handles them natively.
   ═══════════════════════════════════════════════════════════════════ */

function activateHub(button) {
  if (!button) return;
  var target = button.dataset && button.dataset.target;
  if (!target) return; /* external link — no SPA logic */

  var hubCards  = document.querySelectorAll('.hub-card');
  var hubPanels = document.querySelectorAll('.hub-panel');

  if (hubCards.length)  { hubCards.forEach(function (c)  { c.classList.remove('active'); }); }
  if (hubPanels.length) { hubPanels.forEach(function (p) { p.classList.remove('active'); }); }

  button.classList.add('active');

  var panel = document.getElementById(target);
  if (panel) { panel.classList.add('active'); }

  var navSection = document.querySelector('.hub-navigation-section');
  if (navSection) {
    window.scrollTo({
      top:      Math.max(0, navSection.offsetTop - 80),
      behavior: 'smooth'
    });
  }
}

function activateHubFromTopbar(navLink) {
  if (!navLink) return;
  var targetId = navLink.dataset && navLink.dataset.target;

  if (targetId) {
    var matchingHubCard = document.querySelector('.hub-card[data-target="' + targetId + '"]');
    if (matchingHubCard) { activateHub(matchingHubCard); }
  }

  var topbarLinks = document.querySelectorAll('.topbar-nav-link');
  if (topbarLinks.length) { topbarLinks.forEach(function (l) { l.classList.remove('active-nav'); }); }
  navLink.classList.add('active-nav');
}

/* ═══════════════════════════════════════════════════════════════════
   5. SMOOTH SCROLL
   Only intercepts local hash anchors. SPA tab switching is handled
   entirely by initTopbarNav / initHubCards (buttons, not <a> tags),
   so there is zero collision between smooth scroll and the hub SPA.
   ═══════════════════════════════════════════════════════════════════ */

function initSmoothScroll() {
  var anchors = document.querySelectorAll('a[href^="#"]');
  if (!anchors.length) return;

  anchors.forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var href = this.getAttribute('href');
      if (!href || href === '#') return;
      var target = document.querySelector(href);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   6. BACK TO TOP
   ═══════════════════════════════════════════════════════════════════ */

function initBackToTop() {
  var btn = document.getElementById('back-to-top');
  if (!btn) return;

  window.addEventListener('scroll', function () {
    btn.classList.toggle('visible', window.scrollY > 300);
  }, { passive: true });

  btn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   7. EARLY LANGUAGE LOCK  (sync, before DOMContentLoaded)
   Applies stored language to body immediately after HTML parse so the
   [data-lang] CSS never flashes the wrong language variant.
   ═══════════════════════════════════════════════════════════════════ */
(function () {
  try {
    var lang = null;
    try {
      var p = new URLSearchParams(window.location.search).get('lang');
      if (p === 'en' || p === 'es') lang = p;
    } catch (e) {}
    if (!lang) {
      try {
        var s = localStorage.getItem('llyCockpitLang');
        if (s === 'en' || s === 'es') lang = s;
      } catch (e) {}
    }
    if (lang && document.body) {
      document.documentElement.lang    = lang;
      document.body.dataset.activeLang = lang;
    }
  } catch (e) {}
}());

/* ═══════════════════════════════════════════════════════════════════
   8. URL PARAMETER RESOLVER
   ═══════════════════════════════════════════════════════════════════ */

function resolveUrlParams() {
  var params;
  try { params = new URLSearchParams(window.location.search); } catch (e) { return; }

  var langParam = params.get('lang');
  if (langParam === 'es' || langParam === 'en') { setLang(langParam); }

  var hash = window.location.hash.slice(1);
  if (!hash) return;

  var hubCard = document.querySelector('.hub-card[data-target="' + hash + '"]');
  if (!hubCard) return;

  function activateTargetPanel() {
    setTimeout(function () {
      var cards    = document.querySelectorAll('.hub-card');
      var panels   = document.querySelectorAll('.hub-panel');
      var navLinks = document.querySelectorAll('.topbar-nav-link');

      if (cards.length)    { cards.forEach(function (c)    { c.classList.remove('active');     }); }
      if (panels.length)   { panels.forEach(function (p)   { p.classList.remove('active');     }); }
      if (navLinks.length) { navLinks.forEach(function (l) { l.classList.remove('active-nav'); }); }

      hubCard.classList.add('active');
      var panel = document.getElementById(hash);
      if (panel) { panel.classList.add('active'); }

      var topbarLink = document.querySelector('.topbar-nav-link[data-target="' + hash + '"]');
      if (topbarLink) { topbarLink.classList.add('active-nav'); }

      var navSection = document.querySelector('.hub-navigation-section');
      if (navSection) {
        window.scrollTo({ top: Math.max(0, navSection.offsetTop - 80), behavior: 'smooth' });
      }
    }, 150);
  }

  if (document.readyState === 'complete') { activateTargetPanel(); }
  else { window.addEventListener('load', activateTargetPanel); }
}

/* ═══════════════════════════════════════════════════════════════════
   9. EVENT BINDING INITIALIZERS
   Each function is self-contained, guards on element existence, and
   silently returns when its target elements are absent from the page.
   Called from DOMContentLoaded — the single source of truth for all
   interactive wiring across every page that loads this script.
   ═══════════════════════════════════════════════════════════════════ */

/** Bind SPA tab switching to all .hub-card buttons with data-target. */
function initHubCards() {
  var cards = document.querySelectorAll('.hub-card');
  if (!cards.length) return;
  cards.forEach(function (card) {
    var target = card.dataset && card.dataset.target;
    if (!target) return; /* <a> external links have no data-target — skip */
    card.addEventListener('click', function (e) {
      e.preventDefault();
      activateHub(card);
    });
  });
}

/** Bind SPA panel switching to all .topbar-nav-link buttons with data-target. */
function initTopbarNav() {
  var links = document.querySelectorAll('.topbar-nav-link');
  if (!links.length) return;
  links.forEach(function (link) {
    var target = link.dataset && link.dataset.target;
    if (!target) return; /* <a> external links (Book Editor) — skip */
    link.addEventListener('click', function (e) {
      e.preventDefault();
      activateHubFromTopbar(link);
    });
  });
}

/** Bind language switching to #btn-en and #btn-es. */
function initLangToggle() {
  var btnEn = document.getElementById('btn-en');
  var btnEs = document.getElementById('btn-es');
  if (btnEn) { btnEn.addEventListener('click', function () { setLang('en'); }); }
  if (btnEs) { btnEs.addEventListener('click', function () { setLang('es'); }); }
}

/** Bind day/night toggle to #theme-toggle. */
function initThemeToggle() {
  var btn = document.getElementById('theme-toggle');
  if (btn) { btn.addEventListener('click', toggleTheme); }
}

/** Bind accordion open/close to all .accordion-trigger buttons. */
function initAccordion() {
  var triggers = document.querySelectorAll('.accordion-trigger');
  if (!triggers.length) return;
  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () { toggleAccordion(trigger); });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   10. INIT — readyState-aware entry point
   Deferred scripts run after HTML parse; document.readyState is
   already 'interactive' or 'complete' by that time.  Using the
   readyState check means the init fires immediately at script
   execution on fast/cached pages rather than waiting for the
   DOMContentLoaded event that may have already fired.
   ═══════════════════════════════════════════════════════════════════ */

function llyInitAll() {
  restoreTheme();       /* html[data-theme] from localStorage            */
  restoreLang();        /* sync toggle buttons (body attr set by IIFE)   */
  resolveUrlParams();   /* ?lang= and #hash activation                   */
  initHubCards();       /* .hub-card[data-target] → activateHub          */
  initTopbarNav();      /* .topbar-nav-link[data-target] → activateHubFromTopbar */
  initLangToggle();     /* #btn-en / #btn-es → setLang                   */
  initThemeToggle();    /* #theme-toggle → toggleTheme                   */
  initAccordion();      /* .accordion-trigger → toggleAccordion          */
  initSmoothScroll();   /* a[href^="#"] → scrollIntoView                 */
  initBackToTop();      /* #back-to-top → scrollTo(0)                   */
}

if (document.readyState === 'loading') {
  /* Script somehow ran before parse completed — wait for DOM ready */
  document.addEventListener('DOMContentLoaded', llyInitAll);
} else {
  /* DOM already parsed (normal path for a deferred script) — run now */
  llyInitAll();
}
