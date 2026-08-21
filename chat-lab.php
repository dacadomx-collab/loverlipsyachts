<?php
declare(strict_types=1);

/**
 * LOVER LIPS YACHTS — chat-lab.php
 * Mobile-first PG-AI audit console — a lean, full-height chat screen so
 * Lester can test the live chatbot straight from his phone without going
 * through the full pg_ai_hub.php dashboard. Talks to the exact same
 * production endpoint the public site widget uses
 * (api/public/ai_widget_gateway.php) — nothing here is mocked. Direct
 * deep link (not an include), validates its own session like
 * aura_diagnostic.php / pg_ai_hub.php. No footer/full site chrome on
 * purpose — every pixel of viewport height goes to the chat itself.
 */

require __DIR__ . '/api/conexion.php';
require __DIR__ . '/core/auth_check.php';
require __DIR__ . '/core/dev_bypass.php';

if (!lly_is_authenticated()) {
    header('Location: index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Lover Lips Yachts — AI Concierge Chat Lab, mobile audit console" />
  <meta name="robots" content="noindex, nofollow" />
  <title>Lover Lips Yachts · Chat Lab</title>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" />
  <link rel="icon" type="image/png" href="assets/img/logo.png" />
  <script src="assets/js/theme-init.js"></script>
</head>

<body class="chatlab-page" data-active-lang="en">

  <!-- ═══════════════════════════════════════════════════════════════
       TOPBAR — brand, back to Hub, theme + language toggles
  ═══════════════════════════════════════════════════════════════ -->
  <header class="topbar" role="banner">
    <div class="container">
      <div class="topbar-inner">

        <a href="pg_ai_hub.php" class="topbar-logo" aria-label="Lover Lips Yachts — PG-AI Hub">
          <img class="logo-day"   src="assets/img/logo.png"  alt="Lover Lips Yachts" />
          <img class="logo-night" src="assets/img/logo2.png" alt="Lover Lips Yachts" />
          <div class="topbar-brand">
            Lover Lips Yachts
            <span>Chat Lab · Confidential</span>
          </div>
        </a>

        <div class="topbar-actions">
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

  <!-- ═══════════════════════════════════════════════════════════════
       CHAT SHELL — fills the rest of the viewport, scrolls internally
  ═══════════════════════════════════════════════════════════════ -->
  <main class="chatlab-shell">

    <div class="lly-ai-widget-header chatlab-header">
      <div class="chatlab-header-brand">
        <!-- Single asset regardless of day/night theme: the gradient bar
             behind it never changes color with the toggle, so a theme-swapped
             logo-day/logo-night pair doesn't apply here — only logo.png has
             a real alpha channel to render as pure white (see .chatlab-header-logo). -->
        <img class="chatlab-header-logo" src="assets/img/logo.png" alt="" aria-hidden="true" />
        <div>
          <p class="lly-ai-widget-title">
            <span data-lang="en">Lover Lips AI Concierge</span>
            <span data-lang="es">Concierge IA Lover Lips</span>
          </p>
          <p class="lly-ai-widget-subtitle chatlab-status">
            <span class="chatlab-status-dot" aria-hidden="true"></span>
            <span data-lang="en">Concierge Online — live endpoint</span>
            <span data-lang="es">Concierge en Línea — endpoint en vivo</span>
          </p>
        </div>
      </div>
    </div>

    <!-- NO_PRICE_WITHOUT_LEAD_DATA / White-Glove Escalation are internal
         business rules the model applies silently in conversation — never
         surfaced as a UI badge to whoever is chatting (owner included). -->
    <div class="lly-ai-widget-messages chatlab-messages" id="chatlab-messages" aria-live="polite">
      <div class="lly-ai-widget-msg lly-ai-widget-msg--bot" data-lang="en">Hi! I'm the Lover Lips AI Concierge — ask me anything about the fleet, routes, or experiences. This is the exact endpoint real guests talk to.</div>
      <div class="lly-ai-widget-msg lly-ai-widget-msg--bot" data-lang="es">¡Hola! Soy el Concierge IA Lover Lips — pregúntame lo que quieras sobre la flota, rutas o experiencias. Este es el mismo endpoint que usan los huéspedes reales.</div>
    </div>

    <!-- Each chip carries both languages — the visible label follows the
         same [data-lang] toggle as the rest of the page, and the message
         actually SENT on tap (data-prompt-en/es) always matches whichever
         language is showing, so a tap in Spanish gets a Spanish reply. -->
    <div class="chatlab-quickprompts arf-grid" role="group" aria-label="Quick test prompts / Pruebas rápidas">
      <button type="button" class="chatlab-quick-btn"
              data-prompt-en="Quote a charter to Balandra for 8 people"
              data-prompt-es="Cotizar chárter a Balandra para 8 personas">
        <span data-lang="en">🏝️ Quote a charter to Balandra for 8 people</span>
        <span data-lang="es">🏝️ Cotizar chárter a Balandra para 8 personas</span>
      </button>
      <button type="button" class="chatlab-quick-btn"
              data-prompt-en="Ask about the CNR Maranatha 120 for a corporate event"
              data-prompt-es="Preguntar por el CNR Maranatha 120 para un evento corporativo">
        <span data-lang="en">🛥️ Ask about the CNR Maranatha 120 for a corporate event</span>
        <span data-lang="es">🛥️ Preguntar por el CNR Maranatha 120 para un evento corporativo</span>
      </button>
      <button type="button" class="chatlab-quick-btn"
              data-prompt-en="Information about swimming with whale sharks"
              data-prompt-es="Información sobre nado con Tiburón Ballena">
        <span data-lang="en">🐋 Information about swimming with whale sharks</span>
        <span data-lang="es">🐋 Información sobre nado con Tiburón Ballena</span>
      </button>
      <button type="button" class="chatlab-quick-btn"
              data-prompt-en="What's included in the Pink Glove Experience?"
              data-prompt-es="¿Qué incluye la Pink Glove Experience?">
        <span data-lang="en">💗 What's included in the Pink Glove Experience?</span>
        <span data-lang="es">💗 ¿Qué incluye la Pink Glove Experience?</span>
      </button>
    </div>

    <form class="lly-ai-widget-inputrow chatlab-inputrow" id="chatlab-form">
      <input type="text" class="lly-ai-widget-input" id="chatlab-input" maxlength="2000" autocomplete="off"
             data-placeholder-en="Type your message…" data-placeholder-es="Escribe tu mensaje…"
             aria-label="Message / Mensaje" />
      <button type="submit" class="lly-ai-widget-send" id="chatlab-send" aria-label="Send / Enviar">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
      </button>
    </form>

  </main>

  <script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>

  <!-- Page-specific inline script — same pattern as pg_ai_hub.php's
       Section B testbed (only reuses the endpoint + CSS classes, adds
       one-tap Quick Prompt buttons on top). -->
  <script>
  (function () {
    var form      = document.getElementById('chatlab-form');
    var input     = document.getElementById('chatlab-input');
    var sendBtn   = document.getElementById('chatlab-send');
    var thread    = document.getElementById('chatlab-messages');
    var quickBtns = document.querySelectorAll('.chatlab-quick-btn');
    if (!form || !input || !thread) return;

    function getSessionId() {
      // localStorage (not sessionStorage, 2026-08-18) — the point is that
      // reloading the page, or closing the tab and coming back later,
      // reuses the same thread instead of starting a blank one every time.
      var key = 'lly_chatlab_session';
      var id = null;
      try { id = localStorage.getItem(key); } catch (e) {}
      if (!id) {
        id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID().replace(/-/g, '') : ('c' + Date.now() + Math.random().toString(36).slice(2));
        try { localStorage.setItem(key, id); } catch (e) {}
      }
      return id;
    }

    function currentLang() {
      return (document.body && document.body.dataset.activeLang === 'es') ? 'es' : 'en';
    }

    function refreshPlaceholder() {
      var lang = currentLang();
      input.placeholder = input.getAttribute('data-placeholder-' + lang) || '';
    }
    refreshPlaceholder();
    /* main.js's setLang() writes body[data-active-lang] on toggle — watch
       the attribute instead of the button click so this never races the
       toggle's own listener regardless of script order (see pg_ai_widget.js). */
    new MutationObserver(refreshPlaceholder).observe(document.body, {
      attributes: true,
      attributeFilter: ['data-active-lang'],
    });

    function escapeHtml(str) {
      var div = document.createElement('div');
      div.textContent = str == null ? '' : str;
      return div.innerHTML;
    }

    /* Quote links (api/public/l.php?t=...) come back as plain text inside
       the model's reply — turn them into a real clickable link plus a
       one-tap copy button, so sending the quote over WhatsApp doesn't
       require selecting text by hand. Escapes the WHOLE message first,
       then only re-injects markup for the URL substrings it matched
       itself — never trusts the message text as HTML wholesale. */
    function linkifyQuoteUrls(escapedText) {
      var pattern = /(https?:\/\/[^\s&"'<]+\/api\/public\/l\.php\?t=[A-Za-z0-9_-]+)/g;
      return escapedText.replace(pattern, function (url) {
        var copyLabel = currentLang() === 'es' ? '📋 Copiar Enlace' : '📋 Copy Link';
        return '<a href="' + url + '" target="_blank" rel="noopener noreferrer" class="lly-chat-link">' + url + '</a>'
          + '<button type="button" class="lly-chat-copy-btn" data-copy-url="' + url + '">' + copyLabel + '</button>';
      });
    }

    function addMessage(text, who) {
      var el = document.createElement('div');
      el.className = 'lly-ai-widget-msg lly-ai-widget-msg--' + who;
      if (who === 'bot') {
        el.innerHTML = linkifyQuoteUrls(escapeHtml(text));
      } else {
        el.textContent = text;
      }
      thread.appendChild(el);
      thread.scrollTop = thread.scrollHeight;
    }

    thread.addEventListener('click', function (e) {
      var btn = e.target.closest('.lly-chat-copy-btn');
      if (!btn) return;
      var url = btn.getAttribute('data-copy-url');
      if (!url) return;

      var restoreLabel = btn.textContent;
      var copiedLabel = currentLang() === 'es' ? '✅ ¡Copiado!' : '✅ Copied!';

      var afterCopy = function () {
        btn.textContent = copiedLabel;
        btn.classList.add('lly-chat-copy-btn--copied');
        setTimeout(function () {
          btn.textContent = restoreLabel;
          btn.classList.remove('lly-chat-copy-btn--copied');
        }, 2000);
      };

      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(afterCopy).catch(function () {
          btn.textContent = currentLang() === 'es' ? '❌ Error' : '❌ Error';
        });
      } else {
        // Fallback for browsers without the async Clipboard API.
        var tmp = document.createElement('textarea');
        tmp.value = url;
        tmp.style.position = 'fixed';
        tmp.style.opacity = '0';
        document.body.appendChild(tmp);
        tmp.select();
        try { document.execCommand('copy'); afterCopy(); } catch (err) { /* give up silently */ }
        document.body.removeChild(tmp);
      }
    });

    function showTyping() {
      if (document.getElementById('chatlab-typing-indicator')) return;
      var el = document.createElement('div');
      el.className = 'lly-ai-widget-msg lly-ai-widget-msg--bot chatlab-typing';
      el.id = 'chatlab-typing-indicator';
      el.setAttribute('role', 'status');
      el.setAttribute('aria-label', currentLang() === 'es' ? 'Concierge escribiendo…' : 'Concierge typing…');
      el.innerHTML = '<span class="chatlab-typing-dot"></span><span class="chatlab-typing-dot"></span><span class="chatlab-typing-dot"></span>';
      thread.appendChild(el);
      thread.scrollTop = thread.scrollHeight;
    }

    function hideTyping() {
      var el = document.getElementById('chatlab-typing-indicator');
      if (el) el.remove();
    }

    function setBusy(busy) {
      sendBtn.disabled = busy;
      quickBtns.forEach(function (btn) { btn.disabled = busy; });
    }

    function sendMessage(rawText) {
      var text = (rawText || '').trim();
      if (text === '' || sendBtn.disabled) return;

      addMessage(text, 'user');
      input.value = '';
      setBusy(true);
      showTyping();

      fetch('api/public/ai_widget_gateway.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, session_id: getSessionId(), lang: currentLang() })
      })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        hideTyping();
        addMessage(data && data.reply ? data.reply : (currentLang() === 'es' ? 'Ocurrió un error, intenta de nuevo.' : 'Something went wrong, please try again.'), 'bot');
      })
      .catch(function () {
        hideTyping();
        addMessage(currentLang() === 'es' ? 'Error de red — revisa tu conexión.' : 'Network error — check your connection.', 'bot');
      })
      .then(function () {
        setBusy(false);
        input.focus();
      });
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      sendMessage(input.value);
    });

    quickBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var lang = currentLang();
        sendMessage(lang === 'es' ? btn.dataset.promptEs : btn.dataset.promptEn);
      });
    });

    /* Reloading the page (or returning later, same localStorage session_id)
       re-renders the prior thread instead of starting blank — only replaces
       the default greeting bubbles if the session actually has history, so
       a genuinely new visitor still sees the normal welcome message. */
    function loadHistory() {
      var url = 'api/public/ai_widget_gateway.php?action=history&session_id=' + encodeURIComponent(getSessionId());
      fetch(url).then(function (res) { return res.json(); }).then(function (data) {
        if (!data || data.status !== 'success' || !Array.isArray(data.messages) || !data.messages.length) return;
        thread.innerHTML = '';
        data.messages.forEach(function (m) {
          addMessage(m.content || '', m.direction === 'inbound' ? 'user' : 'bot');
        });
      }).catch(function () { /* degrade silently — the default greeting bubbles stay as-is */ });
    }
    loadHistory();
  }());
  </script>

</body>
</html>
