(function () {
  var STORAGE_KEY = 'metplus_cart_view_v2';
  var LABELS = {
    original: 'Оригинал',
    new: 'Новая'
  };
  var ALLOWED = Object.keys(LABELS);

  function normalizeView(view) {
    return ALLOWED.indexOf(view) !== -1 ? view : 'new';
  }

  function getViewFromUrl() {
    return new URL(window.location.href).searchParams.get('cart_view');
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
    return normalizeView(getStoredView() || 'new');
  }

  function applyView(view, options) {
    options = options || {};
    var normalized = normalizeView(view);
    document.documentElement.setAttribute('data-cart-view', normalized);
    storeView(normalized);

    var toggle = document.getElementById('cart-view-switcher-toggle');
    if (toggle) {
      toggle.textContent = 'Корзина: ' + (LABELS[normalized] || LABELS.new);
    }

    var root = document.getElementById('cart-view-switcher');
    if (root) {
      root.querySelectorAll('[data-cart-view]').forEach(function (btn) {
        btn.classList.toggle('is-active', btn.getAttribute('data-cart-view') === normalized);
      });
    }

    if (options.updateUrl) {
      var url = new URL(window.location.href);
      if (normalized === 'new') {
        url.searchParams.delete('cart_view');
      } else {
        url.searchParams.set('cart_view', normalized);
      }
      window.history.replaceState({}, '', url.toString());
    }

    if (options.navigate) {
      var onCartPage = window.location.pathname.indexOf('/cart') === 0;
      if (normalized === 'new' && !onCartPage) {
        window.location.href = '/cart/';
        return;
      }
    }
  }

  window.MetplusCartView = {
    getActiveView: getActiveView,
    applyView: applyView,
    isNew: function () {
      return getActiveView() === 'new';
    }
  };

  applyView(getActiveView(), { updateUrl: false });

  function bindSwitcher() {
    var root = document.getElementById('cart-view-switcher');
    var toggle = document.getElementById('cart-view-switcher-toggle');
    var panel = document.getElementById('cart-view-switcher-panel');
    if (!root || !toggle || !panel || root.getAttribute('data-bound') === '1') {
      return;
    }
    root.setAttribute('data-bound', '1');

    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var open = !panel.hidden;
      panel.hidden = open;
      root.classList.toggle('is-open', !open);
    });

    panel.querySelectorAll('[data-cart-view]').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        applyView(btn.getAttribute('data-cart-view'), { updateUrl: true, navigate: true });
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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindSwitcher);
  } else {
    bindSwitcher();
  }
})();
