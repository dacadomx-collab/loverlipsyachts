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
   9b. REPORT DIALOG (dashboard.php — Payments card)
   Clicking a .dash-pay-row opens #lly-report-dialog pre-filled with
   the reports its data-report-ids point to, read from the JSON payload
   in #lly-reportes-data (rendered server-side from $lly_reportes).
   ═══════════════════════════════════════════════════════════════════ */

function openReportDialog(row) {
  if (!row) return;
  var dialog = document.getElementById('lly-report-dialog');
  var dataEl = document.getElementById('lly-reportes-data');
  if (!dialog || !dataEl) return;

  var reportes;
  try { reportes = JSON.parse(dataEl.textContent); } catch (e) { return; }

  var ids = (row.dataset.reportIds || '').split(',').filter(Boolean);
  if (!ids.length) return;

  var lang = (document.body && document.body.dataset.activeLang) || 'en';
  var titleEl    = document.getElementById('lly-report-dialog-title');
  var eyebrowEl  = document.getElementById('lly-report-dialog-eyebrow');
  var bodyEl     = document.getElementById('lly-report-dialog-body');
  if (!titleEl || !eyebrowEl || !bodyEl) return;

  titleEl.textContent = lang === 'es'
    ? (row.dataset.batchLabelEs || '')
    : (row.dataset.batchLabelEn || '');
  eyebrowEl.textContent = lang === 'es' ? 'Trabajo Cubierto' : 'Work Covered';

  var html = '';
  ids.forEach(function (id) {
    var r = reportes[id];
    if (!r) return;
    var date    = lang === 'es' ? r.date_es    : r.date_en;
    var title   = lang === 'es' ? r.title_es   : r.title_en;
    var benefit = lang === 'es' ? r.benefit_es : r.benefit_en;
    html += '<p><strong>' + title + '</strong> — ' + date + '<br>' + benefit + '</p>';
  });
  bodyEl.innerHTML = html;

  try { dialog.showModal(); } catch (e) {}
}

/** Bind click + keyboard activation to all .dash-pay-row elements. */
function initReportDialog() {
  var rows = document.querySelectorAll('.dash-pay-row');
  if (!rows.length) return;
  rows.forEach(function (row) {
    row.addEventListener('click', function () { openReportDialog(row); });
    row.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openReportDialog(row);
      }
    });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   8b. CSRF TOKEN SYNC — shared across every panel below
   pg_ai_config.php ships several independent panels (fleet, prompt
   editor, templates, connection settings, module doc, AURA/OpenAI
   tests), each with its own hidden csrf field, but the server keeps
   only ONE session-wide token (see api/pgai_settings.php, api/
   aura_diagnostic.php, api/openai_diagnostic.php — every successful
   POST rotates it). If each panel only updated its own field, testing
   one panel (e.g. "Test AURA Connection") would rotate the token
   server-side while every OTHER panel's field kept the now-stale copy
   — the next save from any other panel would fail with "Invalid or
   expired CSRF token" even though nothing was wrong with that panel.
   Every fetch callback below calls this instead of touching its own
   field directly, so one rotation event stays valid for all panels
   (naming convention: every csrf field's id ends in "-csrf-field").
   ═══════════════════════════════════════════════════════════════════ */

function llySyncCsrfFields(token) {
  if (!token) return;
  document.querySelectorAll('[id$="-csrf-field"]').forEach(function (el) { el.value = token; });
}

/* ═══════════════════════════════════════════════════════════════════
   9a. LIVE LEADS PANEL (dashboard.php Card #1 — guards on
   #leads-table absence for every other page)
   ═══════════════════════════════════════════════════════════════════ */

function leadsEscape(str) {
  var div = document.createElement('div');
  div.textContent = str == null ? '' : str;
  return div.innerHTML;
}

function leadsChannelLabel(channelType) {
  var labels = { whatsapp: '🟢 WhatsApp', telegram: '✈️ Telegram', web_widget: '🌐 Web' };
  return labels[channelType] || leadsEscape(channelType);
}

function leadsRelativeWhen(isoDateTime) {
  if (!isoDateTime) return '—';
  var then = new Date(isoDateTime.replace(' ', 'T'));
  if (isNaN(then.getTime())) return leadsEscape(isoDateTime);
  var diffMs = Date.now() - then.getTime();
  var diffMin = Math.round(diffMs / 60000);
  if (diffMin < 1) return 'now';
  if (diffMin < 60) return diffMin + 'm';
  var diffHr = Math.round(diffMin / 60);
  if (diffHr < 24) return diffHr + 'h';
  return Math.round(diffHr / 24) + 'd';
}

/**
 * One row per lead/session (never raw messages) — [Client Name] |
 * [Date/Time] | [Phone/WhatsApp] | [Email] | [Status/VIP] | [Actions].
 * Deterministically-captured fields (core/PgAiActionProcessor.php) take
 * priority over the older free-text lead_contact/display_name, which
 * stay only as a fallback for sessions captured before sql/009.
 */
function leadsRenderRows(leads) {
  var tbody = document.getElementById('leads-tbody');
  if (!tbody) return;
  if (!leads.length) {
    tbody.innerHTML = '<tr><td colspan="6">'
      + '<span data-lang="en">No leads yet — new WhatsApp and website conversations will appear here.</span>'
      + '<span data-lang="es">Aún no hay leads — las conversaciones nuevas de WhatsApp y el sitio web aparecerán aquí.</span></td></tr>';
    return;
  }
  var rows = leads.map(function (l) {
    var name = l.lead_name || l.lead_contact || l.display_name || l.external_id || '—';
    var vipBadge = (l.is_vip == 1) ? ' <span class="leads-vip-badge" title="White-Glove Escalation">⭐ VIP</span>' : '';
    var statusLabel = l.status === 'open'
      ? '<span data-lang="en">Open</span><span data-lang="es">Abierto</span>'
      : '<span data-lang="en">Closed</span><span data-lang="es">Cerrado</span>';
    return '<tr>'
      + '<td>' + leadsEscape(name) + vipBadge + '</td>'
      + '<td>' + leadsRelativeWhen(l.last_activity_at) + '</td>'
      + '<td>' + (l.lead_phone ? leadsEscape(l.lead_phone) : '—') + '</td>'
      + '<td>' + (l.lead_email ? leadsEscape(l.lead_email) : '—') + '</td>'
      + '<td>' + statusLabel + '</td>'
      + '<td><button type="button" class="dash-card-btn dash-card-btn--secondary leads-detail-btn" data-session-id="' + l.id + '">'
      +   '<span data-lang="en">👁️ View Summary &amp; Chat</span><span data-lang="es">👁️ Ver Resumen y Charla</span>'
      + '</button></td>'
      + '</tr>';
  }).join('');
  tbody.innerHTML = rows;
}

/** Full unfiltered result of the last successful fetch — the search box filters this client-side instead of re-fetching per keystroke; date range still goes server-side (api/leads.php), it changes the actual row set, not just what's visible. */
var lly_leadsAllRows = [];

function leadsLoadList() {
  var tbody = document.getElementById('leads-tbody');
  if (!tbody) return;
  var csrfField  = document.getElementById('leads-csrf-field');
  var dateFromEl = document.getElementById('leads-date-from');
  var dateToEl   = document.getElementById('leads-date-to');

  var body = new URLSearchParams();
  body.set('action', 'list');
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (dateFromEl && dateFromEl.value) { body.set('date_from', dateFromEl.value); }
  if (dateToEl && dateToEl.value) { body.set('date_to', dateToEl.value); }

  fetch('api/leads.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) { return res.json(); }).then(function (data) {
    if (data.status !== 'success') return;
    llySyncCsrfFields(data.csrf_token);
    lly_leadsAllRows = data.leads || [];
    leadsApplySearchFilter();
  }).catch(function () { /* degrade silently — panel just keeps its loading row */ });
}

/** Client-side filter over the already-fetched rows — name/phone/email substring match, case-insensitive. */
function leadsApplySearchFilter() {
  var searchEl = document.getElementById('leads-search-input');
  var query = searchEl ? searchEl.value.trim().toLowerCase() : '';

  if (!query) {
    leadsRenderRows(lly_leadsAllRows);
    return;
  }

  var filtered = lly_leadsAllRows.filter(function (l) {
    var haystack = [l.lead_name, l.lead_contact, l.display_name, l.lead_phone, l.lead_email]
      .filter(Boolean).join(' ').toLowerCase();
    return haystack.indexOf(query) !== -1;
  });
  leadsRenderRows(filtered);
}

function initLeadsPanel() {
  var table = document.getElementById('leads-table');
  if (!table) return; /* only leads.php ships this panel */
  leadsLoadList();
}

function initLeadsFilters() {
  var searchEl   = document.getElementById('leads-search-input');
  var dateFromEl = document.getElementById('leads-date-from');
  var dateToEl   = document.getElementById('leads-date-to');
  var clearBtn   = document.getElementById('leads-filter-clear');
  if (!searchEl && !dateFromEl && !dateToEl) return; /* only leads.php ships these filters */

  if (searchEl) { searchEl.addEventListener('input', leadsApplySearchFilter); }
  if (dateFromEl) { dateFromEl.addEventListener('change', leadsLoadList); }
  if (dateToEl) { dateToEl.addEventListener('change', leadsLoadList); }
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      if (searchEl) { searchEl.value = ''; }
      if (dateFromEl) { dateFromEl.value = ''; }
      if (dateToEl) { dateToEl.value = ''; }
      leadsLoadList();
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════
   9a2. LEAD DETAIL MODAL — "👁️ Ver Resumen y Charla" (leads.php —
   guards on #lead-detail-dialog absence). Event-delegated on
   #leads-tbody so newly-rendered rows (after every leadsLoadList()
   refresh) never need their own listener rebound.
   ═══════════════════════════════════════════════════════════════════ */

function initLeadDetailModal() {
  var dialog = document.getElementById('lead-detail-dialog');
  var tbody  = document.getElementById('leads-tbody');
  if (!dialog || !tbody) return;

  var closeBtn     = document.getElementById('lead-detail-close');
  var eyebrowEl     = document.getElementById('lead-detail-eyebrow');
  var titleEl       = document.getElementById('lead-detail-dialog-title');
  var summaryEl     = document.getElementById('lead-detail-summary');
  var transcriptEl  = document.getElementById('lead-detail-transcript');
  var csrfField     = document.getElementById('leads-csrf-field');

  if (closeBtn) {
    closeBtn.addEventListener('click', function () { dialog.close(); });
  }

  function openLeadDetail(sessionId) {
    if (titleEl) { titleEl.textContent = 'Loading…'; }
    if (eyebrowEl) { eyebrowEl.textContent = ''; }
    if (summaryEl) { summaryEl.textContent = ''; }
    if (transcriptEl) { transcriptEl.innerHTML = ''; }
    dialog.showModal();

    var body = new URLSearchParams();
    body.set('action', 'detail');
    body.set('session_id', sessionId);
    body.set('csrf_token', csrfField ? csrfField.value : '');

    fetch('api/leads.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    }).then(function (res) { return res.json(); }).then(function (data) {
      llySyncCsrfFields(data.csrf_token);

      if (data.status !== 'success') {
        if (titleEl) { titleEl.textContent = 'Error'; }
        if (summaryEl) { summaryEl.textContent = data.message || 'Could not load this lead.'; }
        return;
      }

      var s = data.session || {};
      var name = s.lead_name || s.display_name || '—';
      if (titleEl) { titleEl.textContent = name; }
      if (eyebrowEl) {
        eyebrowEl.textContent = leadsChannelLabel(s.channel_type) + (s.is_vip == 1 ? ' · ⭐ VIP' : '');
      }
      if (summaryEl) {
        summaryEl.textContent = s.summary || 'No summary yet — not enough data captured.';
      }

      var messages = data.messages || [];
      if (transcriptEl) {
        transcriptEl.innerHTML = messages.length
          ? messages.map(function (m) {
              var who = m.direction === 'inbound' ? 'user' : 'bot';
              return '<div class="lly-ai-widget-msg lly-ai-widget-msg--' + who + '">' + leadsEscape(m.content) + '</div>';
            }).join('')
          : '<p style="padding:1rem;color:var(--ink-60)">No messages.</p>';
      }
    }).catch(function () {
      if (titleEl) { titleEl.textContent = 'Error'; }
      if (summaryEl) { summaryEl.textContent = 'Network error — check your connection.'; }
    });
  }

  tbody.addEventListener('click', function (e) {
    var btn = e.target.closest('.leads-detail-btn');
    if (!btn) return;
    var sessionId = btn.getAttribute('data-session-id');
    if (sessionId) { openLeadDetail(sessionId); }
  });

  // Deep link from agenda.php's "Open Lead Chat" button (?open_lead=ID) —
  // opens straight to that lead's modal instead of just landing on the list.
  try {
    var deepLinkId = new URLSearchParams(window.location.search).get('open_lead');
    if (deepLinkId) { openLeadDetail(deepLinkId); }
  } catch (e) {}
}

/* ═══════════════════════════════════════════════════════════════════
   9a2. PG-AI HUB — CONNECTION SETTINGS PANEL (pg_ai_hub.php only —
   guards on #pgai-settings-form absence for every other page)
   ═══════════════════════════════════════════════════════════════════ */

function pgaiSettingsPost(action, extraFields) {
  var csrfField = document.getElementById('pgai-settings-csrf-field');
  var body = new URLSearchParams();
  body.set('action', action);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (extraFields) {
    Object.keys(extraFields).forEach(function (key) {
      body.set(key, extraFields[key] == null ? '' : String(extraFields[key]));
    });
  }
  return fetch('api/pgai_settings.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) {
    return res.json().then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      return data;
    });
  });
}

function pgaiSettingsApply(settings) {
  var inputs = document.querySelectorAll('#pgai-settings-form [data-setting-key]');
  inputs.forEach(function (input) {
    var key = input.getAttribute('data-setting-key');
    var entry = settings[key];
    if (!entry) return;

    var badge = document.getElementById(input.id + '-badge');
    if (input.type === 'password') {
      /* Never prefill a secret input — only show a masked hint + status badge. */
      input.placeholder = entry.value || input.placeholder;
    } else if (entry.value) {
      input.value = entry.value;
    }
    if (badge) {
      badge.textContent = entry.is_set ? 'configured' : 'not set';
      badge.classList.toggle('pgai-settings-badge--set', entry.is_set);
    }
  });
}

function initPgaiSettingsPanel() {
  var form = document.getElementById('pgai-settings-form');
  if (!form) return; /* only pg_ai_hub.php ships this panel */

  pgaiSettingsPost('get').then(function (data) {
    if (data.status === 'success') { pgaiSettingsApply(data.settings || {}); }
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var feedback = document.getElementById('pgai-settings-feedback');
    var inputs = Array.prototype.slice.call(form.querySelectorAll('[data-setting-key]'))
      .filter(function (input) { return input.value.trim() !== ''; });

    if (!inputs.length) {
      if (feedback) { feedback.textContent = 'Nothing to save — fill in a field first.'; }
      return;
    }

    if (feedback) { feedback.textContent = 'Saving…'; }

    // Sequential, NOT Promise.all: every 'save' call rotates the session-
    // wide CSRF token server-side (api/pgai_settings.php). Saving multiple
    // fields (e.g. the OpenAI key + model + Active AI Engine selector) used
    // to fire one POST per field in parallel, all starting from the same
    // token — only the first one the server happened to process could
    // succeed; every other field came back "Invalid or expired CSRF token"
    // even though nothing was wrong with it (reproduced live, 2026-08-18).
    // Chaining guarantees each request waits for, and uses, the token the
    // previous one just rotated to.
    var lastResult = null;
    var chain = inputs.reduce(function (promise, input) {
      return promise.then(function () {
        return pgaiSettingsPost('save', { key: input.getAttribute('data-setting-key'), value: input.value.trim() });
      }).then(function (data) {
        lastResult = data;
      });
    }, Promise.resolve());

    chain.then(function () {
      if (feedback) {
        feedback.textContent = (lastResult && lastResult.status === 'success') ? 'Saved.' : ((lastResult && lastResult.message) || 'Could not save.');
      }
      if (lastResult && lastResult.status === 'success') {
        pgaiSettingsApply(lastResult.settings || {});
        inputs.forEach(function (input) { if (input.type === 'password') { input.value = ''; } });
      }
    }).catch(function () {
      if (feedback) { feedback.textContent = 'Network error — check your connection.'; }
    });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   9b. SELF-DESTRUCT LINKS PANEL (dashboard.php / pg_ai_hub.php —
   guards on #ephemeral-links-table absence for every other page)
   ═══════════════════════════════════════════════════════════════════ */

function ephemeralPost(action, extraFields) {
  var form = document.getElementById('ephemeral-create-form');
  var csrfField = document.getElementById('ephemeral-csrf-field');
  var body = new URLSearchParams();
  body.set('action', action);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (extraFields) {
    Object.keys(extraFields).forEach(function (key) {
      body.set(key, extraFields[key] == null ? '' : String(extraFields[key]));
    });
  }
  return fetch('api/ephemeral_links.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) {
    return res.json().then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      return data;
    });
  });
}

function ephemeralRenderRows(links) {
  var tbody = document.getElementById('ephemeral-links-tbody');
  if (!tbody) return;
  if (!links.length) {
    tbody.innerHTML = '<tr><td colspan="5">'
      + '<span data-lang="en">No private links yet.</span>'
      + '<span data-lang="es">Aún no hay enlaces privados.</span></td></tr>';
    return;
  }
  var statusLabels = {
    active:  '<span data-lang="en">Active</span><span data-lang="es">Activo</span>',
    expired: '<span data-lang="en">Expired</span><span data-lang="es">Expirado</span>',
    revoked: '<span data-lang="en">Revoked</span><span data-lang="es">Revocado</span>',
  };
  var rows = links.map(function (l) {
    var statusLabel = statusLabels[l.status] || l.status;
    var canEdit = l.status !== 'revoked';
    return '<tr data-link-id="' + l.id + '">'
      + '<td>' + ephemeralEscape(l.title) + '</td>'
      + '<td><a href="' + ephemeralEscape(l.public_url) + '" target="_blank" rel="noopener noreferrer" class="ephemeral-copy-link" data-url="' + ephemeralEscape(l.public_url) + '">🔗 ' + l.token.slice(0, 10) + '…</a></td>'
      + '<td>' + l.view_count + ' / <input type="number" min="' + l.view_count + '" max="50" value="' + l.max_views + '" class="ephemeral-max-views-input" ' + (canEdit ? '' : 'disabled') + '>'
      + ' <span class="ephemeral-remaining-badge">(' + Math.max(0, l.max_views - l.view_count) + ' <span data-lang="en">left</span><span data-lang="es">restantes</span>)</span></td>'
      + '<td>' + statusLabel + '</td>'
      + '<td>'
      + (canEdit ? '<button type="button" class="ephemeral-row-save" title="Save views">💾</button> <button type="button" class="ephemeral-row-revoke" title="Revoke">✕</button>' : '—')
      + '</td></tr>';
  }).join('');
  tbody.innerHTML = rows;
}

function ephemeralEscape(str) {
  var div = document.createElement('div');
  div.textContent = str == null ? '' : str;
  return div.innerHTML;
}

function ephemeralLoadList() {
  var tbody = document.getElementById('ephemeral-links-tbody');
  if (!tbody) return;
  ephemeralPost('list').then(function (data) {
    if (data.status !== 'success') return;
    ephemeralRenderRows(data.links || []);
    var defaultInput = document.getElementById('ephemeral-default-max-views');
    if (defaultInput && data.default_max_views) { defaultInput.value = data.default_max_views; }
  });
}

/**
 * Mirrors core/PgAiActionProcessor.php::buildQuotePayloadHtml() — same
 * "Clear Luxury / Pink Glove" quote-card markup, built client-side so an
 * owner quick-filling a manual ephemeral link (below) sees/edits the exact
 * HTML that a chatbot-generated quote link would have gotten automatically.
 * Kept in sync by hand (2026-08-18, core/pgai_templates.php restructure) —
 * there's no shared-across-languages template engine in this project.
 */
function buildQuoteCardHtml(tpl) {
  function esc(s) { return leadsEscape(s); }
  var inclusions = (tpl.inclusions || []).map(function (item) {
    return '<li><span class="quote-card-inclusion-icon">' + esc(item.icon) + '</span>'
      + '<span data-lang="en">' + esc(item.en) + '</span><span data-lang="es">' + esc(item.es) + '</span></li>';
  }).join('');
  var policies = (tpl.policies || []).map(function (item) {
    return '<li><span data-lang="en">' + esc(item.en) + '</span><span data-lang="es">' + esc(item.es) + '</span></li>';
  }).join('');
  var rate = Number(tpl.rate_mxn || 0).toLocaleString('en-US');

  return '<div class="quote-card">'
    + '<p class="quote-card-eyebrow">🛥️ <span data-lang="en">Experience</span><span data-lang="es">Experiencia</span></p>'
    + '<h3><span data-lang="en">' + esc(tpl.title.en) + '</span><span data-lang="es">' + esc(tpl.title.es) + '</span></h3>'
    + '<p class="quote-card-description"><span data-lang="en">' + esc(tpl.description.en) + '</span><span data-lang="es">' + esc(tpl.description.es) + '</span></p>'
    + '</div>'
    + '<div class="quote-card quote-card--rate">'
    + '<p class="quote-card-eyebrow">💎 <span data-lang="en">Official Rate</span><span data-lang="es">Tarifa Oficial</span></p>'
    + '<p class="quote-card-rate">$' + rate + ' <span class="quote-card-rate-currency">MXN</span></p>'
    + '</div>'
    + '<div class="quote-card">'
    + '<p class="quote-card-eyebrow">✨ <span data-lang="en">VIP Inclusions</span><span data-lang="es">Inclusiones VIP</span></p>'
    + '<ul class="quote-card-list">' + inclusions + '</ul>'
    + '</div>'
    + '<div class="quote-card">'
    + '<p class="quote-card-eyebrow">📋 <span data-lang="en">Booking Policies</span><span data-lang="es">Políticas de Reserva</span></p>'
    + '<ul class="quote-card-list quote-card-list--policies">' + policies + '</ul>'
    + '</div>';
}

/** Reads pg_ai_hub.php's #lly-pgai-quote-templates-data JSON — PG-AI "PINK LIPS Experience" quick-fill templates. */
function initEphemeralQuoteTemplates() {
  var select = document.getElementById('ephemeral-quote-template');
  var dataEl = document.getElementById('lly-pgai-quote-templates-data');
  if (!select || !dataEl) return;

  var templates = {};
  try { templates = JSON.parse(dataEl.textContent || '{}'); } catch (e) { templates = {}; }

  select.addEventListener('change', function () {
    var tpl = templates[select.value];
    if (!tpl) return;
    var titleField = document.getElementById('ephemeral-title');
    var payloadField = document.getElementById('ephemeral-payload');
    var typeField = document.getElementById('ephemeral-resource-type');
    if (titleField && !titleField.value) { titleField.value = tpl.title_internal; }
    if (payloadField) { payloadField.value = buildQuoteCardHtml(tpl); }
    if (typeField) { typeField.value = 'quote'; }
  });
}

function initEphemeralLinksPanel() {
  var table = document.getElementById('ephemeral-links-table');
  if (!table) return; /* only dashboard.php ships this panel */

  initEphemeralQuoteTemplates();
  ephemeralLoadList();

  var saveDefaultBtn = document.getElementById('ephemeral-save-default-btn');
  if (saveDefaultBtn) {
    saveDefaultBtn.addEventListener('click', function () {
      var input = document.getElementById('ephemeral-default-max-views');
      ephemeralPost('set_default_max_views', { max_views: input.value }).then(function () {
        ephemeralLoadList();
      });
    });
  }

  var form = document.getElementById('ephemeral-create-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var feedback = document.getElementById('ephemeral-create-feedback');
      ephemeralPost('create', {
        title: document.getElementById('ephemeral-title').value,
        resource_type: document.getElementById('ephemeral-resource-type').value,
        payload_html: document.getElementById('ephemeral-payload').value,
        target_url: document.getElementById('ephemeral-target-url').value,
        max_views: document.getElementById('ephemeral-max-views').value,
      }).then(function (data) {
        if (!feedback) return;
        if (data.status === 'success') {
          feedback.textContent = 'Link ready: ' + data.link.public_url;
          form.reset();
          ephemeralLoadList();
        } else {
          feedback.textContent = data.message || 'Could not create the link.';
        }
      });
    });
  }

  table.addEventListener('click', function (e) {
    var row = e.target.closest('tr[data-link-id]');
    if (!row) return;
    var id = row.getAttribute('data-link-id');

    if (e.target.classList.contains('ephemeral-copy-link')) {
      e.preventDefault();
      var url = e.target.getAttribute('data-url');
      if (navigator.clipboard) { navigator.clipboard.writeText(url); }
    }

    if (e.target.classList.contains('ephemeral-row-save')) {
      var input = row.querySelector('.ephemeral-max-views-input');
      ephemeralPost('update_max_views', { id: id, max_views: input.value }).then(function () {
        ephemeralLoadList();
      });
    }

    if (e.target.classList.contains('ephemeral-row-revoke')) {
      if (!window.confirm('Revoke this link? It will stop working immediately.')) return;
      ephemeralPost('revoke', { id: id }).then(function () {
        ephemeralLoadList();
      });
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════
   9c. FLEET CATALOG EDITOR (pg_ai_hub.php Section E — guards on
   #fleet-catalog-table absence for every other page)
   ═══════════════════════════════════════════════════════════════════ */

function fleetPost(action, extraFields) {
  var csrfField = document.getElementById('fleet-csrf-field');
  var body = new URLSearchParams();
  body.set('action', action);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (extraFields) {
    Object.keys(extraFields).forEach(function (key) {
      body.set(key, extraFields[key] == null ? '' : String(extraFields[key]));
    });
  }
  return fetch('api/fleet_catalog.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) {
    return res.json().then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      return data;
    });
  });
}

function fleetEscape(str) {
  var div = document.createElement('div');
  div.textContent = str == null ? '' : str;
  return div.innerHTML;
}

function fleetRenderRows(vessels) {
  var tbody = document.getElementById('fleet-catalog-tbody');
  if (!tbody) return;
  if (!vessels.length) {
    tbody.innerHTML = '<tr><td colspan="5">'
      + '<span data-lang="en">No vessels yet — add the first one above.</span>'
      + '<span data-lang="es">Aún no hay embarcaciones — agrega la primera arriba.</span></td></tr>';
    return;
  }
  var rows = vessels.map(function (v) {
    var statusLabel = v.verification_status === 'verified'
      ? '<span class="pill pill-green"><span data-lang="en">Verified</span><span data-lang="es">Verificado</span></span>'
      : '<span class="pill pill-orange"><span data-lang="en">Pending</span><span data-lang="es">Pendiente</span></span>';
    return '<tr data-vessel-id="' + v.id + '">'
      + '<td><strong>' + fleetEscape(v.vessel_name) + '</strong>' + (v.vessel_slug ? '<br><small>' + fleetEscape(v.vessel_slug) + '</small>' : '') + '</td>'
      + '<td>' + (v.max_pax != null ? v.max_pax : '—') + '</td>'
      + '<td>' + (v.length_ft != null ? v.length_ft + ' ft' : '—') + '</td>'
      + '<td>' + statusLabel + '</td>'
      + '<td><button type="button" class="fleet-row-edit" title="Edit">✏️</button> <button type="button" class="fleet-row-delete" title="Delete">✕</button></td>'
      + '</tr>';
  }).join('');
  tbody.innerHTML = rows;
}

function fleetLoadList() {
  var tbody = document.getElementById('fleet-catalog-tbody');
  if (!tbody) return;
  fleetPost('list').then(function (data) {
    if (data.status !== 'success') return;
    fleetRenderRows(data.vessels || []);
    window.__fleetVesselsCache = data.vessels || [];
  });
}

function fleetResetForm() {
  var form = document.getElementById('fleet-form');
  if (!form) return;
  form.reset();
  document.getElementById('fleet-id').value = '';
  document.getElementById('fleet-submit-btn').querySelector('[data-lang="en"]').textContent = '💾 Save Vessel';
  document.getElementById('fleet-submit-btn').querySelector('[data-lang="es"]').textContent = '💾 Guardar Embarcación';
  var cancelBtn = document.getElementById('fleet-cancel-edit-btn');
  if (cancelBtn) { cancelBtn.hidden = true; }
}

function fleetPopulateFormForEdit(vessel) {
  document.getElementById('fleet-id').value = vessel.id;
  document.getElementById('fleet-vessel-name').value = vessel.vessel_name || '';
  document.getElementById('fleet-vessel-slug').value = vessel.vessel_slug || '';
  document.getElementById('fleet-max-pax').value = vessel.max_pax != null ? vessel.max_pax : '';
  document.getElementById('fleet-length-ft').value = vessel.length_ft != null ? vessel.length_ft : '';
  document.getElementById('fleet-role-en').value = vessel.role_label_en || '';
  document.getElementById('fleet-role-es').value = vessel.role_label_es || '';
  document.getElementById('fleet-status-pill').value = vessel.status_pill || 'pill-orange';
  document.getElementById('fleet-verification-status').value = vessel.verification_status || 'pending';
  document.getElementById('fleet-submit-btn').querySelector('[data-lang="en"]').textContent = '💾 Update Vessel';
  document.getElementById('fleet-submit-btn').querySelector('[data-lang="es"]').textContent = '💾 Actualizar Embarcación';
  document.getElementById('fleet-cancel-edit-btn').hidden = false;
  document.getElementById('fleet-vessel-name').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function initFleetCatalogPanel() {
  var table = document.getElementById('fleet-catalog-table');
  if (!table) return; /* only pg_ai_hub.php ships this panel */

  fleetLoadList();

  var form = document.getElementById('fleet-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var feedback = document.getElementById('fleet-feedback');
      var id = document.getElementById('fleet-id').value;
      var fields = {
        vessel_name: document.getElementById('fleet-vessel-name').value,
        vessel_slug: document.getElementById('fleet-vessel-slug').value,
        max_pax: document.getElementById('fleet-max-pax').value,
        length_ft: document.getElementById('fleet-length-ft').value,
        role_label_en: document.getElementById('fleet-role-en').value,
        role_label_es: document.getElementById('fleet-role-es').value,
        status_pill: document.getElementById('fleet-status-pill').value,
        verification_status: document.getElementById('fleet-verification-status').value,
      };
      var action = id ? 'update' : 'create';
      if (id) { fields.id = id; }
      fleetPost(action, fields).then(function (data) {
        if (!feedback) return;
        if (data.status === 'success') {
          feedback.textContent = '';
          fleetResetForm();
          fleetLoadList();
        } else {
          feedback.textContent = data.message || 'Could not save the vessel.';
        }
      });
    });
  }

  var cancelBtn = document.getElementById('fleet-cancel-edit-btn');
  if (cancelBtn) {
    cancelBtn.addEventListener('click', fleetResetForm);
  }

  table.addEventListener('click', function (e) {
    var row = e.target.closest('tr[data-vessel-id]');
    if (!row) return;
    var id = row.getAttribute('data-vessel-id');

    if (e.target.classList.contains('fleet-row-edit')) {
      var vessel = (window.__fleetVesselsCache || []).find(function (v) { return String(v.id) === String(id); });
      if (vessel) { fleetPopulateFormForEdit(vessel); }
    }

    if (e.target.classList.contains('fleet-row-delete')) {
      if (!window.confirm('Delete this vessel? This cannot be undone.')) return;
      fleetPost('delete', { id: id }).then(function () {
        fleetLoadList();
      });
    }
  });
}

/* ═══════════════════════════════════════════════════════════════════
   9d. PG-AI CONFIG — MASTER PROMPT EDITOR (pg_ai_config.php — guards on
   #prompt-editor-textarea absence for every other page)
   ═══════════════════════════════════════════════════════════════════ */

function promptEditorPost(action, extraFields) {
  var csrfField = document.getElementById('prompt-editor-csrf-field');
  var body = new URLSearchParams();
  body.set('action', action);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (extraFields) {
    Object.keys(extraFields).forEach(function (key) {
      body.set(key, extraFields[key] == null ? '' : String(extraFields[key]));
    });
  }
  return fetch('api/prompt_editor.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) {
    return res.json().then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      return data;
    });
  });
}

function initPromptEditorPanel() {
  var textarea = document.getElementById('prompt-editor-textarea');
  if (!textarea) return; /* only pg_ai_config.php ships this panel */

  var feedback = document.getElementById('prompt-editor-feedback');
  var saveBtn  = document.getElementById('prompt-editor-save-btn');

  promptEditorPost('get').then(function (data) {
    if (data.status === 'success') { textarea.value = data.content || ''; }
    else if (feedback) { feedback.textContent = data.message || 'Could not load the prompt file.'; }
  });

  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      saveBtn.disabled = true;
      promptEditorPost('save', { content: textarea.value }).then(function (data) {
        if (feedback) {
          feedback.textContent = data.status === 'success'
            ? 'Saved — the live chatbot will use this on its next message.'
            : (data.message || 'Could not save.');
        }
      }).then(function () { saveBtn.disabled = false; });
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════
   9e. PG-AI CONFIG — LEAD NOTIFICATION TEMPLATES (pg_ai_config.php —
   guards on #templates-list absence for every other page)
   ═══════════════════════════════════════════════════════════════════ */

function templatesPost(action, extraFields) {
  var csrfField = document.getElementById('templates-csrf-field');
  var body = new URLSearchParams();
  body.set('action', action);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (extraFields) {
    Object.keys(extraFields).forEach(function (key) {
      body.set(key, extraFields[key] == null ? '' : String(extraFields[key]));
    });
  }
  return fetch('api/notification_templates.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) {
    return res.json().then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      return data;
    });
  });
}

function templatesEscape(str) {
  var div = document.createElement('div');
  div.textContent = str == null ? '' : str;
  return div.innerHTML;
}

function templatesRender(container, templates) {
  container.innerHTML = templates.map(function (t) {
    var subjectRow = (t.channel === 'email')
      ? '<div class="ephemeral-form-row ephemeral-form-row--inline">'
        + '<label>Subject (EN)</label><input type="text" class="tpl-subject-en" value="' + templatesEscape(t.subject_en) + '">'
        + '<label>Subject (ES)</label><input type="text" class="tpl-subject-es" value="' + templatesEscape(t.subject_es) + '">'
        + '</div>'
      : '';
    return '<div class="ephemeral-form" data-template-id="' + t.id + '">'
      + '<h4>' + templatesEscape(t.template_key) + ' — ' + templatesEscape(t.channel) + '</h4>'
      + subjectRow
      + '<div class="ephemeral-form-row"><label>Body (EN)</label><textarea class="editor-textarea tpl-body-en" rows="4">' + templatesEscape(t.body_en) + '</textarea></div>'
      + '<div class="ephemeral-form-row"><label>Body (ES)</label><textarea class="editor-textarea tpl-body-es" rows="4">' + templatesEscape(t.body_es) + '</textarea></div>'
      + '<button type="button" class="dash-card-btn dash-card-btn--secondary tpl-save-btn">💾 Save</button>'
      + '<span class="ephemeral-feedback tpl-feedback" role="status" aria-live="polite"></span>'
      + '</div>';
  }).join('');
}

function initNotificationTemplatesPanel() {
  var container = document.getElementById('templates-list');
  if (!container) return; /* only pg_ai_config.php ships this panel */

  templatesPost('list').then(function (data) {
    if (data.status !== 'success') {
      container.innerHTML = '<p>' + (data.message || 'Could not load templates.') + '</p>';
      return;
    }
    templatesRender(container, data.templates || []);
  });

  container.addEventListener('click', function (e) {
    if (!e.target.classList.contains('tpl-save-btn')) return;
    var row = e.target.closest('[data-template-id]');
    if (!row) return;
    var id = row.getAttribute('data-template-id');
    var fields = {
      body_en: row.querySelector('.tpl-body-en').value,
      body_es: row.querySelector('.tpl-body-es').value,
    };
    var subjectEnEl = row.querySelector('.tpl-subject-en');
    var subjectEsEl = row.querySelector('.tpl-subject-es');
    if (subjectEnEl) { fields.subject_en = subjectEnEl.value; }
    if (subjectEsEl) { fields.subject_es = subjectEsEl.value; }

    e.target.disabled = true;
    templatesPost('update', Object.assign({ id: id }, fields)).then(function (data) {
      var feedback = row.querySelector('.tpl-feedback');
      if (feedback) { feedback.textContent = data.status === 'success' ? 'Saved.' : (data.message || 'Could not save.'); }
      e.target.disabled = false;
    });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   9f. PG-AI CONFIG — KNOWLEDGE MODULE EDITOR (pg_ai_config.php,
   super_admin only — guards on #moduledoc-editor-textarea absence)
   ═══════════════════════════════════════════════════════════════════ */

function moduleDocPost(action, extraFields) {
  var csrfField = document.getElementById('moduledoc-editor-csrf-field');
  var body = new URLSearchParams();
  body.set('action', action);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (extraFields) {
    Object.keys(extraFields).forEach(function (key) {
      body.set(key, extraFields[key] == null ? '' : String(extraFields[key]));
    });
  }
  return fetch('api/module_doc_editor.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) {
    return res.json().then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      return data;
    });
  });
}

function initModuleDocEditorPanel() {
  var textarea = document.getElementById('moduledoc-editor-textarea');
  if (!textarea) return; /* only pg_ai_config.php Section 6 (super_admin) ships this panel */

  var feedback = document.getElementById('moduledoc-editor-feedback');
  var saveBtn  = document.getElementById('moduledoc-editor-save-btn');

  moduleDocPost('get').then(function (data) {
    if (data.status === 'success') { textarea.value = data.content || ''; }
    else if (feedback) { feedback.textContent = data.message || 'Could not load the module doc.'; }
  });

  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      saveBtn.disabled = true;
      moduleDocPost('save', { content: textarea.value }).then(function (data) {
        if (feedback) {
          feedback.textContent = data.status === 'success' ? 'Saved.' : (data.message || 'Could not save.');
        }
      }).then(function () { saveBtn.disabled = false; });
    });
  }
}

/* ═══════════════════════════════════════════════════════════════════
   9g. PG-AI CONFIG — M2M HANDSHAKE TEST BUTTON (pg_ai_config.php,
   super_admin only — guards on #handshake-test-btn absence)
   ═══════════════════════════════════════════════════════════════════ */

function initHandshakeTestPanel() {
  var btn = document.getElementById('handshake-test-btn');
  if (!btn) return; /* only pg_ai_config.php Section 4 AURA fieldset (super_admin) ships this panel */

  var result    = document.getElementById('handshake-result');
  var telemetry = document.getElementById('handshake-telemetry');
  var csrfField = document.getElementById('pgai-settings-csrf-field'); /* shared with the enclosing #pgai-settings-form */

  btn.addEventListener('click', function () {
    btn.disabled = true;
    if (result) { result.textContent = 'Testing…'; }
    if (telemetry) { telemetry.hidden = true; }

    var body = new URLSearchParams();
    body.set('action', 'handshake');
    body.set('csrf_token', csrfField ? csrfField.value : '');

    fetch('api/aura_diagnostic.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    }).then(function (res) { return res.json(); }).then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      if (!result) return;
      if (data.status === 'success' && data.result) {
        var r = data.result;
        var statusEl  = document.getElementById('handshake-status');
        var latencyEl = document.getElementById('handshake-latency');
        var tenantEl  = document.getElementById('handshake-tenant');
        var engineEl  = document.getElementById('handshake-engine');
        if (statusEl)  { statusEl.textContent  = r.success ? ('✅ Connected (HTTP ' + r.httpCode + ')') : ('❌ Error (HTTP ' + (r.httpCode || 'ERR') + ')'); }
        if (latencyEl) { latencyEl.textContent = (r.reportedLatencyMs || r.networkLatencyMs) ? (r.reportedLatencyMs || r.networkLatencyMs) + ' ms' : '—'; }
        if (tenantEl)  { tenantEl.textContent  = r.tenantName || '—'; }
        if (engineEl)  { engineEl.textContent  = (r.engine || r.model) ? ((r.engine || '—') + ' / ' + (r.model || '—')) : '—'; }
        if (telemetry) { telemetry.hidden = false; }
        result.textContent = r.response || r.errorMessage || '';
      } else {
        result.textContent = data.message || 'Handshake failed.';
      }
    }).catch(function () {
      if (result) { result.textContent = 'Network error — check your connection.'; }
    }).then(function () { btn.disabled = false; });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   9h. PG-AI CONFIG — OPENAI TEST CONNECTION BUTTON (pg_ai_config.php,
   super_admin only — guards on #openai-test-btn absence)
   ═══════════════════════════════════════════════════════════════════ */

function initOpenAiTestPanel() {
  var btn = document.getElementById('openai-test-btn');
  if (!btn) return; /* only pg_ai_config.php Section 4 (super_admin) ships this panel */

  var telemetry = document.getElementById('openai-test-telemetry');
  var response  = document.getElementById('openai-test-response');
  var csrfField = document.getElementById('pgai-settings-csrf-field'); /* shared with the enclosing #pgai-settings-form */
  var keyInput  = document.getElementById('pgai-openai-key');
  var modelSel  = document.getElementById('pgai-openai-model');

  btn.addEventListener('click', function () {
    btn.disabled = true;
    if (response) { response.textContent = 'Testing…'; }
    if (telemetry) { telemetry.hidden = true; }

    var body = new URLSearchParams();
    body.set('action', 'test');
    body.set('csrf_token', csrfField ? csrfField.value : '');
    body.set('api_key', keyInput ? keyInput.value.trim() : '');
    body.set('model', modelSel ? modelSel.value : '');

    fetch('api/openai_diagnostic.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    }).then(function (res) { return res.json(); }).then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      if (!response) return;
      if (data.status === 'success' && data.result) {
        var r = data.result;
        var statusEl  = document.getElementById('openai-test-status');
        var latencyEl = document.getElementById('openai-test-latency');
        var modelEl   = document.getElementById('openai-test-model');
        if (statusEl)  { statusEl.textContent  = r.success ? ('✅ Connected (HTTP ' + r.httpCode + ')') : ('❌ ' + (r.httpCode === 401 || r.httpCode === 403 ? 'Auth error' : 'Error') + ' (HTTP ' + (r.httpCode || 'ERR') + ')'); }
        if (latencyEl) { latencyEl.textContent = r.latencyMs != null ? r.latencyMs + ' ms' : '—'; }
        if (modelEl)   { modelEl.textContent   = r.model || '—'; }
        if (telemetry) { telemetry.hidden = false; }
        response.textContent = r.response || r.errorMessage || '';
      } else {
        response.textContent = data.message || 'Test failed.';
      }
    }).catch(function () {
      if (response) { response.textContent = 'Network error — check your connection.'; }
    }).then(function () { btn.disabled = false; });
  });
}

/* ═══════════════════════════════════════════════════════════════════
   9i. CATAMARAN INVENTORY CHECKLIST (checklist.php — guards on
   #checklist-tabs/#checklist-panels absence for every other page).
   checklist.php's own inline script builds the DOM (item catalog is
   page-specific content, same idea as dashboard.php's inline
   $lly_reportes/$lly_pagos arrays) and exposes
   window.LLY_CHECKLIST_FIELD_LABELS / window.LLY_CHECKLIST_TAB_ORDER —
   everything below is pure behavior, reusing api/inventory_checklists.php.
   ═══════════════════════════════════════════════════════════════════ */

function checklistEscape(str) {
  var div = document.createElement('div');
  div.textContent = str == null ? '' : str;
  return div.innerHTML;
}

function checklistShowToast(message, isError) {
  var toast = document.getElementById('checklist-toast');
  if (!toast) return;
  toast.textContent = message;
  toast.classList.toggle('lly-toast--error', !!isError);
  toast.classList.toggle('lly-toast--success', !isError);
  toast.classList.add('lly-toast--visible');
  clearTimeout(checklistShowToast._t);
  checklistShowToast._t = setTimeout(function () { toast.classList.remove('lly-toast--visible'); }, 4000);
}

function checklistShowValidationAlert(message) {
  var alertBox = document.getElementById('validation-alert');
  if (!alertBox) return;
  alertBox.textContent = message;
  alertBox.classList.add('show');
  clearTimeout(checklistShowValidationAlert._t);
  checklistShowValidationAlert._t = setTimeout(function () { alertBox.classList.remove('show'); }, 5000);
}

/* ── View switch — "📝 Checklist" / "📜 Historial" (only one visible) ── */
function initChecklistViewSwitch() {
  var btnChecklist = document.getElementById('view-btn-checklist');
  var btnHistory   = document.getElementById('view-btn-history');
  var viewChecklist = document.getElementById('view-checklist');
  var viewHistory   = document.getElementById('view-history');
  if (!btnChecklist || !btnHistory || !viewChecklist || !viewHistory) return;

  function showView(view) {
    var isHistory = view === 'history';
    viewChecklist.classList.toggle('hidden-view', isHistory);
    viewHistory.classList.toggle('active', isHistory);
    btnChecklist.classList.toggle('active', !isHistory);
    btnChecklist.setAttribute('aria-pressed', String(!isHistory));
    btnHistory.classList.toggle('active', isHistory);
    btnHistory.setAttribute('aria-pressed', String(isHistory));
    if (isHistory) { checklistLoadHistory(); }
  }

  btnChecklist.addEventListener('click', function () { showView('checklist'); });
  btnHistory.addEventListener('click', function () { showView('history'); });
}

/* ── Tab nav — only the active section panel is ever visible ── */
function initChecklistTabs() {
  var tabsNav    = document.getElementById('checklist-tabs');
  var panelsWrap = document.getElementById('checklist-panels');
  if (!tabsNav || !panelsWrap) return;

  function showTab(id) {
    panelsWrap.querySelectorAll('.tab-panel').forEach(function (p) {
      p.hidden = (p.id !== 'panel-' + id);
    });
    tabsNav.querySelectorAll('.checklist-tab').forEach(function (t) {
      t.classList.toggle('active', t.dataset.tabTarget === id);
    });
    var activeTab = tabsNav.querySelector('.checklist-tab[data-tab-target="' + id + '"]');
    if (activeTab) { activeTab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' }); }
    var panel = document.getElementById('panel-' + id);
    if (panel) { panel.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  }

  tabsNav.addEventListener('click', function (e) {
    var btn = e.target.closest('.checklist-tab');
    if (btn) { showTab(btn.dataset.tabTarget); }
  });

  panelsWrap.addEventListener('click', function (e) {
    var panel = e.target.closest('.tab-panel');
    if (!panel) return;
    var order = window.LLY_CHECKLIST_TAB_ORDER || [];
    var idx = order.indexOf(panel.dataset.sectionId);
    if (e.target.closest('.checklist-prev') && idx > 0) { showTab(order[idx - 1]); }
    if (e.target.closest('.checklist-next') && idx > -1 && idx < order.length - 1) { showTab(order[idx + 1]); }
  });
}

/** Tallies every [data-field-id] row's status into the sticky summary pills + per-tab attention badges. */
function checklistRecalcSummary() {
  var totals = { good: 0, damaged: 0, missing: 0, replace: 0 };
  var perSection = {};

  document.querySelectorAll('#checklist-panels [data-field-id]').forEach(function (el) {
    var s = el.dataset.status;
    if (s && totals[s] !== undefined) { totals[s]++; }
    if (s && s !== 'good') {
      var panel = el.closest('.tab-panel');
      var sid = panel ? panel.dataset.sectionId : null;
      if (sid) { perSection[sid] = (perSection[sid] || 0) + 1; }
    }
  });

  var pillsEl = document.getElementById('checklist-summary-pills');
  if (pillsEl) {
    var flagged = totals.damaged + totals.missing + totals.replace;
    pillsEl.innerHTML =
      '<span class="summary-pill total">📋 ' + flagged + '</span>' +
      '<span class="summary-pill good">✅ ' + totals.good + '</span>' +
      '<span class="summary-pill damaged">⚠️ ' + totals.damaged + '</span>' +
      '<span class="summary-pill missing">❌ ' + totals.missing + '</span>' +
      '<span class="summary-pill replace">🔄 ' + totals.replace + '</span>';
  }

  document.querySelectorAll('.checklist-tab').forEach(function (tab) {
    var count = perSection[tab.dataset.tabTarget] || 0;
    var badge = tab.querySelector('.badge');
    if (!badge) return;
    if (count > 0) { badge.hidden = false; badge.textContent = count; } else { badge.hidden = true; }
  });
}

/**
 * Applies the current language to every checklist input's placeholder.
 * setLang() (main.js §1) only knows about [data-lang] spans/labels — it has
 * no idea these data-ph-en/data-ph-es attributes exist, so this page-local
 * sync is called once at init and again from initChecklistLangSync() on
 * every #btn-en/#btn-es click (registered after initLangToggle in
 * llyInitAll(), so the site's own toggle has already flipped
 * body.dataset.activeLang by the time this reads it).
 */
function checklistSyncPlaceholders() {
  var lang = document.body.dataset.activeLang === 'es' ? 'es' : 'en';
  document.querySelectorAll('#view-checklist [data-ph-en], #view-history [data-placeholder-en]').forEach(function (el) {
    var enText = el.dataset.phEn || el.dataset.placeholderEn;
    var esText = el.dataset.phEs || el.dataset.placeholderEs;
    el.placeholder = lang === 'es' ? esText : enText;
  });
}

function initChecklistLangSync() {
  var panelsWrap = document.getElementById('checklist-panels');
  if (!panelsWrap) return;
  var btnEn = document.getElementById('btn-en');
  var btnEs = document.getElementById('btn-es');
  if (btnEn) { btnEn.addEventListener('click', checklistSyncPlaceholders); }
  if (btnEs) { btnEs.addEventListener('click', checklistSyncPlaceholders); }
}

/* ── Status buttons, Before/After toggle, guests-vs-life-vests check ── */
function initChecklistForm() {
  var panelsWrap = document.getElementById('checklist-panels');
  if (!panelsWrap) return;

  panelsWrap.addEventListener('click', function (e) {
    var btn = e.target.closest('.status-btn');
    if (!btn) return;
    var container = btn.closest('[data-field-id]');
    var group = btn.closest('.status-group');
    if (!container || !group) return;
    var wasActive = btn.classList.contains('is-active');
    group.querySelectorAll('.status-btn').forEach(function (b) { b.classList.remove('is-active'); });
    if (wasActive) {
      container.dataset.status = '';
    } else {
      btn.classList.add('is-active');
      container.dataset.status = btn.dataset.value;
    }
    checklistRecalcSummary();
  });

  var modeBefore = document.getElementById('mode-before');
  var modeAfter  = document.getElementById('mode-after');
  if (modeBefore && modeAfter) {
    [modeBefore, modeAfter].forEach(function (b) {
      b.addEventListener('click', function () {
        modeBefore.classList.toggle('active', b === modeBefore);
        modeBefore.setAttribute('aria-pressed', String(b === modeBefore));
        modeAfter.classList.toggle('active', b === modeAfter);
        modeAfter.setAttribute('aria-pressed', String(b === modeAfter));
      });
    });
  }

  var guestsInput = document.getElementById('op-guests');
  function checkLifeVests() {
    var vestRow = document.getElementById('safety-adult-vests');
    if (!vestRow || !guestsInput) return;
    var countInput = vestRow.querySelector('.item-count');
    var guests = parseInt(guestsInput.value, 10) || 0;
    var vests  = parseInt(countInput.value, 10);
    var existingWarn = vestRow.querySelector('.compliance-inline');
    if (existingWarn) { existingWarn.remove(); }
    if (guests > 0 && !isNaN(vests) && vests < guests) {
      var warn = document.createElement('div');
      warn.className = 'callout warn compliance-inline';
      warn.style.margin = '.5rem 0 0';
      var shortfall = guests - vests;
      warn.innerHTML = '⚠️ ' +
        '<span data-lang="en">Only ' + vests + ' vests for ' + guests + ' guests — shortfall of ' + shortfall + '.</span>' +
        '<span data-lang="es">Solo ' + vests + ' chalecos para ' + guests + ' huéspedes — faltan ' + shortfall + '.</span>';
      vestRow.querySelector('.item-controls').appendChild(warn);
    }
  }
  if (guestsInput) { guestsInput.addEventListener('input', checkLifeVests); }
  panelsWrap.addEventListener('input', function (e) {
    if (e.target.closest('#safety-adult-vests .item-count')) { checkLifeVests(); }
  });

  checklistResetForm();
}

/**
 * Blanks every item row + header/sign-off field back to defaults (today's
 * date/time, mode = Before, vessel = NOMADA) and clears edit mode. Called
 * on initial page load, after a successful Save/Update, and from "Cancel /
 * New Checklist" in the editing banner.
 */
function checklistResetForm() {
  document.querySelectorAll('#checklist-panels [data-field-id]').forEach(function (el) {
    el.dataset.status = '';
    el.querySelectorAll('.status-btn').forEach(function (b) { b.classList.remove('is-active'); });
    var c = el.querySelector('.item-count');  if (c) { c.value = ''; }
    var n = el.querySelector('.item-notes');  if (n) { n.value = ''; }
    var x = el.querySelector('.item-expiry'); if (x) { x.value = ''; }
    var warn = el.querySelector('.compliance-inline'); if (warn) { warn.remove(); }
  });

  var setVal = function (id, v) { var el = document.getElementById(id); if (el) { el.value = v; } };
  setVal('op-vessel', 'NOMADA');
  setVal('op-guests', '');
  setVal('op-captain', '');
  setVal('op-checkedby', '');
  setVal('final-missing-report', '');
  setVal('final-actions', '');
  setVal('captain-signature', '');

  var sigInput = document.getElementById('captain-signature');
  var sigLabel = document.getElementById('sig-label');
  if (sigInput) { sigInput.classList.remove('invalid'); }
  if (sigLabel) { sigLabel.classList.remove('invalid'); }

  var modeBefore = document.getElementById('mode-before');
  var modeAfter  = document.getElementById('mode-after');
  if (modeBefore && modeAfter) {
    modeBefore.classList.add('active'); modeBefore.setAttribute('aria-pressed', 'true');
    modeAfter.classList.remove('active'); modeAfter.setAttribute('aria-pressed', 'false');
  }

  var now = new Date();
  var pad = function (n) { return String(n).padStart(2, '0'); };
  var todayStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
  setVal('sig-date', todayStr);
  setVal('sig-time', pad(now.getHours()) + ':' + pad(now.getMinutes()));
  setVal('op-date', todayStr);

  lly_checklistEditingId = null;
  checklistUpdateSaveButtonLabel();
  checklistSyncPlaceholders();
  checklistRecalcSummary();
}

/** Loads one saved record back into the live form for correction — checklist.php's "Edit" flow. Switches Save into update-mode (see checklistUpdateSaveButtonLabel). */
function checklistPopulateForm(record) {
  checklistResetForm();

  var setVal = function (id, v) { var el = document.getElementById(id); if (el) { el.value = v == null ? '' : v; } };
  setVal('op-vessel', record.vessel_name);
  setVal('op-date', record.charter_date || '');
  setVal('op-guests', record.guests_count != null ? record.guests_count : '');
  setVal('op-captain', record.captain_name || '');
  setVal('op-checkedby', record.checked_by || '');
  setVal('final-missing-report', record.missing_report || '');
  setVal('final-actions', record.required_actions || '');
  setVal('captain-signature', record.captain_signature || '');
  if (record.signed_at) {
    var parts = String(record.signed_at).split(' ');
    setVal('sig-date', parts[0] || '');
    setVal('sig-time', (parts[1] || '').substring(0, 5));
  }

  var modeBefore = document.getElementById('mode-before');
  var modeAfter  = document.getElementById('mode-after');
  if (modeBefore && modeAfter) {
    var isAfter = record.inspection_mode === 'after';
    modeBefore.classList.toggle('active', !isAfter); modeBefore.setAttribute('aria-pressed', String(!isAfter));
    modeAfter.classList.toggle('active', isAfter);   modeAfter.setAttribute('aria-pressed', String(isAfter));
  }

  var payload = record.payload || {};
  Object.keys(payload).forEach(function (fieldId) {
    if (!/^[a-zA-Z0-9_-]+$/.test(fieldId)) { return; } // ids are our own scheme, but never trust DB content in a selector
    var el = document.querySelector('#checklist-panels [data-field-id="' + fieldId + '"]');
    if (!el) { return; }
    var entry = payload[fieldId] || {};
    el.dataset.status = entry.status || '';
    if (entry.status) {
      var btn = el.querySelector('.status-btn[data-value="' + entry.status + '"]');
      if (btn) { btn.classList.add('is-active'); }
    }
    var c = el.querySelector('.item-count');  if (c && entry.count)  { c.value = entry.count; }
    var n = el.querySelector('.item-notes');  if (n && entry.notes)  { n.value = entry.notes; }
    var x = el.querySelector('.item-expiry'); if (x && entry.expiry) { x.value = entry.expiry; }
  });

  lly_checklistEditingId = record.id;
  checklistUpdateSaveButtonLabel();
  checklistSyncPlaceholders();
  checklistRecalcSummary();
}

/** Currently-open bitácora entry id while editing, or null for a fresh checklist. */
var lly_checklistEditingId = null;

function checklistUpdateSaveButtonLabel() {
  var saveBtn = document.getElementById('btn-save');
  var banner  = document.getElementById('checklist-editing-banner');
  if (saveBtn) {
    saveBtn.innerHTML = lly_checklistEditingId
      ? ('💾 <span data-lang="en">Update Entry #' + lly_checklistEditingId + '</span><span data-lang="es">Actualizar Entrada #' + lly_checklistEditingId + '</span>')
      : '💾 <span data-lang="en">Save to Log</span><span data-lang="es">Guardar en Bitácora</span>';
  }
  if (banner) { banner.hidden = !lly_checklistEditingId; }
}

function initChecklistCancelEdit() {
  var btn = document.getElementById('btn-cancel-edit');
  if (!btn) return;
  btn.addEventListener('click', checklistResetForm);
}

/** Walks every rendered row, keeping only ones the crew actually touched — an untouched item-row (default blank state) doesn't need to round-trip to the DB. */
function checklistSerializeItems() {
  var items = {};
  document.querySelectorAll('#checklist-panels [data-field-id]').forEach(function (el) {
    var status  = el.dataset.status || '';
    var countEl = el.querySelector('.item-count');
    var notesEl = el.querySelector('.item-notes');
    var expiryEl = el.querySelector('.item-expiry');
    var count  = countEl ? countEl.value : '';
    var notes  = notesEl ? notesEl.value : '';
    var expiry = expiryEl ? expiryEl.value : '';
    if (!status && !count && !notes && !expiry) return;
    items[el.dataset.fieldId] = { status: status, count: count, notes: notes, expiry: expiry };
  });
  return items;
}

/** Blocks Save/Print until the Captain signature field is filled — jumps to the Sign-Off tab and highlights it if not. Shared by both actions. */
function checklistRequireSignature() {
  var sigInput = document.getElementById('captain-signature');
  var sigLabel = document.getElementById('sig-label');
  if (!sigInput) return true;
  if (sigInput.value.trim()) {
    sigInput.classList.remove('invalid');
    if (sigLabel) { sigLabel.classList.remove('invalid'); }
    return true;
  }
  sigInput.classList.add('invalid');
  if (sigLabel) { sigLabel.classList.add('invalid'); }
  var signoffTab = document.querySelector('.checklist-tab[data-tab-target="signoff"]');
  if (signoffTab) { signoffTab.click(); }
  sigInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
  sigInput.focus();
  var lang = document.body.dataset.activeLang;
  checklistShowValidationAlert(lang === 'es'
    ? '⚠️ Se requiere la firma del Capitán antes de continuar.'
    : '⚠️ Captain signature is required before continuing.');
  return false;
}

function initChecklistSave() {
  var saveBtn = document.getElementById('btn-save');
  if (!saveBtn) return;

  saveBtn.addEventListener('click', function () {
    if (!checklistRequireSignature()) return;

    var val = function (id) { var el = document.getElementById(id); return el ? el.value : ''; };
    var modeAfter = document.getElementById('mode-after');
    var sigDate = val('sig-date');
    var sigTime = val('sig-time');
    var isEdit = !!lly_checklistEditingId;

    var body = new URLSearchParams();
    body.set('action', isEdit ? 'update' : 'save');
    if (isEdit) { body.set('id', lly_checklistEditingId); }
    body.set('csrf_token', val('checklist-csrf-field'));
    body.set('vessel_name', val('op-vessel'));
    body.set('charter_date', val('op-date'));
    body.set('inspection_mode', modeAfter && modeAfter.classList.contains('active') ? 'after' : 'before');
    body.set('guests_count', val('op-guests'));
    body.set('captain_name', val('op-captain'));
    body.set('checked_by', val('op-checkedby'));
    body.set('missing_report', val('final-missing-report'));
    body.set('required_actions', val('final-actions'));
    body.set('captain_signature', val('captain-signature'));
    body.set('signed_at', sigDate ? (sigDate + ' ' + (sigTime || '00:00') + ':00') : '');
    body.set('items_json', JSON.stringify(checklistSerializeItems()));

    saveBtn.disabled = true;
    fetch('api/inventory_checklists.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    }).then(function (res) { return res.json(); }).then(function (data) {
      llySyncCsrfFields(data.csrf_token);
      var lang = document.body.dataset.activeLang;
      if (data.status !== 'success') {
        checklistShowToast(data.message || (lang === 'es' ? 'No se pudo guardar.' : 'Could not save.'), true);
        return;
      }
      checklistShowToast(isEdit
        ? (lang === 'es' ? ('✅ Actualizado — bitácora #' + data.id) : ('✅ Updated — log #' + data.id))
        : (lang === 'es' ? ('✅ Guardado — bitácora #' + data.id) : ('✅ Saved — log #' + data.id)), false);
      checklistResetForm();
    }).catch(function () {
      var lang = document.body.dataset.activeLang;
      checklistShowToast(lang === 'es' ? 'Error de red.' : 'Network error.', true);
    }).then(function () { saveBtn.disabled = false; });
  });
}

/* ── Print — validate signature, force every tab panel visible, restore after ── */
function initChecklistPrint() {
  var printBtn = document.getElementById('btn-print');
  if (!printBtn) return;

  printBtn.addEventListener('click', function () {
    if (!checklistRequireSignature()) return;

    var previousHidden = [];
    document.querySelectorAll('#checklist-panels .tab-panel').forEach(function (p) {
      previousHidden.push([p, p.hidden]);
      p.hidden = false;
    });

    window.print();
    window.addEventListener('afterprint', function restore() {
      previousHidden.forEach(function (pair) { pair[0].hidden = pair[1]; });
      window.removeEventListener('afterprint', restore);
    });
  });
}

/* ── Historial — list + search/date filters (server-side; q hits the
   FULLTEXT search_blob built in api/inventory_checklists.php) ── */
var lly_checklistHistoryRows = [];

function checklistPillForCount(icon, count, cls) {
  return count > 0 ? '<span class="pill pill-' + cls + '">' + icon + ' ' + count + '</span>' : '';
}

function checklistRenderHistoryRows(rows) {
  var tbody = document.getElementById('checklist-history-tbody');
  if (!tbody) return;
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="7">' +
      '<span data-lang="en">No saved checklists yet.</span><span data-lang="es">Aún no hay checklists guardados.</span></td></tr>';
    return;
  }
  tbody.innerHTML = rows.map(function (r) {
    var modeLabel = r.inspection_mode === 'after'
      ? '<span data-lang="en">After</span><span data-lang="es">Después</span>'
      : '<span data-lang="en">Before</span><span data-lang="es">Antes</span>';
    var flagged = [
      checklistPillForCount('⚠️', r.damaged_count, 'orange'),
      checklistPillForCount('❌', r.missing_count, 'pink'),
      checklistPillForCount('🔄', r.replace_count, 'gold'),
    ].filter(Boolean).join(' ') || ('<span class="pill pill-green">✅ ' + r.good_count + '</span>');
    var dateStr = r.charter_date || (r.created_at ? String(r.created_at).substring(0, 10) : '—');
    return '<tr>' +
      '<td>' + checklistEscape(dateStr) + '</td>' +
      '<td>' + checklistEscape(r.vessel_name) + '</td>' +
      '<td>' + modeLabel + '</td>' +
      '<td>' + (r.captain_name ? checklistEscape(r.captain_name) : '—') + '</td>' +
      '<td>' + (r.guests_count != null ? r.guests_count : '—') + '</td>' +
      '<td>' + flagged + '</td>' +
      '<td class="checklist-row-actions">' +
      '<button type="button" class="dash-card-btn dash-card-btn--secondary checklist-detail-btn" data-id="' + r.id + '"><span data-lang="en">👁️ View</span><span data-lang="es">👁️ Ver</span></button> ' +
      '<button type="button" class="dash-card-btn dash-card-btn--secondary checklist-edit-btn" data-id="' + r.id + '"><span data-lang="en">✏️ Edit</span><span data-lang="es">✏️ Editar</span></button> ' +
      '<button type="button" class="dash-card-btn dash-card-btn--secondary checklist-delete-btn" data-id="' + r.id + '"><span data-lang="en">🗑️ Delete</span><span data-lang="es">🗑️ Borrar</span></button>' +
      '</td>' +
      '</tr>';
  }).join('');
}

function checklistLoadHistory() {
  var tbody = document.getElementById('checklist-history-tbody');
  if (!tbody) return;
  var csrfField  = document.getElementById('checklist-csrf-field');
  var searchEl   = document.getElementById('checklist-search-input');
  var dateFromEl = document.getElementById('checklist-date-from');
  var dateToEl   = document.getElementById('checklist-date-to');

  var body = new URLSearchParams();
  body.set('action', 'list');
  body.set('csrf_token', csrfField ? csrfField.value : '');
  if (searchEl && searchEl.value.trim()) { body.set('q', searchEl.value.trim()); }
  if (dateFromEl && dateFromEl.value) { body.set('date_from', dateFromEl.value); }
  if (dateToEl && dateToEl.value) { body.set('date_to', dateToEl.value); }

  fetch('api/inventory_checklists.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) { return res.json(); }).then(function (data) {
    if (data.status !== 'success') return;
    llySyncCsrfFields(data.csrf_token);
    lly_checklistHistoryRows = data.checklists || [];
    checklistRenderHistoryRows(lly_checklistHistoryRows);
  }).catch(function () { /* degrade silently — table just keeps its loading row */ });
}

function initChecklistHistoryFilters() {
  var searchEl   = document.getElementById('checklist-search-input');
  var dateFromEl = document.getElementById('checklist-date-from');
  var dateToEl   = document.getElementById('checklist-date-to');
  var clearBtn   = document.getElementById('checklist-filter-clear');
  if (!searchEl && !dateFromEl && !dateToEl) return;

  var debounceTimer;
  if (searchEl) {
    searchEl.addEventListener('input', function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(checklistLoadHistory, 350);
    });
  }
  if (dateFromEl) { dateFromEl.addEventListener('change', checklistLoadHistory); }
  if (dateToEl) { dateToEl.addEventListener('change', checklistLoadHistory); }
  if (clearBtn) {
    clearBtn.addEventListener('click', function () {
      if (searchEl) { searchEl.value = ''; }
      if (dateFromEl) { dateFromEl.value = ''; }
      if (dateToEl) { dateToEl.value = ''; }
      checklistLoadHistory();
    });
  }
}

/* ── Historial detail dialog — read-only "bitácora" entry view; only
   flagged (non-"good") items are shown, grouped by section, resolved
   against window.LLY_CHECKLIST_FIELD_LABELS built by checklist.php ── */
function checklistFormatFieldEntry(fieldId, entry, lang) {
  var meta = (window.LLY_CHECKLIST_FIELD_LABELS || {})[fieldId];
  var label = meta ? meta.item[lang] : fieldId;
  var unitLabel = meta && meta.unit ? (' — ' + meta.unit[lang]) : '';
  var statusIcons = { good: '✅', damaged: '⚠️', missing: '❌', replace: '🔄' };
  var icon = statusIcons[entry.status] || '•';
  var extras = [];
  if (entry.count)  { extras.push((lang === 'es' ? 'Cant.' : 'Count') + ': ' + checklistEscape(entry.count)); }
  if (entry.expiry) { extras.push((lang === 'es' ? 'Vence' : 'Expiry') + ': ' + checklistEscape(entry.expiry)); }
  if (entry.notes)  { extras.push(checklistEscape(entry.notes)); }
  return '<div class="checklist-detail-row"><span class="k">' + icon + ' ' + checklistEscape(label) + unitLabel + '</span>' +
    '<span>' + (extras.join(' · ') || '—') + '</span></div>';
}

function checklistRenderDetail(record) {
  var lang = document.body.dataset.activeLang === 'es' ? 'es' : 'en';
  var titleEl   = document.getElementById('checklist-detail-dialog-title');
  var eyebrowEl = document.getElementById('checklist-detail-eyebrow');
  var bodyEl    = document.getElementById('checklist-detail-body');
  if (!bodyEl) return;

  var dateStr = record.charter_date || String(record.created_at || '').substring(0, 10);
  if (titleEl) { titleEl.textContent = record.vessel_name + ' — ' + dateStr; }
  if (eyebrowEl) {
    var modeText = record.inspection_mode === 'after'
      ? (lang === 'es' ? 'Después del Charter' : 'After Charter')
      : (lang === 'es' ? 'Antes del Charter' : 'Before Charter');
    eyebrowEl.textContent = modeText + ' · ' + (record.captain_name || '—');
  }

  var payload = record.payload || {};
  var bySection = {};
  Object.keys(payload).forEach(function (fieldId) {
    var entry = payload[fieldId];
    if (!entry || !entry.status || entry.status === 'good') return;
    var meta = (window.LLY_CHECKLIST_FIELD_LABELS || {})[fieldId];
    var sectionLabel = meta ? meta.section[lang] : fieldId;
    if (!bySection[sectionLabel]) { bySection[sectionLabel] = []; }
    bySection[sectionLabel].push(checklistFormatFieldEntry(fieldId, entry, lang));
  });

  var html = '';
  var sectionNames = Object.keys(bySection);
  if (!sectionNames.length) {
    html += '<p style="color:var(--ink-60);">' +
      (lang === 'es' ? 'Todo en buen estado — sin ítems marcados.' : 'Everything in good condition — no flagged items.') + '</p>';
  } else {
    sectionNames.forEach(function (name) {
      html += '<h3 class="checklist-detail-block-title">' + checklistEscape(name) + '</h3>' + bySection[name].join('');
    });
  }

  html += '<h3 class="checklist-detail-block-title">' + (lang === 'es' ? 'Cierre' : 'Sign-Off') + '</h3>';
  html += '<div class="checklist-detail-row"><span class="k">' + (lang === 'es' ? 'Huéspedes' : 'Guests') + '</span><span>' + (record.guests_count != null ? record.guests_count : '—') + '</span></div>';
  html += '<div class="checklist-detail-row"><span class="k">' + (lang === 'es' ? 'Revisado por' : 'Checked by') + '</span><span>' + checklistEscape(record.checked_by || '—') + '</span></div>';
  if (record.missing_report) { html += '<div class="checklist-detail-row"><span class="k">' + (lang === 'es' ? 'Reporte de faltantes' : 'Missing report') + '</span><span>' + checklistEscape(record.missing_report) + '</span></div>'; }
  if (record.required_actions) { html += '<div class="checklist-detail-row"><span class="k">' + (lang === 'es' ? 'Acciones requeridas' : 'Required actions') + '</span><span>' + checklistEscape(record.required_actions) + '</span></div>'; }
  html += '<div class="checklist-detail-row"><span class="k">' + (lang === 'es' ? 'Firma' : 'Signature') + '</span><span>' + checklistEscape(record.captain_signature || '—') + (record.signed_at ? (' · ' + checklistEscape(record.signed_at)) : '') + '</span></div>';

  bodyEl.innerHTML = html;
}

/** Shared by View/Edit — fetches one full record (with decoded payload) and resolves it, or null on any failure. */
function checklistFetchRecord(id) {
  var csrfField = document.getElementById('checklist-csrf-field');
  var body = new URLSearchParams();
  body.set('action', 'get');
  body.set('id', id);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  return fetch('api/inventory_checklists.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) { return res.json(); }).then(function (data) {
    llySyncCsrfFields(data.csrf_token);
    return data.status === 'success' ? data.checklist : null;
  }).catch(function () { return null; });
}

/** Resolves true on success, false on any failure — caller decides what UI follows. */
function checklistDeleteRecord(id) {
  var csrfField = document.getElementById('checklist-csrf-field');
  var body = new URLSearchParams();
  body.set('action', 'delete');
  body.set('id', id);
  body.set('csrf_token', csrfField ? csrfField.value : '');
  return fetch('api/inventory_checklists.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  }).then(function (res) { return res.json(); }).then(function (data) {
    llySyncCsrfFields(data.csrf_token);
    return data.status === 'success';
  }).catch(function () { return false; });
}

/** Historial row actions — View (read-only dialog), Edit (loads the record into the live Checklist form), Delete (window.confirm(), same pattern as api/fleet_catalog.php's vessel delete). All three share one delegated listener on the tbody. */
function initChecklistHistoryRowActions() {
  var tbody  = document.getElementById('checklist-history-tbody');
  var dialog = document.getElementById('checklist-detail-dialog');
  if (!tbody) return;

  if (dialog) {
    var closeBtn = document.getElementById('checklist-detail-close');
    if (closeBtn) { closeBtn.addEventListener('click', function () { dialog.close(); }); }
  }

  tbody.addEventListener('click', function (e) {
    var viewBtn   = e.target.closest('.checklist-detail-btn');
    var editBtn   = e.target.closest('.checklist-edit-btn');
    var deleteBtn = e.target.closest('.checklist-delete-btn');
    var lang = document.body.dataset.activeLang;

    if (viewBtn && dialog) {
      var id = viewBtn.getAttribute('data-id');
      var titleEl = document.getElementById('checklist-detail-dialog-title');
      var bodyEl  = document.getElementById('checklist-detail-body');
      if (titleEl) { titleEl.textContent = 'Loading…'; }
      if (bodyEl) { bodyEl.innerHTML = ''; }
      dialog.showModal();
      checklistFetchRecord(id).then(function (record) {
        if (!record) {
          if (titleEl) { titleEl.textContent = 'Error'; }
          if (bodyEl) { bodyEl.textContent = 'Could not load this record.'; }
          return;
        }
        checklistRenderDetail(record);
      });
    }

    if (editBtn) {
      checklistFetchRecord(editBtn.getAttribute('data-id')).then(function (record) {
        if (!record) {
          checklistShowToast(lang === 'es' ? 'No se pudo cargar este registro.' : 'Could not load this record.', true);
          return;
        }
        checklistPopulateForm(record);
        var switcher = document.getElementById('view-btn-checklist');
        if (switcher) { switcher.click(); }
        checklistShowToast(lang === 'es' ? ('✏️ Editando entrada #' + record.id) : ('✏️ Editing entry #' + record.id), false);
      });
    }

    if (deleteBtn) {
      var deleteId = deleteBtn.getAttribute('data-id');
      var msg = lang === 'es'
        ? '¿Borrar esta entrada de la bitácora? Esta acción no se puede deshacer.'
        : 'Delete this bitácora entry? This cannot be undone.';
      if (!window.confirm(msg)) return;
      checklistDeleteRecord(deleteId).then(function (ok) {
        if (!ok) {
          checklistShowToast(lang === 'es' ? 'No se pudo borrar.' : 'Could not delete.', true);
          return;
        }
        if (lly_checklistEditingId && String(lly_checklistEditingId) === String(deleteId)) { checklistResetForm(); }
        checklistShowToast(lang === 'es' ? '🗑️ Entrada borrada.' : '🗑️ Entry deleted.', false);
        checklistLoadHistory();
      });
    }
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

/**
 * Runs one init function in isolation — a thrown error (or a bad
 * assumption inside it) is logged and swallowed instead of stopping the
 * rest of llyInitAll(). Each init* already self-guards on its own
 * element's absence (if (!el) return;), so this is a second, independent
 * safety net: one panel misbehaving can never take the others down with
 * it. Named functions only (not arrow fns) so the name shows up in the
 * console.error for whoever's debugging.
 */
function llySafeInit(fn) {
  try {
    fn();
  } catch (err) {
    console.error('[LLY init] ' + fn.name + '() failed — other panels continue.', err);
  }
}

function llyInitAll() {
  llySafeInit(restoreTheme);       /* html[data-theme] from localStorage            */
  llySafeInit(restoreLang);        /* sync toggle buttons (body attr set by IIFE)   */
  llySafeInit(resolveUrlParams);   /* ?lang= and #hash activation                   */
  llySafeInit(initHubCards);       /* .hub-card[data-target] → activateHub          */
  llySafeInit(initTopbarNav);      /* .topbar-nav-link[data-target] → activateHubFromTopbar */
  llySafeInit(initLangToggle);     /* #btn-en / #btn-es → setLang                   */
  llySafeInit(initThemeToggle);    /* #theme-toggle → toggleTheme                   */
  llySafeInit(initAccordion);      /* .accordion-trigger → toggleAccordion          */
  llySafeInit(initReportDialog);   /* .dash-pay-row → openReportDialog              */
  llySafeInit(initSmoothScroll);   /* a[href^="#"] → scrollIntoView                 */
  llySafeInit(initBackToTop);      /* #back-to-top → scrollTo(0)                   */
  llySafeInit(initEphemeralLinksPanel); /* #ephemeral-links-table → create/list/revoke */
  llySafeInit(initLeadsPanel);          /* #leads-table → list recent WhatsApp/Web leads */
  llySafeInit(initLeadsFilters);        /* #leads-search-input / #leads-date-from / #leads-date-to → filter the leads list */
  llySafeInit(initLeadDetailModal);     /* #lead-detail-dialog → "Ver Resumen y Charla" per-row modal */
  llySafeInit(initPgaiSettingsPanel);   /* #pgai-settings-form → get/save AURA+WhatsApp config */
  llySafeInit(initFleetCatalogPanel);   /* #fleet-catalog-table → create/list/update/delete vessels */
  llySafeInit(initPromptEditorPanel);        /* #prompt-editor-textarea → get/save master prompt */
  llySafeInit(initNotificationTemplatesPanel); /* #templates-list → list/update lead notification templates */
  llySafeInit(initModuleDocEditorPanel);     /* #moduledoc-editor-textarea → get/save knowledge module doc */
  llySafeInit(initHandshakeTestPanel);       /* #handshake-test-btn → one-click AURA handshake test */
  llySafeInit(initOpenAiTestPanel);          /* #openai-test-btn → one-click OpenAI connection test */
  llySafeInit(initChecklistViewSwitch);      /* #view-btn-checklist/#view-btn-history → show one view at a time */
  llySafeInit(initChecklistTabs);            /* #checklist-tabs → show one section panel at a time + Prev/Next */
  llySafeInit(initChecklistForm);            /* #checklist-panels → status buttons, mode toggle, guests-vs-vests check */
  llySafeInit(initChecklistLangSync);        /* #btn-en/#btn-es → re-sync data-ph-en/es placeholders after language switch */
  llySafeInit(initChecklistSave);            /* #btn-save → serialize + POST api/inventory_checklists.php (save or update) */
  llySafeInit(initChecklistCancelEdit);      /* #btn-cancel-edit → checklistResetForm(), exits edit mode */
  llySafeInit(initChecklistPrint);           /* #btn-print → validate signature, force panels visible, window.print() */
  llySafeInit(initChecklistHistoryFilters);  /* #checklist-search-input / date filters → checklistLoadHistory() */
  llySafeInit(initChecklistHistoryRowActions); /* #checklist-history-tbody → View/Edit/Delete per row */
}

if (document.readyState === 'loading') {
  /* Script somehow ran before parse completed — wait for DOM ready */
  document.addEventListener('DOMContentLoaded', llyInitAll);
} else {
  /* DOM already parsed (normal path for a deferred script) — run now */
  llyInitAll();
}
