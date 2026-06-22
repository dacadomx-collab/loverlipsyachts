/* =====================================================================
   LOVER LIPS YACHTS — assets/js/main.js
   Language Engine · Theme Engine · Accordion · Scroll
   Arquitecto: DCD LABS | v2.0 | 2026-05-30
   ===================================================================== */

/* ─── Language System ─────────────────────────────────────────────── */

function setLang(lang) {
  document.body.dataset.activeLang = lang;
  document.documentElement.lang = lang;

  var btnEn = document.getElementById('btn-en');
  var btnEs = document.getElementById('btn-es');
  if (btnEn) { btnEn.classList.toggle('active', lang === 'en'); btnEn.setAttribute('aria-pressed', lang === 'en'); }
  if (btnEs) { btnEs.classList.toggle('active', lang === 'es'); btnEs.setAttribute('aria-pressed', lang === 'es'); }

  try { localStorage.setItem('llyCockpitLang', lang); } catch (e) {}
}

function restoreLang() {
  try {
    var saved = localStorage.getItem('llyCockpitLang');
    if (saved === 'es') setLang('es');
  } catch (e) {}
}

/* ─── Theme System ────────────────────────────────────────────────── */

function setTheme(theme) {
  document.documentElement.dataset.theme = theme;

  var themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.setAttribute('aria-label', theme === 'dark' ? 'Switch to Day Mode' : 'Switch to Night Mode');
    themeToggle.setAttribute('aria-pressed', theme === 'dark');
  }

  try { localStorage.setItem('llyCockpitTheme', theme); } catch (e) {}
}

function toggleTheme() {
  var current = document.documentElement.dataset.theme || 'light';
  setTheme(current === 'dark' ? 'light' : 'dark');
}

function restoreTheme() {
  try {
    var saved = localStorage.getItem('llyCockpitTheme');
    if (saved === 'dark') setTheme('dark');
  } catch (e) {}
}

/* ─── Accordion System ────────────────────────────────────────────── */

function toggleAccordion(triggerBtn) {
  var item = triggerBtn.closest('.accordion-item');
  if (!item) return;
  var isOpen = item.classList.contains('open');
  item.classList.toggle('open', !isOpen);
  triggerBtn.setAttribute('aria-expanded', String(!isOpen));
}

/* ─── Back to Top ─────────────────────────────────────────────────── */

function initBackToTop() {
  var btn = document.getElementById('back-to-top');
  if (!btn) return;

  window.addEventListener('scroll', function () {
    btn.classList.toggle('visible', window.scrollY > 300);
  });

  btn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ─── Smooth Scroll ───────────────────────────────────────────────── */

function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var targetId = this.getAttribute('href');
      if (targetId === '#') return;
      var target = document.querySelector(targetId);
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });
}

/* ─── SPA Hub Controller ──────────────────────────────────────────── */

function activateHubFromTopbar(navLink) {
  var targetId = navLink.dataset.target;
  var matchingHubCard = document.querySelector('.hub-card[data-target="' + targetId + '"]');
  if (matchingHubCard) { activateHub(matchingHubCard); }

  document.querySelectorAll('.topbar-nav-link').forEach(function (l) { l.classList.remove('active-nav'); });
  navLink.classList.add('active-nav');
}

function activateHub(button) {
  var target = button.dataset.target;

  document.querySelectorAll('.hub-card').forEach(function (card) {
    card.classList.remove('active');
  });

  document.querySelectorAll('.hub-panel').forEach(function (panel) {
    panel.classList.remove('active');
  });

  button.classList.add('active');

  var panel = document.getElementById(target);
  if (panel) { panel.classList.add('active'); }

  var navSection = document.querySelector('.hub-navigation-section');
  if (navSection) {
    window.scrollTo({
      top: navSection.offsetTop - 80,
      behavior: 'smooth'
    });
  }
}

/* ─── URL Parameter Resolver ──────────────────────────────────────── */

/* STAGE 1 — Immediate language lock.
   Runs synchronously the moment this defer script is parsed —
   before DOMContentLoaded — so no English flash occurs on
   incoming bilingual links. document.body is guaranteed to
   exist because defer scripts execute after full HTML parsing.   */
(function () {
  try {
    var lang = new URLSearchParams(window.location.search).get('lang');
    if (lang === 'es' || lang === 'en') {
      document.documentElement.lang    = lang;
      document.body.dataset.activeLang = lang;
    }
  } catch (e) {}
}());

function resolveUrlParams() {
  var params;
  try { params = new URLSearchParams(window.location.search); } catch (e) { return; }

  /* Sync toggle buttons + localStorage with the language already
     applied by the IIFE above.                                    */
  var langParam = params.get('lang');
  if (langParam === 'es' || langParam === 'en') {
    setLang(langParam);
  }

  /* STAGE 2 — SPA panel activation.
     We need the full page painted before activating a panel that
     starts as display:none. Two guards achieve this:
       a) window.load  — all assets rendered, heights settled.
       b) setTimeout(150) — absorbs any residual CSS transition
          registration so offsetTop is accurate when we scroll.   */
  var hash    = window.location.hash.slice(1);
  if (!hash) return;

  var hubCard = document.querySelector('.hub-card[data-target="' + hash + '"]');
  if (!hubCard) return;

  function activateTargetPanel() {
    setTimeout(function () {

      /* Reset every hub indicator to a clean slate */
      document.querySelectorAll('.hub-card').forEach(function (c) {
        c.classList.remove('active');
      });
      document.querySelectorAll('.hub-panel').forEach(function (p) {
        p.classList.remove('active');
      });
      document.querySelectorAll('.topbar-nav-link').forEach(function (l) {
        l.classList.remove('active-nav');
      });

      /* Activate the requested panel + matching nav indicators */
      hubCard.classList.add('active');

      var panel = document.getElementById(hash);
      if (panel) { panel.classList.add('active'); }

      var topbarLink = document.querySelector(
        '.topbar-nav-link[data-target="' + hash + '"]'
      );
      if (topbarLink) { topbarLink.classList.add('active-nav'); }

      /* Scroll to hub nav now that the panel is visible and the
         browser has a valid layout to measure against.           */
      var navSection = document.querySelector('.hub-navigation-section');
      if (navSection) {
        window.scrollTo({
          top:      Math.max(0, navSection.offsetTop - 80),
          behavior: 'smooth'
        });
      }

    }, 150);
  }

  /* If load already fired (e.g. cached page), run immediately;
     otherwise wait for the load event.                          */
  if (document.readyState === 'complete') {
    activateTargetPanel();
  } else {
    window.addEventListener('load', activateTargetPanel);
  }
}

/* ─── Init ────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', function () {
  restoreTheme();
  restoreLang();       // restore saved preference first …
  resolveUrlParams();  // … URL params override language + activate panel
  initSmoothScroll();
  initBackToTop();
});
