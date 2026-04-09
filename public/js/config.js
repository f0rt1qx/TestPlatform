(function() {
  
  
  var scripts = document.querySelectorAll('script[src]');
  var base = '';
  for (var i = 0; i < scripts.length; i++) {
    var src = scripts[i].src;
    var idx = src.indexOf('/public/js/config.js');
    if (idx !== -1) {
      base = src.substring(0, idx);
      break;
    }
  }
  
  try {
    var url = new URL(base);
    window.APP_URL = url.pathname.replace(/\/$/, '');
  } catch(e) {
    window.APP_URL = base.replace(/\/$/, '');
  }
  
  window.ANTICHEAT_TAB_SWITCH_WARN = 2;
  window.ANTICHEAT_TAB_SWITCH_MAX  = 5;
  window.ANTICHEAT_RAPID_ANSWER_SEC = 3;
  
  console.log('[Config] APP_URL =', window.APP_URL);
})();