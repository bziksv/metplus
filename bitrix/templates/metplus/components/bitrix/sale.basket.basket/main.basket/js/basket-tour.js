(function (window, document) {
	'use strict';

	var PAD = 8;
	var AUTO_KEY = 'metplus_basket_tour_auto_v1';
	var booted = false;
	var active = false;
	var closing = false;
	var autoScheduled = false;
	var stepIndex = 0;
	var openedForTour = false;
	var tourCuttingId = null;
	var overlay = null;
	var dim = null;
	var spot = null;
	var card = null;

	function $(sel, root) {
		if (typeof sel !== 'string') {
			return sel;
		}
		return (root || document).querySelector(sel);
	}

	function $all(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function on(el, event, handler) {
		if (!el) {
			return;
		}
		el.addEventListener(event, handler);
	}

	function firstVisible(selectors) {
		for (var i = 0; i < selectors.length; i++) {
			var nodes = $all(selectors[i]);
			for (var j = 0; j < nodes.length; j++) {
				var el = nodes[j];
				if (!el || el.hidden) {
					continue;
				}
				var style = window.getComputedStyle(el);
				if (style.display === 'none' || style.visibility === 'hidden') {
					continue;
				}
				var rect = el.getBoundingClientRect();
				if (rect.width < 2 || rect.height < 2) {
					continue;
				}
				return el;
			}
		}
		return null;
	}

	function ensureCuttingOpen(done) {
		var toggle = firstVisible(['#basket-root [data-entity="cutting-plan-toggle"]']);
		if (!toggle) {
			done(false);
			return;
		}

		var id = String(toggle.getAttribute('data-id') || '');
		var row = document.getElementById('basket-item-' + id + '-cutting');
		var alreadyOpen = !!(row && !row.hidden && row.classList.contains('is-open'));

		tourCuttingId = id;
		if (alreadyOpen) {
			openedForTour = false;
			done(true);
			return;
		}

		openedForTour = true;
		if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.open === 'function') {
			window.MetplusBasketCutting.open(id, true, { skipSave: true, step: 1 });
		} else {
			toggle.click();
		}

		window.setTimeout(function () {
			if (typeof window.metplusSetCuttingWizardStep === 'function') {
				var plan = document.querySelector('#basket-item-' + id + '-cutting [data-entity="cutting-plan"]');
				if (plan) {
					window.metplusSetCuttingWizardStep(window.jQuery ? window.jQuery(plan) : plan, 1);
				}
			}
			done(true);
		}, 280);
	}

	function setWizardStep(step) {
		if (!tourCuttingId) {
			return;
		}
		var plan = document.querySelector('#basket-item-' + tourCuttingId + '-cutting [data-entity="cutting-plan"]');
		if (!plan || typeof window.metplusSetCuttingWizardStep !== 'function') {
			return;
		}
		if (window.jQuery) {
			window.metplusSetCuttingWizardStep(window.jQuery(plan), step);
		}
	}

	function closeTourCutting() {
		if (!openedForTour || !tourCuttingId) {
			return;
		}
		if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.open === 'function') {
			window.MetplusBasketCutting.open(tourCuttingId, false, { skipSave: true, skipUiPersist: true });
		}
		openedForTour = false;
		tourCuttingId = null;
	}

	var STEPS = [
		{
			id: 'welcome',
			title: 'Как пользоваться корзиной и сделать резку товаров?',
			text: 'Короткий интерактивный тур по корзине: количество, сумма, резка и итог. Нажмите «Начать тур», чтобы пройти путь визуально. Закрыть можно в любой момент — попап свернётся в кнопку «?» над таблицей.',
			center: true
		},
		{
			id: 'qty',
			title: 'Количество товара',
			text: 'Меняйте штуки, метры (или м²) и вес кнопками «− / +» или вручную. Поля связаны: изменили одно — пересчитаются остальные.',
			find: function () {
				return firstVisible([
					'#basket-root [data-entity="basket-item-pieces-block"]',
					'#basket-root [data-entity="basket-item-quantity-block"]',
					'#basket-root .cart-table_col-pieces'
				]);
			},
			placement: 'bottom'
		},
		{
			id: 'sum',
			title: 'Сумма строки',
			text: 'Здесь стоимость позиции. Суммы округляются до 0,1 ₽. Если есть наценка за неполную штуку или резку листа — она уже учтена в сумме.',
			find: function () {
				return firstVisible([
					'#basket-root .cart-table_col-sum',
					'#basket-root [id^="basket-item-sum-price-"]'
				]);
			},
			placement: 'left'
		},
		{
			id: 'cut-btn',
			title: 'Кнопка «Хочу порезку»',
			text: 'Если товар режется, под названием появится эта кнопка. Откроется мастер: что режем → как режем → итог. Если кнопки нет — товар отпускается только целыми штуками.',
			find: function () {
				return firstVisible(['#basket-root [data-entity="cutting-plan-toggle"]']);
			},
			placement: 'bottom',
			fallbackTitle: 'Резка недоступна в этой корзине',
			fallbackText: 'Сейчас нет позиций с резкой. Добавьте балку, трубу или лист — и кнопка «Хочу порезку» появится под названием.'
		},
		{
			id: 'wizard',
			title: 'Шаги мастера резки',
			text: 'Три шага: 1) что режем (целые / неполная), 2) тип резки и длины кусков, 3) итоговый расчёт. Можно листать вкладки сверху.',
			needsCutting: true,
			wizardStep: 1,
			find: function () {
				return firstVisible(['#basket-root [data-entity="cutting-wizard-steps"]']);
			},
			placement: 'bottom'
		},
		{
			id: 'target',
			title: 'Что режем',
			text: 'Выберите целые штуки или неполную (если есть остаток в корзине). От этого зависит схема на шаге «Как режем».',
			needsCutting: true,
			wizardStep: 1,
			find: function () {
				return firstVisible(['#basket-root [data-entity="cutting-target"]']);
			},
			placement: 'bottom'
		},
		{
			id: 'cuts',
			title: 'Куски и остаток',
			text: 'На шаге «Как режем» укажите длины кусков через «+» (кратно 0,1 м). Справа подсвечивается остаток — его лучше закрыть последним куском, иначе при переходе дальше он добавится сам.',
			needsCutting: true,
			wizardStep: 2,
			find: function () {
				return firstVisible([
					'#basket-root [data-entity="cutting-part-cuts"]',
					'#basket-root .cutting-part__cuts-wrap',
					'#basket-root [data-entity="cutting-part"]'
				]);
			},
			placement: 'top'
		},
		{
			id: 'totals',
			title: 'Итог заказа',
			text: 'Внизу — стоимость металла, отдельно резка (если есть) и итог с резкой. Когда всё готово — «Оформить заказ».',
			find: function () {
				return firstVisible([
					'#basket-root [data-entity="basket-total-block"]',
					'#basket-root .cart-content_footer'
				]);
			},
			placement: 'top'
		},
		{
			id: 'done',
			title: 'Готово!',
			text: 'Тур можно запустить снова кнопкой «Как пользоваться корзиной…» над таблицей. Удачных заказов!',
			center: true
		}
	];

	function buildOverlay() {
		if (overlay) {
			return;
		}

		overlay = document.createElement('div');
		overlay.className = 'basket-tour-overlay is-interactive';
		overlay.setAttribute('aria-live', 'polite');

		dim = document.createElement('div');
		dim.className = 'basket-tour-dim';

		spot = document.createElement('div');
		spot.className = 'basket-tour-spot';
		spot.hidden = true;

		card = document.createElement('div');
		card.className = 'basket-tour-card';
		card.setAttribute('role', 'dialog');
		card.setAttribute('aria-modal', 'true');
		card.innerHTML =
			'<div class="basket-tour-card__top">' +
				'<div class="basket-tour-card__step"></div>' +
				'<button type="button" class="basket-tour-card__close" aria-label="Закрыть тур">&times;</button>' +
			'</div>' +
			'<h3 class="basket-tour-card__title"></h3>' +
			'<p class="basket-tour-card__text"></p>' +
			'<div class="basket-tour-card__actions">' +
				'<button type="button" class="basket-tour-btn" data-tour-prev>Назад</button>' +
				'<button type="button" class="basket-tour-btn basket-tour-btn--primary" data-tour-next>Далее</button>' +
				'<button type="button" class="basket-tour-btn basket-tour-btn--ghost" data-tour-skip>Выйти</button>' +
			'</div>';

		overlay.appendChild(dim);
		overlay.appendChild(spot);
		overlay.appendChild(card);
		document.body.appendChild(overlay);

		on(card, 'click', function (e) {
			var t = e.target;
			if (!t) {
				return;
			}
			if (t.closest && t.closest('[data-tour-next]')) {
				e.preventDefault();
				go(1);
			} else if (t.closest && t.closest('[data-tour-prev]')) {
				e.preventDefault();
				go(-1);
			} else if (t.closest && (t.closest('[data-tour-skip]') || t.closest('.basket-tour-card__close'))) {
				e.preventDefault();
				stopTour();
			}
		});
	}

	function placeCard(rect, placement) {
		var cw = card.offsetWidth || 360;
		var ch = card.offsetHeight || 180;
		var vw = window.innerWidth;
		var vh = window.innerHeight;
		var left;
		var top;

		if (!rect || placement === 'center') {
			card.classList.add('basket-tour-card--center');
			card.style.left = '';
			card.style.top = '';
			card.style.right = '';
			card.style.bottom = '';
			return;
		}

		card.classList.remove('basket-tour-card--center');

		if (placement === 'bottom') {
			top = rect.bottom + 14;
			left = rect.left + rect.width / 2 - cw / 2;
		} else if (placement === 'top') {
			top = rect.top - ch - 14;
			left = rect.left + rect.width / 2 - cw / 2;
		} else if (placement === 'left') {
			top = rect.top + rect.height / 2 - ch / 2;
			left = rect.left - cw - 14;
		} else {
			top = rect.top + rect.height / 2 - ch / 2;
			left = rect.right + 14;
		}

		left = Math.max(12, Math.min(left, vw - cw - 12));
		top = Math.max(12, Math.min(top, vh - ch - 12));
		card.style.left = left + 'px';
		card.style.top = top + 'px';
		card.style.right = 'auto';
		card.style.bottom = 'auto';
	}

	function highlight(el, placement) {
		if (!el) {
			spot.hidden = true;
			spot.removeAttribute('data-arrow');
			dim.style.clipPath = 'none';
			placeCard(null, 'center');
			return;
		}

		var rect = el.getBoundingClientRect();
		var x = Math.max(0, rect.left - PAD);
		var y = Math.max(0, rect.top - PAD);
		var w = Math.min(window.innerWidth - x, rect.width + PAD * 2);
		var h = Math.min(window.innerHeight - y, rect.height + PAD * 2);

		spot.hidden = false;
		spot.setAttribute('data-arrow', placement === 'center' ? '' : (placement || 'bottom'));
		spot.style.left = x + 'px';
		spot.style.top = y + 'px';
		spot.style.width = w + 'px';
		spot.style.height = h + 'px';

		dim.style.clipPath = 'polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%, 0% 0%, '
			+ x + 'px ' + y + 'px, '
			+ x + 'px ' + (y + h) + 'px, '
			+ (x + w) + 'px ' + (y + h) + 'px, '
			+ (x + w) + 'px ' + y + 'px, '
			+ x + 'px ' + y + 'px)';

		placeCard(rect, placement || 'bottom');
	}

	function renderStep() {
		var step = STEPS[stepIndex];
		if (!step) {
			stopTour();
			return;
		}

		var el = null;
		var title = step.title;
		var text = step.text;

		if (typeof step.find === 'function') {
			el = step.find();
			if (!el && step.fallbackTitle) {
				title = step.fallbackTitle;
				text = step.fallbackText || text;
			}
		}

		card.querySelector('.basket-tour-card__step').textContent = 'Шаг ' + (stepIndex + 1) + ' из ' + STEPS.length;
		card.querySelector('.basket-tour-card__title').textContent = title;
		card.querySelector('.basket-tour-card__text').textContent = text;

		var prevBtn = card.querySelector('[data-tour-prev]');
		var nextBtn = card.querySelector('[data-tour-next]');
		prevBtn.style.display = stepIndex > 0 ? '' : 'none';
		if (stepIndex >= STEPS.length - 1) {
			nextBtn.textContent = 'Готово';
		} else if (stepIndex === 0) {
			nextBtn.textContent = 'Начать тур';
		} else {
			nextBtn.textContent = 'Далее';
		}

		if (el && !step.center) {
			try {
				el.scrollIntoView({ block: 'center', behavior: 'smooth', inline: 'nearest' });
			} catch (err) {}
			window.setTimeout(function () {
				highlight(el, step.placement || 'bottom');
			}, 220);
		} else {
			highlight(null, 'center');
		}
	}

	function prepareAndShow() {
		var step = STEPS[stepIndex];
		if (!step) {
			stopTour();
			return;
		}

		var after = function () {
			if (step.wizardStep) {
				setWizardStep(step.wizardStep);
			}
			window.setTimeout(renderStep, step.wizardStep ? 120 : 0);
		};

		if (step.needsCutting) {
			ensureCuttingOpen(function () {
				after();
			});
			return;
		}

		after();
	}

	function go(delta) {
		if (delta > 0 && stepIndex >= STEPS.length - 1) {
			stopTour({ animateToButton: true });
			return;
		}
		if (delta > 0 && stepIndex === 0) {
			markAutoSeen();
		}
		stepIndex = Math.max(0, Math.min(STEPS.length - 1, stepIndex + delta));
		prepareAndShow();
	}

	function onKey(e) {
		if (!active) {
			return;
		}
		if (e.key === 'Escape') {
			e.preventDefault();
			stopTour();
		} else if (e.key === 'ArrowRight' || e.key === 'Enter') {
			e.preventDefault();
			go(1);
		} else if (e.key === 'ArrowLeft') {
			e.preventDefault();
			go(-1);
		}
	}

	function onResize() {
		if (active) {
			renderStep();
		}
	}

	function markAutoSeen() {
		try {
			window.sessionStorage.setItem(AUTO_KEY, '1');
		} catch (e) {}
	}

	function wasAutoSeen() {
		try {
			return window.sessionStorage.getItem(AUTO_KEY) === '1';
		} catch (e) {
			return false;
		}
	}

	function getTourStartButton() {
		return document.querySelector('#basket-root [data-entity="basket-tour-start"]');
	}

	function pulseTourButton() {
		var btn = getTourStartButton();
		if (!btn) {
			return;
		}
		btn.classList.remove('is-tour-pulse');
		void btn.offsetWidth;
		btn.classList.add('is-tour-pulse');
		window.setTimeout(function () {
			btn.classList.remove('is-tour-pulse');
		}, 1600);
	}

	function resetCardMotion() {
		if (!card) {
			return;
		}
		card.classList.remove('basket-tour-card--shrinking');
		card.style.transition = '';
		card.style.transform = '';
		card.style.opacity = '';
		card.style.transformOrigin = '';
		if (dim) {
			dim.classList.remove('basket-tour-dim--fade');
			dim.style.opacity = '';
			dim.style.transition = '';
		}
		if (spot) {
			spot.style.opacity = '';
			spot.style.transition = '';
		}
	}

	function animateCloseToButton(done) {
		var btn = getTourStartButton();
		if (!card || !btn) {
			done();
			return;
		}

		var from = card.getBoundingClientRect();
		var to = btn.getBoundingClientRect();
		if (from.width < 8 || to.width < 8) {
			done();
			return;
		}

		if (spot) {
			spot.style.transition = 'opacity .25s ease';
			spot.style.opacity = '0';
		}
		if (dim) {
			dim.classList.add('basket-tour-dim--fade');
			dim.style.transition = 'opacity .35s ease';
			dim.style.opacity = '0';
		}

		card.classList.remove('basket-tour-card--center');
		card.classList.add('basket-tour-card--shrinking');
		card.style.left = from.left + 'px';
		card.style.top = from.top + 'px';
		card.style.right = 'auto';
		card.style.bottom = 'auto';
		card.style.width = from.width + 'px';
		card.style.transformOrigin = 'top left';
		card.style.transition = 'transform .45s cubic-bezier(.4,0,.2,1), opacity .45s ease';

		var sx = Math.max(0.08, to.width / from.width);
		var sy = Math.max(0.08, to.height / from.height);
		var tx = to.left - from.left;
		var ty = to.top - from.top;

		window.requestAnimationFrame(function () {
			card.style.transform = 'translate(' + tx + 'px, ' + ty + 'px) scale(' + sx + ', ' + sy + ')';
			card.style.opacity = '0';
		});

		window.setTimeout(function () {
			done();
		}, 460);
	}

	function finishStopTour() {
		active = false;
		closing = false;
		document.documentElement.classList.remove('basket-tour-active');
		document.removeEventListener('keydown', onKey);
		window.removeEventListener('resize', onResize);
		window.removeEventListener('scroll', onResize, true);
		if (overlay) {
			overlay.style.display = 'none';
			if (spot) {
				spot.hidden = true;
			}
			if (dim) {
				dim.style.clipPath = 'none';
			}
		}
		resetCardMotion();
		if (card) {
			card.style.width = '';
			card.style.left = '';
			card.style.top = '';
		}
		closeTourCutting();
		pulseTourButton();
	}

	function startTour() {
		if (active || closing) {
			return;
		}
		buildOverlay();
		resetCardMotion();
		active = true;
		stepIndex = 0;
		document.documentElement.classList.add('basket-tour-active');
		overlay.style.display = '';
		if (dim) {
			dim.style.opacity = '';
		}
		if (spot) {
			spot.style.opacity = '';
		}
		document.addEventListener('keydown', onKey);
		window.addEventListener('resize', onResize);
		window.addEventListener('scroll', onResize, true);
		prepareAndShow();
	}

	function stopTour(opts) {
		opts = opts || {};
		if (!active || closing) {
			return;
		}
		markAutoSeen();
		closing = true;
		active = false;
		document.removeEventListener('keydown', onKey);
		window.removeEventListener('resize', onResize);
		window.removeEventListener('scroll', onResize, true);

		var finish = function () {
			finishStopTour();
		};

		if (opts.animateToButton !== false) {
			animateCloseToButton(finish);
			return;
		}

		finish();
	}

	function maybeAutoStartTour() {
		if (wasAutoSeen() || active || closing || autoScheduled) {
			return;
		}
		var root = document.getElementById('basket-root');
		if (!root) {
			return;
		}
		// Только страница /cart или полноценный корень корзины
		var onCartPage = root.classList.contains('basket-root--page')
			|| root.getAttribute('data-cart-mode') === 'page'
			|| (window.location.pathname || '').indexOf('/cart') === 0;
		if (!onCartPage) {
			return;
		}
		if (!getTourStartButton()) {
			injectButton();
		}
		autoScheduled = true;
		window.setTimeout(function () {
			if (wasAutoSeen() || active || closing || !document.getElementById('basket-root')) {
				return;
			}
			startTour();
		}, 450);
	}

	function injectButton() {
		var root = document.getElementById('basket-root');
		if (!root) {
			return;
		}

		var body = root.querySelector('.cart-content_body');
		if (!body) {
			return;
		}

		var toolbar = root.querySelector('[data-entity="basket-toolbar"]');
		if (!toolbar) {
			toolbar = document.createElement('div');
			toolbar.className = 'basket-toolbar';
			toolbar.setAttribute('data-entity', 'basket-toolbar');
			body.insertBefore(toolbar, body.firstChild);
		}

		if (!toolbar.querySelector('[data-entity="basket-tour-start"]')) {
			var tourBtn = document.createElement('button');
			tourBtn.type = 'button';
			tourBtn.className = 'basket-tour-start';
			tourBtn.setAttribute('data-entity', 'basket-tour-start');
			tourBtn.innerHTML =
				'<span class="basket-tour-start__icon" aria-hidden="true">?</span>' +
				'<span>Как пользоваться корзиной и сделать резку товаров?</span>';
			toolbar.appendChild(tourBtn);
		}

		if (!toolbar.querySelector('[data-entity="basket-clear"]')) {
			var clearBtn = document.createElement('button');
			clearBtn.type = 'button';
			clearBtn.className = 'basket-clear-btn';
			clearBtn.setAttribute('data-entity', 'basket-clear');
			clearBtn.textContent = 'Очистить корзину';
			toolbar.appendChild(clearBtn);
		}
	}

	function clearBasketLocalState() {
		try {
			window.localStorage.removeItem('metplus_cutting_panel_open');
			window.localStorage.removeItem('metplus_cutting_wizard_step');
		} catch (e) {}
	}

	function refreshCartChrome() {
		if (!window.jQuery) {
			window.location.reload();
			return;
		}
		var $ = window.jQuery;
		$.get('/ajax/', { component: 'cart_small' }).done(function (cart) {
			$('.head-cart').html(cart);
		});
	}

	function clearBasket(btn) {
		if (!window.confirm('Очистить корзину от всех товаров?')) {
			return;
		}

		if (btn) {
			btn.disabled = true;
		}

		var finish = function (ok, message) {
			if (btn) {
				btn.disabled = false;
			}
			if (!ok) {
				window.alert(message || 'Не удалось очистить корзину');
				return;
			}
			clearBasketLocalState();
			var root = document.getElementById('basket-root');
			var mode = root ? root.getAttribute('data-cart-mode') : 'page';
			if (mode === 'page' || window.location.pathname.indexOf('/cart') === 0) {
				window.location.reload();
				return;
			}
			refreshCartChrome();
			if (window.jQuery) {
				window.jQuery.get('/ajax/', { component: 'cart' }).done(function (html) {
					var $box = window.jQuery('.cart-content > .cart-content_first');
					if ($box.length) {
						$box.html(html);
					} else {
						window.location.reload();
					}
				}).fail(function () {
					window.location.reload();
				});
			} else {
				window.location.reload();
			}
		};

		var url = '/ajax/clear_cart.php';
		if (window.fetch) {
			window.fetch(url, {
				method: 'GET',
				credentials: 'same-origin',
				headers: { 'Accept': 'application/json' }
			}).then(function (r) {
				return r.json();
			}).then(function (data) {
				finish(!!(data && data.success), data && data.error);
			}).catch(function () {
				finish(false, 'Не удалось очистить корзину');
			});
			return;
		}

		var xhr = new XMLHttpRequest();
		xhr.open('GET', url, true);
		xhr.setRequestHeader('Accept', 'application/json');
		xhr.onreadystatechange = function () {
			if (xhr.readyState !== 4) {
				return;
			}
			try {
				var data = JSON.parse(xhr.responseText || '{}');
				finish(!!data.success, data.error);
			} catch (e) {
				finish(false, 'Не удалось очистить корзину');
			}
		};
		xhr.send();
	}

	function onDocClick(e) {
		var t = e.target && e.target.closest ? e.target.closest('#basket-root [data-entity]') : null;
		if (!t) {
			return;
		}
		var entity = t.getAttribute('data-entity');
		if (entity === 'basket-tour-start' || (t.closest && t.closest('[data-entity="basket-tour-start"]'))) {
			var tourBtn = t.closest ? t.closest('[data-entity="basket-tour-start"]') : t;
			if (tourBtn) {
				e.preventDefault();
				startTour();
			}
			return;
		}
		if (entity === 'basket-clear' || (t.closest && t.closest('[data-entity="basket-clear"]'))) {
			var clearBtn = t.closest ? t.closest('[data-entity="basket-clear"]') : t;
			if (clearBtn) {
				e.preventDefault();
				clearBasket(clearBtn);
			}
		}
	}

	function boot() {
		if (booted) {
			return;
		}
		booted = true;
		document.addEventListener('click', onDocClick);
		injectButton();
		maybeAutoStartTour();

		var root = document.getElementById('basket-root');
		if (root && window.MutationObserver) {
			var t = null;
			new MutationObserver(function () {
				window.clearTimeout(t);
				t = window.setTimeout(function () {
					injectButton();
					maybeAutoStartTour();
				}, 200);
			}).observe(root, { childList: true, subtree: true });
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	window.MetplusBasketTour = {
		start: startTour,
		stop: function () {
			stopTour({ animateToButton: true });
		},
		autoStart: maybeAutoStartTour
	};
})(window, document);
