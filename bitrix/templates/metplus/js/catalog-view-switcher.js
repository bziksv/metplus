(function () {
  var STORAGE_KEY = 'metplus_catalog_view';
  var LABELS = {
    original: 'Оригинал',
    concept1: 'Мастерская',
    concept2: 'Витрина'
  };
  var ALLOWED = Object.keys(LABELS);

  function normalizeView(view) {
    return ALLOWED.indexOf(view) !== -1 ? view : 'original';
  }

  function getViewFromUrl() {
    return new URL(window.location.href).searchParams.get('catalog_view');
  }

  function getStoredView() {
    try {
      return localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      return null;
    }
  }

  function storeView(view) {
    try {
      localStorage.setItem(STORAGE_KEY, view);
    } catch (e) {}
  }

  function getActiveView() {
    var fromUrl = getViewFromUrl();
    if (fromUrl) {
      return normalizeView(fromUrl);
    }
    return normalizeView(getStoredView() || 'original');
  }

  function applyView(view, options) {
    options = options || {};
    var normalized = normalizeView(view);
    document.documentElement.setAttribute('data-catalog-view', normalized);
    storeView(normalized);

    var toggle = document.getElementById('catalog-view-switcher-toggle');
    if (toggle) {
      toggle.textContent = 'Каталог: ' + (LABELS[normalized] || LABELS.original);
    }

    var root = document.getElementById('catalog-view-switcher');
    if (root) {
      root.querySelectorAll('[data-catalog-view]').forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-catalog-view') === normalized);
      });
    }

    if (options.updateUrl) {
      var url = new URL(window.location.href);
      if (normalized === 'original') {
        url.searchParams.delete('catalog_view');
      } else {
        url.searchParams.set('catalog_view', normalized);
      }
      window.history.replaceState({}, '', url.toString());
    }
  }

  window.MetplusCatalogView = {
    getActiveView: getActiveView,
    applyView: applyView
  };

  applyView(getActiveView(), { updateUrl: false });

  var root = document.getElementById('catalog-view-switcher');
  var toggle = document.getElementById('catalog-view-switcher-toggle');
  var panel = document.getElementById('catalog-view-switcher-panel');
  if (!root || !toggle || !panel) {
    return;
  }

  toggle.addEventListener('click', function () {
    var open = !panel.hidden;
    panel.hidden = open;
    root.classList.toggle('is-open', !open);
  });

  panel.querySelectorAll('[data-catalog-view]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      applyView(btn.getAttribute('data-catalog-view'), { updateUrl: true });
      panel.hidden = true;
      root.classList.remove('is-open');
    });
  });

  document.addEventListener('click', function (e) {
    if (!root.contains(e.target)) {
      panel.hidden = true;
      root.classList.remove('is-open');
    }
  });
})();
