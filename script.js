// ==========================================================================
// RT CHOCOS MASTER JAVASCRIPT ORCHESTRATOR HUB
// ==========================================================================
(function() {
  const getPrefix = function() {
    return (typeof pathPrefix !== 'undefined' && pathPrefix) ? pathPrefix : '';
  };
  
  const modules = [
    getPrefix() + 'js/core.js',
    getPrefix() + 'js/components.js',
    getPrefix() + 'js/ai-assistant.js'
  ];

  let loaded = 0;
  modules.forEach(function(src) {
    if (document.querySelector('script[src="' + src + '"]')) {
      loaded++;
      if (loaded === modules.length && typeof initApp === 'function') {
        initApp();
      }
      return;
    }
    const s = document.createElement('script');
    s.src = src;
    s.async = false;
    s.onload = function() {
      loaded++;
      if (loaded === modules.length) {
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', function() {
            if (typeof initApp === 'function') initApp();
          });
        } else {
          if (typeof initApp === 'function') initApp();
        }
      }
    };
    document.head.appendChild(s);
  });
})();