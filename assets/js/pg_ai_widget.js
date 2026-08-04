/**
 * LOVER LIPS YACHTS — assets/js/pg_ai_widget.js
 * PG-AI Pink Glove AI — public site chat widget connector. Talks to
 * api/public/ai_widget_gateway.php, which normalizes to the same OCMC
 * contract and omnichannel_* tables WhatsApp writes to (see
 * core/OmnichannelRepository.php) — one shared conversation history
 * per contact regardless of channel.
 *
 * Reusable across any public page that ships the
 * #lly-ai-widget-fab/#lly-ai-widget-panel markup (see book.php) — the
 * gateway URL is read from window.PGAI_WIDGET_GATEWAY_URL (set by the
 * host page from its own dynamic base-URL logic, e.g. book.php's
 * $baseRootUrl) so this file never hardcodes a host/path itself; a
 * page that omits it gets the relative default below.
 */
(function () {
  var fab      = document.getElementById('lly-ai-widget-fab');
  var panel    = document.getElementById('lly-ai-widget-panel');
  var closeBtn = document.getElementById('lly-ai-widget-close');
  var form     = document.getElementById('lly-ai-widget-form');
  var input    = document.getElementById('lly-ai-widget-input');
  var sendBtn  = document.getElementById('lly-ai-widget-send');
  var thread   = document.getElementById('lly-ai-widget-messages');
  if (!fab || !panel || !form) return;

  var gatewayUrl = window.PGAI_WIDGET_GATEWAY_URL || 'api/public/ai_widget_gateway.php';

  /* Session id persists per browser tab so a refresh keeps context. */
  function getSessionId() {
    var key = 'lly_ai_widget_session';
    var id = sessionStorage.getItem(key);
    if (!id) {
      id = (window.crypto && crypto.randomUUID) ? crypto.randomUUID().replace(/-/g, '') : ('s' + Date.now() + Math.random().toString(36).slice(2));
      sessionStorage.setItem(key, id);
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
  /* A click listener on #btn-en/#btn-es would race main.js's own
     listener (whichever attaches first "wins" the read), since both
     fire off the same click. Watching the attribute main.js actually
     writes is race-proof regardless of script/listener order. */
  new MutationObserver(refreshPlaceholder).observe(document.body, {
    attributes: true,
    attributeFilter: ['data-active-lang'],
  });

  function addMessage(text, who) {
    var el = document.createElement('div');
    el.className = 'lly-ai-widget-msg lly-ai-widget-msg--' + who;
    el.textContent = text;
    thread.appendChild(el);
    thread.scrollTop = thread.scrollHeight;
  }

  function openPanel() {
    panel.classList.add('lly-ai-widget-panel--open');
    fab.classList.add('lly-ai-widget-fab--open');
    fab.setAttribute('aria-expanded', 'true');
    input.focus();
  }
  function closePanel() {
    panel.classList.remove('lly-ai-widget-panel--open');
    fab.classList.remove('lly-ai-widget-fab--open');
    fab.setAttribute('aria-expanded', 'false');
  }

  fab.addEventListener('click', function () {
    panel.classList.contains('lly-ai-widget-panel--open') ? closePanel() : openPanel();
  });
  closeBtn.addEventListener('click', closePanel);

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var text = input.value.trim();
    if (text === '') return;

    addMessage(text, 'user');
    input.value = '';
    sendBtn.disabled = true;

    fetch(gatewayUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, session_id: getSessionId(), lang: currentLang() })
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      addMessage(data && data.reply ? data.reply : (currentLang() === 'es' ? 'Ocurrió un error, intenta de nuevo.' : 'Something went wrong, please try again.'), 'bot');
    })
    .catch(function () {
      addMessage(currentLang() === 'es' ? 'Error de red — revisa tu conexión.' : 'Network error — check your connection.', 'bot');
    })
    .then(function () {
      sendBtn.disabled = false;
      input.focus();
    });
  });
}());
