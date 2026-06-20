/* =====================================================================
   LOVER LIPS YACHTS — assets/js/auth-gate.js
   Blocking script: must load BEFORE first paint (no defer/async), same
   pattern as theme-init.js. Sets data-gate="locked" on <html> so the
   dashboard never flashes into view before the session check resolves.

   NOTE: this is a client-side UX gate, not real access control — anyone
   who opens DevTools can read the credential check in auth.js. For an
   actual access boundary (the dashboard contains confidential business
   data), pair this with server-side HTTP Basic Auth via .htaccess.
   ===================================================================== */
(function () {
  try {
    if (localStorage.getItem('llyDashboardAuth') === 'granted') return;
  } catch (e) {}
  document.documentElement.setAttribute('data-gate', 'locked');
})();
