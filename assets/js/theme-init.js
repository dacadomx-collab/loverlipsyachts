/* =====================================================================
   LOVER LIPS YACHTS — assets/js/theme-init.js
   Anti-Flash Theme Initializer — Must load BLOCKING (no defer/async)
   Runs before first paint to prevent white flash on dark mode reload.
   ===================================================================== */
(function () {
  try {
    var theme = localStorage.getItem('llyCockpitTheme');
    if (theme === 'dark') document.documentElement.dataset.theme = 'dark';
  } catch (e) {}
  try {
    var lang = localStorage.getItem('llyCockpitLang');
    if (lang) document.documentElement.lang = lang;
  } catch (e) {}
})();
