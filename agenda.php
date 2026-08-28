<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — agenda.php
 * PG-AI Pink Glove AI — Booking Calendar (Agenda). Independent, modular
 * Cockpit page (direct deep link, not an include) — validates its own
 * session like pg_ai_hub.php/chat-lab.php.
 *
 * Two-tier data model, merged live by api/bookings.php, never duplicated:
 *   🟡 Interested/Quoting — chatbot leads with a route+date captured
 *      (core/PgAiActionProcessor.php) that never became a formal booking.
 *   🟢 Confirmed/Reserved — real rows in yacht_bookings (sql/011),
 *      deposit-backed, entered by a human once a lead converts.
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
  <meta name="description" content="Lover Lips Yachts — Booking Calendar" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Agenda</title>
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

        <a href="pg_ai_hub.php" class="topbar-logo" aria-label="Lover Lips Yachts — PG-AI Hub">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Agenda · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
          <a href="pg_ai_hub.php" class="topbar-back-btn">
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

    <!-- ═══════════════════════════════════════════════════════════════
         HEADER
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section section-white" aria-labelledby="agenda-title">
      <div class="container">
        <p class="section-label">
          <span data-lang="en">Booking Calendar</span>
          <span data-lang="es">Calendario de Reservas</span>
        </p>
        <h1 class="section-title" id="agenda-title">
          <span data-lang="en">📅 <em>Agenda</em></span>
          <span data-lang="es">📅 <em>Agenda</em></span>
        </h1>
        <p class="section-subtitle" data-lang="en">
          Interested leads from the chatbot and confirmed, deposit-backed reservations, in one calendar.
        </p>
        <p class="section-subtitle" data-lang="es">
          Leads interesados del chatbot y reservas confirmadas con depósito, en un solo calendario.
        </p>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         CALENDAR
    ═══════════════════════════════════════════════════════════════ -->
    <section class="section" id="agenda-section" aria-labelledby="agenda-calendar-title">
      <div class="container">
        <input type="hidden" id="agenda-csrf-field" value="<?= $lly_csrf ?>">

        <div class="agenda-toolbar">
          <div class="agenda-month-nav">
            <button type="button" class="agenda-nav-btn" id="agenda-prev-month" aria-label="Previous month">‹</button>
            <h2 class="agenda-month-label" id="agenda-month-label">—</h2>
            <button type="button" class="agenda-nav-btn" id="agenda-next-month" aria-label="Next month">›</button>
          </div>

          <div class="agenda-filters">
            <select id="agenda-filter-yacht" class="agenda-filter-select" aria-label="Filter by yacht">
              <option value="all">All Yachts / Todos los Yates</option>
              <option value="CNR Maranatha 120">CNR Maranatha 120</option>
              <option value="Pink Lips">Pink Lips</option>
              <option value="Lover Lips">Lover Lips</option>
            </select>
            <input type="number" id="agenda-filter-pax-min" class="agenda-filter-input" placeholder="PAX min" min="0" max="500" />
            <input type="number" id="agenda-filter-pax-max" class="agenda-filter-input" placeholder="PAX max" min="0" max="500" />
          </div>
        </div>

        <div class="agenda-legend">
          <span class="agenda-legend-item"><span class="agenda-dot agenda-dot--interested"></span> <span data-lang="en">Interested / Quoting</span><span data-lang="es">Interesados / En Cotización</span></span>
          <span class="agenda-legend-item"><span class="agenda-dot agenda-dot--confirmed"></span> <span data-lang="en">Confirmed / Reserved</span><span data-lang="es">Confirmados / Reservados</span></span>
          <span class="agenda-legend-item"><span class="agenda-dot agenda-dot--other"></span> <span data-lang="en">Completed / Cancelled</span><span data-lang="es">Completados / Cancelados</span></span>
        </div>

        <div class="agenda-grid-wrap">
          <div class="agenda-weekday-row">
            <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
          </div>
          <div class="agenda-grid" id="agenda-grid" aria-live="polite">
            <p class="agenda-loading"><span data-lang="en">Loading…</span><span data-lang="es">Cargando…</span></p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════════
         BOOKING DETAIL DIALOG
    ═══════════════════════════════════════════════════════════════ -->
    <dialog class="chapter-dialog" id="agenda-detail-dialog" aria-labelledby="agenda-detail-title">
      <div class="chapter-dialog-inner">
        <button type="button" class="chapter-dialog-close" id="agenda-detail-close" aria-label="Close">✕</button>
        <p class="chapter-dialog-eyebrow" id="agenda-detail-eyebrow"></p>
        <h2 class="chapter-dialog-title" id="agenda-detail-title"></h2>
        <div class="chapter-dialog-body">
          <div class="agenda-detail-grid" id="agenda-detail-grid"></div>
          <p id="agenda-detail-summary" class="lead-detail-summary" hidden></p>
          <a class="dash-card-btn u-mt-xs" id="agenda-detail-open-chat" href="pg_ai_hub.php" hidden>
            <span data-lang="en">💬 Open Lead Chat</span>
            <span data-lang="es">💬 Abrir Chat del Lead</span>
          </a>
        </div>
      </div>
    </dialog>

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
        <span data-lang="en">Agenda · Confidential · Owner Only</span>
        <span data-lang="es">Agenda · Confidencial · Solo Propietario</span>
      </p>
    </div>
  </footer>

  <button id="back-to-top" class="back-to-top" aria-label="Back to top" type="button">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>

  <!-- Page-specific inline script — same pattern as aura_diagnostic.php /
       chat-lab.php: this page is independent and modular, its calendar
       logic doesn't belong in the shared main.js bundle. -->
  <script>
  (function () {
    var csrfField   = document.getElementById('agenda-csrf-field');
    var monthLabel  = document.getElementById('agenda-month-label');
    var grid        = document.getElementById('agenda-grid');
    var yachtFilter = document.getElementById('agenda-filter-yacht');
    var paxMinInput = document.getElementById('agenda-filter-pax-min');
    var paxMaxInput = document.getElementById('agenda-filter-pax-max');
    var dialog      = document.getElementById('agenda-detail-dialog');
    if (!grid) return;

    var today   = new Date();
    var viewYear  = today.getFullYear();
    var viewMonth = today.getMonth() + 1; // 1-12

    var MONTH_NAMES_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var MONTH_NAMES_ES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    function currentLang() {
      return (document.body && document.body.dataset.activeLang === 'es') ? 'es' : 'en';
    }

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = str == null ? '' : String(str);
      return div.innerHTML;
    }

    function dotClass(status) {
      if (status === 'interested' || status === 'quote_sent') return 'agenda-dot--interested';
      if (status === 'confirmed') return 'agenda-dot--confirmed';
      return 'agenda-dot--other';
    }

    function post(action, extraFields) {
      var body = new URLSearchParams();
      body.set('action', action);
      body.set('csrf_token', csrfField ? csrfField.value : '');
      if (extraFields) {
        Object.keys(extraFields).forEach(function (key) {
          var v = extraFields[key];
          body.set(key, v == null ? '' : String(v));
        });
      }
      return fetch('api/bookings.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
      }).then(function (res) { return res.json(); }).then(function (data) {
        if (csrfField && data.csrf_token) { csrfField.value = data.csrf_token; }
        return data;
      });
    }

    function renderMonthLabel() {
      var names = currentLang() === 'es' ? MONTH_NAMES_ES : MONTH_NAMES_EN;
      monthLabel.textContent = names[viewMonth - 1] + ' ' + viewYear;
    }

    function renderCalendar(bookingsByDay, daysInMonth, firstWeekday) {
      var html = '';
      for (var i = 0; i < firstWeekday; i++) {
        html += '<div class="agenda-cell agenda-cell--empty"></div>';
      }
      for (var day = 1; day <= daysInMonth; day++) {
        var dateStr = viewYear + '-' + String(viewMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        var items = bookingsByDay[dateStr] || [];
        var isToday = dateStr === today.toISOString().slice(0, 10);

        var dotsHtml = items.slice(0, 4).map(function (b) {
          var label = (b.guest_name || b.route_destination || 'Lead') + ' — ' + (b.status || 'interested');
          var idAttr = b.source === 'booking' ? 'data-booking-id="' + b.id + '"' : 'data-lead-session-id="' + b.session_id + '"';
          return '<button type="button" class="agenda-dot ' + dotClass(b.status) + '" ' + idAttr + ' title="' + escapeHtml(label) + '"></button>';
        }).join('');
        var overflow = items.length > 4 ? '<span class="agenda-cell-overflow">+' + (items.length - 4) + '</span>' : '';

        html += '<div class="agenda-cell' + (isToday ? ' agenda-cell--today' : '') + '">'
          + '<span class="agenda-cell-daynum">' + day + '</span>'
          + '<div class="agenda-cell-dots">' + dotsHtml + overflow + '</div>'
          + '</div>';
      }
      grid.innerHTML = html;

      grid.querySelectorAll('[data-booking-id]').forEach(function (btn) {
        btn.addEventListener('click', function () { openDetail({ id: btn.getAttribute('data-booking-id') }); });
      });
      grid.querySelectorAll('[data-lead-session-id]').forEach(function (btn) {
        btn.addEventListener('click', function () { openDetail({ lead_session_id: btn.getAttribute('data-lead-session-id') }); });
      });
    }

    function loadMonth() {
      renderMonthLabel();
      grid.innerHTML = '<p class="agenda-loading">' + (currentLang() === 'es' ? 'Cargando…' : 'Loading…') + '</p>';

      var daysInMonth  = new Date(viewYear, viewMonth, 0).getDate();
      var firstWeekday = new Date(viewYear, viewMonth - 1, 1).getDay();

      post('list', {
        year: viewYear,
        month: viewMonth,
        yacht: yachtFilter ? yachtFilter.value : 'all',
        pax_min: paxMinInput ? paxMinInput.value : '',
        pax_max: paxMaxInput ? paxMaxInput.value : '',
      }).then(function (data) {
        if (data.status !== 'success') {
          grid.innerHTML = '<p class="agenda-loading">' + escapeHtml(data.message || 'Error') + '</p>';
          return;
        }
        var byDay = {};
        (data.bookings || []).forEach(function (b) {
          if (!b.charter_date) return;
          var key = String(b.charter_date).slice(0, 10);
          (byDay[key] = byDay[key] || []).push(b);
        });
        renderCalendar(byDay, daysInMonth, firstWeekday);
      }).catch(function () {
        grid.innerHTML = '<p class="agenda-loading">' + (currentLang() === 'es' ? 'Error de red.' : 'Network error.') + '</p>';
      });
    }

    function fieldRow(labelEn, labelEs, value) {
      if (!value) return '';
      return '<div class="agenda-detail-field"><span class="agenda-detail-field-label">' + (currentLang() === 'es' ? labelEs : labelEn) + '</span><span class="agenda-detail-field-value">' + escapeHtml(value) + '</span></div>';
    }

    function openDetail(params) {
      var titleEl   = document.getElementById('agenda-detail-title');
      var eyebrowEl = document.getElementById('agenda-detail-eyebrow');
      var gridEl    = document.getElementById('agenda-detail-grid');
      var summaryEl = document.getElementById('agenda-detail-summary');
      var chatBtn   = document.getElementById('agenda-detail-open-chat');

      titleEl.textContent = currentLang() === 'es' ? 'Cargando…' : 'Loading…';
      eyebrowEl.textContent = '';
      gridEl.innerHTML = '';
      summaryEl.hidden = true;
      chatBtn.hidden = true;
      dialog.showModal();

      post('detail', params).then(function (data) {
        if (data.status !== 'success' || !data.booking) {
          titleEl.textContent = 'Error';
          gridEl.innerHTML = '<p>' + escapeHtml(data.message || 'Not found') + '</p>';
          return;
        }
        var b = data.booking;
        var isLead = data.source === 'lead';

        titleEl.textContent = b.guest_name || (currentLang() === 'es' ? 'Sin nombre aún' : 'No name yet');
        eyebrowEl.textContent = isLead
          ? (currentLang() === 'es' ? '🟡 Lead del Chatbot — aún no formalizado' : '🟡 Chatbot Lead — not yet formalized')
          : ('🟢 ' + (b.status || '').toUpperCase() + (b.yacht_name ? ' — ' + b.yacht_name : ''));

        var rowsHtml = ''
          + fieldRow('Route', 'Ruta', b.route_destination)
          + fieldRow('Date', 'Fecha', b.charter_date)
          + fieldRow('Time Slot', 'Horario', b.charter_time_slot)
          + fieldRow('PAX', 'PAX', b.pax_count)
          + fieldRow('Phone', 'Teléfono', b.guest_phone)
          + fieldRow('Email', 'Correo', b.guest_email);

        if (!isLead) {
          var balance = '';
          if (b.total_price != null) {
            var deposit = b.deposit_paid != null ? parseFloat(b.deposit_paid) : 0;
            var total   = parseFloat(b.total_price);
            balance = '$' + deposit.toFixed(2) + ' / $' + total.toFixed(2) + ' (' + (b.payment_status || 'pending') + ')';
          }
          rowsHtml += fieldRow('Payment Balance (Cash/Trade 50/50)', 'Balance de Pago (Cash/Trade 50/50)', balance);
        }

        gridEl.innerHTML = rowsHtml || ('<p>' + (currentLang() === 'es' ? 'Sin datos adicionales.' : 'No additional data.') + '</p>');

        if (isLead && b.summary) {
          summaryEl.textContent = b.summary;
          summaryEl.hidden = false;
        }

        var sessionForChat = isLead ? b.id : (data.booking.session_id || null);
        if (sessionForChat) {
          chatBtn.href = 'leads.php?open_lead=' + encodeURIComponent(sessionForChat);
          chatBtn.hidden = false;
        }
      }).catch(function () {
        titleEl.textContent = 'Error';
        gridEl.innerHTML = '<p>' + (currentLang() === 'es' ? 'Error de red.' : 'Network error.') + '</p>';
      });
    }

    var closeBtn = document.getElementById('agenda-detail-close');
    if (closeBtn) { closeBtn.addEventListener('click', function () { dialog.close(); }); }

    var prevBtn = document.getElementById('agenda-prev-month');
    var nextBtn = document.getElementById('agenda-next-month');
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        viewMonth--; if (viewMonth < 1) { viewMonth = 12; viewYear--; }
        loadMonth();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        viewMonth++; if (viewMonth > 12) { viewMonth = 1; viewYear++; }
        loadMonth();
      });
    }
    [yachtFilter, paxMinInput, paxMaxInput].forEach(function (el) {
      if (el) { el.addEventListener('change', loadMonth); }
    });

    new MutationObserver(renderMonthLabel).observe(document.body, { attributes: true, attributeFilter: ['data-active-lang'] });

    loadMonth();
  }());
  </script>

</body>
</html>
