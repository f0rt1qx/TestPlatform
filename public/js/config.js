;(function setupBaseUrl() {
  let foundBase = '';
  const scriptTags = document.querySelectorAll('script[src]');

  for (let i = 0; i < scriptTags.length; i++) {
    const scriptSrc = scriptTags[i].src;
    const matchIdx = scriptSrc.indexOf('/public/js/config.js');
    if (matchIdx !== -1) {
      foundBase = scriptSrc.substring(0, matchIdx);
      break;
    }
  }

  try {
    const parsedUrl = new URL(foundBase);
    window.APP_URL = parsedUrl.pathname.replace(/\/$/, '');
  } catch(err) {
    window.APP_URL = foundBase.replace(/\/$/, '');
  }

  window.ANTICHEAT_TAB_SWITCH_WARN = 2;
  window.ANTICHEAT_TAB_SWITCH_MAX  = 5;
  window.ANTICHEAT_RAPID_ANSWER_SEC = 3;
})();