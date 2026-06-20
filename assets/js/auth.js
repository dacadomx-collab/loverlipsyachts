/* =====================================================================
   LOVER LIPS YACHTS — assets/js/auth.js
   Submits the login form to api/login.php and reacts to the result.

   NOTE: sent as FormData, not raw JSON — api/login.php reads $_POST
   directly, which only populates from form-encoded/multipart bodies,
   not from an application/json request body.

   This script is UX only, not the security boundary — index.php's
   server-side session/cookie check is what actually protects the
   dashboard. A failed or skipped fetch here just means the visitor
   keeps seeing the login screen; it never reveals dashboard content.
   ===================================================================== */
(function () {

  function setError(errorBox, visible) {
    if (!errorBox) return;
    errorBox.classList.toggle('visible', visible);
  }

  function handleLoginSubmit(event) {
    event.preventDefault();

    var form      = event.target;
    var errorBox  = document.getElementById('login-error');
    var submitBtn = document.getElementById('login-submit');

    setError(errorBox, false);
    if (submitBtn) submitBtn.disabled = true;

    fetch('api/login.php', {
      method: 'POST',
      body: new FormData(form)
    })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        if (result && result.status === 'success') {
          window.location.reload();
          return;
        }
        setError(errorBox, true);
        if (submitBtn) submitBtn.disabled = false;
      })
      .catch(function () {
        setError(errorBox, true);
        if (submitBtn) submitBtn.disabled = false;
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('login-form');
    if (form) form.addEventListener('submit', handleLoginSubmit);
  });

})();
