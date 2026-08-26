function is_mobile() {
  return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent))
}
jQuery(document).ready(function($) {
  if (!is_mobile()) {
    $('.wrapper-loader').fadeOut(300);
  } else {
    $('.wrapper-loader').fadeOut(10);
  }
  if (!is_mobile()) {
    $('.dropdown-content').addClass('is-animation');
  }
  $(".fixed-menu_hamburger").on("click", function() {
    $(this).toggleClass('is-active');
    $('.head-nav').slideToggle(100);
    if (is_mobile()) {
      $('html').toggleClass('is-hidden');
    }
  });
  $(".tablet-hamburger").on("click", function() {
    $(this).toggleClass('is-active');
    $('.head-nav').slideToggle(100);
    if (is_mobile()) {
      $('html').toggleClass('is-hidden');
    }
  });
  $(window).resize(function() {
    if ($(window).width() > 991) {
      $('.head-nav').removeAttr('style');
      $(".hamburger").removeClass('is-active');
      $('html').removeClass('is-hidden');
      $('.catalog-menu').removeAttr('style');
      $('.catalog_sidebar-title').removeClass('is-active')
    }
  });
  $('.modal-city_list-unstyled li').on('click', function() {
    var text = $(this).text();
    $('.select-city_field').text(text);
    $('#citySelect').modal('hide');
  });
  if (!is_mobile()) {
    $('.text-section').parallax();
  }
  if (is_mobile()) {
    $('.head-menu_catalog-item > a').on('click', function() {
      if ($(this).siblings('.dropdown-content').length) {
        $(this).closest('.head-menu_catalog-item').toggleClass('is-active');
        return false;
      }
    });
    $('.dropdown-menu_item >a').on('click', function() {
      if ($(this).closest('.dropdown-menu_item').find('.dropdown-submenu').length) {
        var active = false;
        if ($(this).closest('.dropdown-menu_item').hasClass('is-active')) active = true;
        $('.dropdown-menu_item').removeClass('is-active');
        if (!active) $(this).closest('.dropdown-menu_item').toggleClass('is-active');
        return false;
      }
    });
    $('.fixed-panel_catalog-btn').on('click', function() {
      if ($(this).siblings('.dropdown-content').length) {
        $(this).closest('.fixed-menu_catalog').toggleClass('is-active');
        $('html').toggleClass('is-hidden');
        return false;
      }
    });
  }
  if (!is_mobile()) {
    /** Как в miinox: панель каталога в пределах экрана после skew */
    function menuAlignCatalogPanel($wrap) {
      var $root = $wrap.children('.dropdown-content');
      if (!$root.length) {
        return;
      }
      $root[0].style.setProperty('--menu-nudge-x', '0px');
      requestAnimationFrame(function() {
        var pad = 16;
        var maxNudge = 200;
        var rootEl = $root[0];
        var rootRect = rootEl.getBoundingClientRect();
        var nudge = 0;

        // уехала влево за край
        if (rootRect.left < pad) {
          nudge = pad - rootRect.left;
        }

        // уехала вправо (с запасом под L3)
        var l3Reserve = 280;
        var overflowRight = rootRect.right + l3Reserve + nudge - (window.innerWidth - pad);
        if (overflowRight > 0) {
          nudge -= Math.min(overflowRight, maxNudge);
        }

        if (Math.abs(nudge) > 0.5) {
          rootEl.style.setProperty('--menu-nudge-x', Math.round(nudge) + 'px');
        }
      });
    }

    $(document).on('mouseenter', '.head-menu_catalog-item, .fixed-menu_catalog', function() {
      var $wrap = $(this);
      $wrap.addClass('is-hover');
      // после visibility:visible — пересчитать, если обрезало слева
      requestAnimationFrame(function() {
        menuAlignCatalogPanel($wrap);
        requestAnimationFrame(function() {
          menuAlignCatalogPanel($wrap);
        });
      });
    });

    $(document).on('mouseleave', '.head-menu_catalog-item, .fixed-menu_catalog', function() {
      $(this).removeClass('is-hover');
    });

    $('.dropdown-menu_item').hover(function() {
      var height1 = $(this).find('.dropdown-submenu').outerHeight();
      var height2 = $(this).find('.dropdown-submenu_img').outerHeight();
      if (height1 < height2) {
        $(this).find('.dropdown-submenu').outerHeight(height2);
      }
    });
  }

  function cartFly($el)
  {
    let $hc = $(".head-cart");

    $el.clone().css({
      'position': 'absolute',
      'z-index': '1000',
      'width': '57px',
      top: $el.offset().top,
      left: $el.offset().left
    }).appendTo("body").animate({
      opacity: 0.05,
      left: $hc.offset()['left'],
      top: $hc.offset()['top'],
      width: 20
    }, 700, function() {
      $(this).remove();
    });
  }

  $(".product-table").on("click", ".add-to-cart-action", function() {
    let $self = $(this);
    let id = $self.attr('id');
    let $row = $self.closest('tr');
    let quantity = resolveCartQuantity($row);

    if (quantity === null) {
      if (isWeightFrom500Row($row) && getOrderMode($row) === 'bulk') {
        alert('Минимальный вес заказа — ' + getMinBulkWeight($row) + ' кг');
      } else if (isWeightFrom500Row($row)) {
        alert('Укажите количество целых штук. Заказ по весу доступен от ' + getMinBulkWeight($row) + ' кг');
      } else {
        alert('Укажите корректное количество товара');
      }
      return false;
    }

    $.get("/ajax/", {
      component: "add_cart",
      id : id,
      quantity : quantity,
    }).done(function(data) {
      if (!data || data.success === false) {
        alert((data && data.error) ? data.error : 'Не удалось добавить товар в корзину');
        return;
      }

      cartFly($self);

      $.get("/ajax/", { component: "cart_small" }).done(function(cart) {
        $('.head-cart').html(cart);
      });
    }).fail(function() {
      alert('Не удалось добавить товар в корзину');
    });

    return false;
  });



  $(".product-item_buy-btn").on("click", function() {
    $(this).clone().css({
      'position': 'absolute',
      'z-index': '1000',
      'width': '120px',
      'minWidth': 'auto',
      top: $(this).offset().top,
      left: $(this).offset().left
    }).appendTo("body").animate({
      opacity: 0.05,
      left: $(".head-cart").offset()['left'],
      top: $(".head-cart").offset()['top'],
      width: 20
    }, 700, function() {
      $(this).remove();
    });
    return false;
  });

  $('.services-detailed_hide-table').on('click', function(){
   if ($(this).html() == 'Скрыть таблицу') {
      $('.services-detailed_table').slideUp(150);
      $(this).text('Показать таблицу');
    } else {
      $('.services-detailed_table').slideDown(150);
      $(this).text('Скрыть таблицу');
    }
    $(this).toggleClass('is-active');
  });

  function lazyLoad($content) {
    $content.find('img[data-src], source[data-src], audio[data-src], iframe[data-src]').each(function() {
      $(this).attr('src', $(this).data('src'));
      $(this).removeAttr('data-src');
      if ($(this).is('source')) {
        $(this).closest('video').get(0).load();
      }
    });
  }
  lazyLoad($('body'));
  $('.our-partners_slider').slick({
    slidesToShow: 5,
    slidesToScroll: 1,
    focusOnSelect: true,
    dots: true,
    responsive: [{
      breakpoint: 1200,
      settings: {
        slidesToShow: 4,
        slidesToScroll: 1,
      }
    }, {
      breakpoint: 992,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 1,
      }
    }, {
      breakpoint: 767,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 1,
      }
    }, {
      breakpoint: 400,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 6000,
      }
    }, ]
  });
  $('.advantages-slider').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    dots: true,
    appendDots: $('.js-dots'),
    autoplay: true,
    autoplaySpeed: 6000
  });
  $('.main-slider').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    dots: true,
    autoplay: true,
    autoplaySpeed: 6000
  });
  // Старый слайдер (фон + текст): меняем background секции
  $('.main-slider:not(.main-slider--picture)').on('beforeChange', function(event, slick, currentSlide, nextSlide){
    var currentSlide = $(slick.$slides.get(nextSlide));
    var currentImage = currentSlide.find('.main-slide').data('background');
    if (currentImage) {
      currentSlide.closest('.main-section').css('background','url('+ currentImage +') no-repeat center top');
    }
  });
  // Картиночный слайдер: пересчёт ширины после загрузки/resize (без «пляски»)
  var $pictureSlider = $('.main-slider--picture');
  if ($pictureSlider.length) {
    var refreshPictureSlider = function() {
      $pictureSlider.slick('setPosition');
    };
    $pictureSlider.find('img').on('load', refreshPictureSlider);
    $(window).on('resize orientationchange', refreshPictureSlider);
  }
  $('.review_mobile-slider').slick({
    dots: true,
    infinite: false,
    responsive: [{
      breakpoint: 9999,
      settings: "unslick"
    }, {
      breakpoint: 767,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 3,
        autoplay: true,
        autoplaySpeed: 7000,
      }
    }, {
      breakpoint: 575,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2,
      }
    }, {
      breakpoint: 400,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    }, ]
  });
  $('.wrapper_partner-item').slick({
    dots: true,
    responsive: [{
      breakpoint: 9999,
      settings: "unslick"
    }, {
      breakpoint: 767,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2,
        autoplay: true,
        autoplaySpeed: 7000,
      }
    }, {
      breakpoint: 575,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2,
      }
    }, {
      breakpoint: 400,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1,
      }
    }, ]
  });
  $(".wrapper_select-office .js-select").on("change", function() {
    var number = $(this).find('option:selected').index();
    
    $(this).closest('.tab-item').find('.contact-section-desc').removeClass('is-active').eq(number).addClass('is-active');
    $(this).closest('.container').find('.contact_right-column .contact-img.is-visible img.contact-section-desc').removeClass('is-active').eq(number).addClass('is-active');
  });

  $(".map-contact_box .js-select").on("change", function() {
    var number = $(this).find('option:selected').val();
    $(this).closest('.map-contact_box').find('.map-contact_list').fadeOut(1);
    $(this).closest('.map-contact_box').find('[data-id=' + number + ']').fadeIn(1);
  });

  if (!is_mobile()) {
    if ($('.digit-list').length) {
      var show = true;
      $(window).on("scroll load resize", function() {
        if (!show) return false;
        var w_top = $(window).scrollTop();
        var e_top = $('.digit-section').offset().top;
        if (w_top + 400 >= e_top) {
          $('.digit-list_item').each(function(index){
             var jthis = $(this);
             setInterval(function(){
               jthis.removeClass('fadein');
             },700*index);
           });
           $('.digit-item_circle').each(function(index){
            var jthis = $(this);
            setTimeout(function() {
               setInterval(function(){
                 jthis.addClass('anim-digit');
               },700*index);
            }, 3000);
              setInterval(function(){
                 jthis.spincrement({
                  from: 0,
                  // to:false,
                  decimalPlaces: 0,
                  decimalPoint:'.',
                  thousandSeparator:',',
                  duration: 3000,// ms; TOTAL length animation
                  leeway: 50,// percent of duraion
                  easing:'spincrementEasing',
                  // fade:true,
                  complete: true
                  });
               },700*index);
            setInterval(function(){
               jthis.addClass('fade');
             },700*index);
          });
           setTimeout(function() {
           $('.digit-list').removeClass('off');
            }, 3000);
          show = false;

      }
    });
  }
}
  if (is_mobile()) {
    $('.digit-list_item').removeClass('fadein')
  }
  $('.history-company_slider').slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    arrows: false,
    // autoplay: true,
    autoplaySpeed: 10000
  });
  $('.history-company_slider').on('afterChange', function(event, slick, i) {
    var item = $('.history-company_slider').slick('slickCurrentSlide')
    $(".year-slider li").removeClass('is-active');
    $(".year-slider li").eq(item).addClass('is-active');
  });
  $(".year-slider li").click(function(e) {
    $(".year-slider li").removeClass('is-active');
    $(this).addClass('is-active');
    var slide = $(this).data('type');
    $('.history-company_slider').slick('slickGoTo', slide);
  });
  $('.history-company_slide-nav .slide-next').on('click', function() {
    $('.history-company_slider').slick("slickNext")
  });
  $('.history-company_slide-nav .slide-prev').on('click', function() {
    $('.history-company_slider').slick("slickPrev")
  });
  /*******COUNTER*********/
  $('.wrapper-counter-btn').each(function() {
    $(this).find('.product-count').on('input', function() {
      var rep = (/^0/);
      var value = $(this).val();
      if (rep.test(value)) {
        value = value.replace(rep, '');
        $(this).val(value);
      }
      var value2 = $(this).val();
      var rep2 = /[a-zA-Zа-яА-Я]/;
      if (rep2.test(value)) {
        value2 = value2.replace(rep, '');
        $(this).val(value2);
      }
      if ($(this).val() == '') {
        $(this).val(0);
      }
      var msg = $(this).val();
    });
  });
  $('.wrapper-counter-btn').each(function() {
    $(this).find('.counter-back').on("click", function(e) {
      var valPlus = $(this).siblings('.product-count').val();
      var result = parseInt(valPlus) - 1;
      if (result >= 1) {
        $(this).siblings('.product-count').val(result);
      }
      return false;
    });
  });
  $('.wrapper-counter-btn').each(function() {
    $(this).find('.counter-forward').on("click", function(e) {
      var valPlus = $(this).siblings('.product-count').val();
      var result = parseInt(valPlus) + 1;
      if (result >= 1) {
        $(this).siblings('.product-count').val(result);
      }
      return false;
    });
  });
  $(".fancybox").fancybox({
    afterLoad: function(instance, current) {
      if (!is_mobile()) {
        $('.fixed-menu').addClass('is-overflow');
        $('.scroll-to-top').addClass('is-hidden');
      }
    },
    afterClose: function(instance, current) {
      if (!is_mobile()) {
        $('.fixed-menu').removeClass('is-overflow');
        $('.scroll-to-top').removeClass('is-hidden');
      }
    }
  });

  // Резка: клик «Хочу порезку» (footer — после jQuery; inline-скрипт корзины может выполниться раньше)
  var CUTTING_WIZARD_STEP_KEY = 'metplus_cutting_wizard_step';

  function metplusReadCuttingWizardStepMap() {
    try {
      var raw = window.localStorage.getItem(CUTTING_WIZARD_STEP_KEY);
      var map = raw ? JSON.parse(raw) : {};
      return map && typeof map === 'object' ? map : {};
    } catch (e) {
      return {};
    }
  }

  function metplusRememberCuttingWizardStep(id, step) {
    if (!id) {
      return;
    }
    step = parseInt(step, 10) || 1;
    if (step < 1) step = 1;
    if (step > 3) step = 3;
    if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.rememberStep === 'function') {
      window.MetplusBasketCutting.rememberStep(id, step);
      return;
    }
    try {
      var map = metplusReadCuttingWizardStepMap();
      map[String(id)] = step;
      window.localStorage.setItem(CUTTING_WIZARD_STEP_KEY, JSON.stringify(map));
    } catch (e) {}
  }

  function metplusGetStoredCuttingWizardStep(id) {
    if (!id) {
      return 0;
    }
    if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.getStoredStep === 'function') {
      return parseInt(window.MetplusBasketCutting.getStoredStep(id), 10) || 0;
    }
    var map = metplusReadCuttingWizardStepMap();
    var step = parseInt(map[String(id)], 10) || 0;
    return (step >= 1 && step <= 3) ? step : 0;
  }

  function metplusSetCuttingWizardStep($plan, step) {
    step = parseInt(step, 10) || 1;
    if (step < 1) step = 1;
    if (step > 3) step = 3;
    $plan.attr('data-wizard-step', String(step));
    $plan.find('[data-entity="cutting-wizard-panel"]').each(function() {
      var panelStep = String($(this).attr('data-wizard-panel') || '');
      this.hidden = panelStep !== String(step);
    });
    $plan.find('[data-wizard-tab]').each(function() {
      var t = parseInt($(this).attr('data-wizard-tab'), 10) || 0;
      $(this).toggleClass('is-active', t === step);
      $(this).toggleClass('is-done', t < step);
    });
    var $cost = $plan.find('[data-entity="cutting-summary-cost"]').first();
    $plan.find('[data-entity="cutting-summary-cost-copy"]').text($cost.text() || '0 ₽');
    var id = $plan.attr('data-id') || $plan.data('id');
    metplusRememberCuttingWizardStep(id, step);
  }
  window.metplusSetCuttingWizardStep = metplusSetCuttingWizardStep;
  window.metplusGetStoredCuttingWizardStep = metplusGetStoredCuttingWizardStep;

  $(document).on('click.metplusCuttingToggle', '#basket-root [data-entity="cutting-plan-toggle"]', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var id = this.getAttribute('data-id');
    if (!id) {
      return false;
    }
    var $row = $('#basket-item-' + id + '-cutting');
    if (!$row.length) {
      return false;
    }
    var isOpen = false;
    if ($row[0] && !$row[0].hidden && $row.hasClass('is-open')) {
      isOpen = true;
    }
    if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.open === 'function') {
      window.MetplusBasketCutting.open(id, !isOpen);
      if (!isOpen) {
        var $plan = $row.find('[data-entity="cutting-plan"]');
        var hasPlan = String($plan.find('[data-entity="cutting-parts"]').attr('data-plan') || '').trim() !== '';
        var savedStep = metplusGetStoredCuttingWizardStep(id);
        var openStep = (savedStep >= 1 && savedStep <= 3) ? savedStep : (hasPlan ? 3 : 1);
        metplusSetCuttingWizardStep($plan, openStep);
      }
      return false;
    }
    // fallback без полного модуля резки
    var $btn = $(this);
    if (!isOpen) {
      $row.prop('hidden', false).addClass('is-open');
      $row.find('[data-entity="cutting-plan"]').attr('data-enabled', 'Y').addClass('is-open');
      $btn.addClass('is-active').attr('aria-expanded', 'true').text('Изменить резку');
      metplusSetCuttingWizardStep($row.find('[data-entity="cutting-plan"]'), 1);
    } else {
      $row.prop('hidden', true).removeClass('is-open');
      $row.find('[data-entity="cutting-plan"]').removeClass('is-open');
      $btn.attr('aria-expanded', 'false');
    }
    return false;
  });

  $(document).on('click.metplusCuttingWizard', '#basket-root [data-entity="cutting-wizard-next"], #basket-root [data-entity="cutting-wizard-back"]', function(e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    var toStep = parseInt($(this).attr('data-to-step'), 10) || 1;
    var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
    if (!$plan.length) {
      return false;
    }

    var fromStep = parseInt($plan.attr('data-wizard-step'), 10) || 1;

    // шаг 1 → 2: партии = только выбранная цель (не добавлять вторую к дефолтной целой)
    if (toStep === 2 && $(this).is('[data-entity="cutting-wizard-next"]')) {
      if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.syncPartsToSelectedTarget === 'function') {
        window.MetplusBasketCutting.syncPartsToSelectedTarget(id);
        if (typeof window.MetplusBasketCutting.refreshPlan === 'function') {
          window.MetplusBasketCutting.refreshPlan(id);
        }
      }
    }

    // уход с «Как режем» / на «Итого»: закрыть остатки последним куском
    if (
      (fromStep === 2 || toStep === 3)
      && window.MetplusBasketCutting
      && typeof window.MetplusBasketCutting.autofillRemainders === 'function'
    ) {
      window.MetplusBasketCutting.autofillRemainders(id);
    }

    metplusSetCuttingWizardStep($plan, toStep);
    if (toStep === 3 && window.MetplusBasketCutting && typeof window.MetplusBasketCutting.refreshAll === 'function') {
      window.MetplusBasketCutting.refreshAll();
    }
    return false;
  });

  $(document).on('click.metplusCuttingWizard', '#basket-root [data-wizard-tab]', function(e) {
    e.preventDefault();
    var $plan = $(this).closest('[data-entity="cutting-plan"]');
    var step = parseInt($(this).attr('data-wizard-tab'), 10) || 1;
    var fromStep = parseInt($plan.attr('data-wizard-step'), 10) || 1;
    var id = $plan.attr('data-id') || $plan.data('id');
    if (
      id
      && (fromStep === 2 || step === 3)
      && window.MetplusBasketCutting
      && typeof window.MetplusBasketCutting.autofillRemainders === 'function'
    ) {
      window.MetplusBasketCutting.autofillRemainders(id);
    }
    metplusSetCuttingWizardStep($plan, step);
    return false;
  });

  $('.head-cart').on('click', 'a', function() {
    var cartView = (window.MetplusCartView && window.MetplusCartView.getActiveView)
      ? window.MetplusCartView.getActiveView()
      : 'new';

    // Новая корзина — страница /cart/
    if (cartView === 'new') {
      window.location.href = '/cart/';
      return false;
    }

    $.get("/ajax/", { component: "cart" }).done(function(data) {
      $('.cart-content > .cart-content_first').html(data);
      $('.cart-content').addClass('is-open');
      if (!is_mobile()) {
        $('html').addClass('is-hidden');
      }
      if (window.MetplusBasketCutting && typeof window.MetplusBasketCutting.refreshAll === 'function') {
        window.MetplusBasketCutting.refreshAll();
      }
    });

    return false;
  });

  function initTelInputMask(context) {
    var $scope = context ? $(context) : $(document);
    $scope.find('input[type="tel"]').each(function() {
      var $input = $(this);
      if ($input.data('inputmask')) {
        $input.inputmask('remove');
      }
      $input.inputmask('+7 (999) 999 99 99', {
        clearIncomplete: true,
        showMaskOnHover: false,
      });
    });
  }

  initTelInputMask();

  function normalizeRuPhoneDigits(value) {
    var digits = String(value || '').replace(/\D+/g, '');
    if (digits.length === 11 && digits.charAt(0) === '8') {
      digits = '7' + digits.slice(1);
    }
    if (digits.length === 10) {
      digits = '7' + digits;
    }
    return digits;
  }

  function isValidRuPhoneDigits(digits) {
    return /^7\d{10}$/.test(digits);
  }

  function isRuPhoneComplete(value) {
    return isValidRuPhoneDigits(normalizeRuPhoneDigits(value));
  }

  $('.cart-content').on('submit', '.checkout-form', function(e) {
    var $form = $(this);
    var hasPhoneError = false;

    $form.find('input[type="tel"]').each(function() {
      var $input = $(this);
      var complete = this.inputmask ? this.inputmask.isComplete() : isRuPhoneComplete($input.val());
      if (!complete) {
        hasPhoneError = true;
        $input.addClass('is-invalid');
      } else {
        $input.removeClass('is-invalid');
      }
    });

    if (hasPhoneError) {
      e.preventDefault();
      if (!$form.find('.checkout-form__phone-error').length) {
        $form.find('.checkout-form__consent').before(
          '<div class="checkout-form__phone-error">Укажите корректный номер телефона</div>'
        );
      }
      return false;
    }

    $form.find('.checkout-form__phone-error').remove();
    return true;
  });

  $('.cart-content').on('input', '.checkout-form input[type="tel"]', function() {
    $(this).removeClass('is-invalid');
    $(this).closest('.checkout-form').find('.checkout-form__phone-error').remove();
  });

  function openAuthModal(options) {
    options = options || {};
    var $modal = $('#authRequired');
    if (!$modal.length) {
      window.location.href = '/auth/';
      return;
    }
    var $title = $modal.find('.auth-mode-panel[data-mode-panel="login"] .auth .title');
    if (!$title.length) {
      $title = $modal.find('.auth .title').first();
    }
    if ($title.length) {
      $title.text(options.checkout
        ? 'Для оформления заказа войдите или зарегистрируйтесь'
        : 'Авторизация');
    }
    var $notice = $modal.find('.auth-shell__notice').first();
    if ($notice.length) {
      $notice.text('').removeClass('is-visible');
    }
    if (options.register && window.primePhoneauthSetMode) {
      window.primePhoneauthSetMode('register');
    } else if (window.primePhoneauthSetMode) {
      window.primePhoneauthSetMode('login');
    }
    $modal.modal('show');
  }

  $(document).on('click', '.js-open-auth', function(e) {
    e.preventDefault();
    openAuthModal({ checkout: false });
  });

  function openCheckoutFromComment(comment) {
    if (!window.METPLUS_AUTH || !window.METPLUS_AUTH.authorized) {
      try {
        sessionStorage.setItem('metplus_checkout_after_auth', '1');
        sessionStorage.setItem('metplus_checkout_comment', comment || '');
      } catch (e) {}
      openAuthModal({ checkout: true });
      return;
    }

    $.get('/ajax/', { component: 'order' }).done(function(data) {
      $('.cart-content > .cart-content_second').html(data);
      $('.cart-content > .cart-content_second').find('textarea[name="COMMENT"]').val(comment || '');
      initTelInputMask($('.cart-content_second'));
      $('.cart-content').addClass('is-open');
      $('.cart-content_second').addClass('is-open');
      if (!is_mobile()) {
        $('html').addClass('is-hidden');
      }
    });
  }

  $('.cart-content').on('click', '.js-checkout', function() {
    var comment = $(this).closest('.container').find('.cart-table_textarea').val();
    openCheckoutFromComment(comment);
    return false;
  });

  // Оформление со страницы /cart/ — заказ в overlay
  $(document).on('click', '.basket-root--page .js-checkout', function(e) {
    e.preventDefault();
    var comment = $(this).closest('#basket-root').find('.cart-table_textarea').val();
    openCheckoutFromComment(comment);
    return false;
  });

  if (window.METPLUS_AUTH && window.METPLUS_AUTH.authorized) {
    try {
      if (sessionStorage.getItem('metplus_checkout_after_auth') === '1') {
        var pendingComment = sessionStorage.getItem('metplus_checkout_comment') || '';
        sessionStorage.removeItem('metplus_checkout_after_auth');
        sessionStorage.removeItem('metplus_checkout_comment');
        openCheckoutFromComment(pendingComment);
      }
    } catch (e) {}
  }


  function closeCheckoutStep() {
    $('.cart-content_second').removeClass('is-open');
    $('html').removeClass('is-hidden');

    if ($('.basket-root--page').length) {
      $('.cart-content').removeClass('is-open');
      return;
    }

    if ($('.cart-content_first').children().length) {
      $('.cart-content_first').addClass('is-open');
      return;
    }

    $('.cart-content').removeClass('is-open');
  }

  $('.cart-content').on('click', '.cart-content_second .js_back-site', function() {
    closeCheckoutStep();
    return false;
  });

  $('.cart-content').on('click', '.cart-close', function () {
    $('.cart-content').removeClass('is-open');
    $('.cart-content_second').removeClass('is-open');
    $('.cart-content_third').removeClass('is-open');
    $('html').removeClass('is-hidden');
  });

  $('.js-back-site_2').on('click', function() {
    $('.cart-content').removeClass('is-open');
    $('.cart-content_second').removeClass('is-open');
    $('.cart-content_third').removeClass('is-open');
    $('html').removeClass('is-hidden');
    return false;
  });
  if ($(window).width() < 575) {
    $('.category-item_other').on('click', function() {
      if ($(this).find('.category-item_other-list').length) {
        $(this).toggleClass('is-active');
        $(this).find('.category-item_other-list').slideToggle(150);
      }
    });
    $('.category-item_other-title').on('click', function() {
      if ($(this).closest('.category-item_other').find('.category-item_other-list').length) {
        $(this).closest('.category-item_other').toggleClass('is-active');
        $(this).closest('.category-item_other').find('.category-item_other-list').slideToggle(150);
        return false;
      }
    });
  }
  $('.catalog-menu_item').on('click', function() {
    if ($(this).find('.catalog-submenu').length) {
      $(this).children('a').toggleClass('is-active');
      $(this).find('.catalog-submenu').slideToggle(150);
    }
  });
  $('.catalog-menu_item > a').on('click', function() {
    if ($(this).closest('.catalog-menu_item').find('.catalog-submenu').length) {
      $(this).toggleClass('is-active');
      $(this).closest('.catalog-menu_item').find('.catalog-submenu').slideToggle(150);
      return false;
    }
  });
  $('.filter-box_title').on('click', function() {
    $('.filter-box_content').slideToggle(150);
    $(this).toggleClass('is-active');
  });
  if (is_mobile()) {
    $('.product-table').on('click', '.product-table_first-cell', function(e) {
      $(this).closest('tr').siblings('tr').find('.product-item_popup').fadeOut(100);
      if ($(e.target).closest(".product-item_popup").length == 0) $(this).find('.product-item_popup').fadeIn(100);
    });
    $('.product-table').on('click', '.product-item_popup-close', function() {
      $(this).closest('.product-item_popup').fadeOut(100);
    });
  }
  $('.catalog_sidebar-title').on('click', function(e) {
    $(this).toggleClass('is-active');
    $('.catalog-menu').slideToggle(150);
  });
  $('.vacancy-item_more-details').on('click', function(e) {
    if ($(this).html() == 'Свернуть') {
      $(this).closest('.vacancy-item').find('.vacancy-item_hidden').slideUp(150);
      $(this).text('Подробнее');
    } else {
      $(this).closest('.vacancy-item').find('.vacancy-item_hidden').slideDown(150);
      $(this).text('Свернуть');
    }
    $(this).toggleClass('is-active');
  });
  if (!is_mobile()) {
    $('.js-modal').on('show.bs.modal', function(event) {
      $('.fixed-menu').addClass('is-overflow');
      $('.scroll-to-top').addClass('is-hidden');
    });
    $('.js-modal').on('hidden.bs.modal', function(event) {
      $('.fixed-menu').removeClass('is-overflow');
      $('.scroll-to-top').removeClass('is-hidden');
    });
  }
  $('.js-select').selectric({
    maxHeight: 200,
    disableOnMobile: false,
    nativeOnMobile: false,
  });
  $('.tab-container').on('click', '.tab:not(.active)', function() {
    $(this).addClass('active').siblings().removeClass('active')
    $(this).closest('.tab-container').find('.tab-item').removeClass('is-visible').eq($(this).index()).addClass('is-visible');
    $(this).closest('.container').find('.contact_right-column .tab-item').removeClass('is-visible').eq($(this).index()).addClass('is-visible');

    $(this).closest('.tab-container').find('.tab-item').eq($(this).index()).find('.js-select').trigger('change');
  });
  var heightTopHead = $('.ui-header').outerHeight();
  jQuery(window).on("scroll load resize", function() {
    if ($(window).scrollTop() > heightTopHead) {
      $('.ui-header').addClass('fixed-menu');
      $('.global-wrapper').addClass('global-pad');
      setTimeout(function() {
        $('.ui-header').addClass('scroll-transform');
      }, 100);
    } else {
      $('.ui-header').removeClass('fixed-menu');
      $('.ui-header').removeClass('scroll-transform');
      $('.global-wrapper').removeClass('global-pad');
    }
    if ($(window).scrollTop() > $(window).height()) {
      $('.scroll-to-top').addClass('scroll-to-top-visible');
    } else {
      $('.scroll-to-top').removeClass('scroll-to-top-visible');
    }
  });
  $('.scroll-to-top').on('click', function() {
    $('html, body').animate({
      scrollTop: 0
    }, 800);
    return false;
  });

  $("#product-table").fancyTable({
    sortColumn: 1,
    nColumns: $("#product-table thead tr:first-child th").length || 8,
    sortable: false,
    searchable: false,
    globalSearch: true,
  });

  /**
   * Умный поиск над таблицей: фильтр по названию / марке / ячейкам строки.
   */
  (function initProductTableSmartSearch() {
    var $table = $('#product-table');
    var $wrap = $('[data-product-table-search]');
    if (!$table.length || !$wrap.length) {
      return;
    }
    var $input = $wrap.find('.product-table-smart-search__input');
    var $clear = $wrap.find('.product-table-smart-search__clear');
    var $meta = $wrap.find('.product-table-smart-search__meta');
    var $rows = $table.find('tbody tr');
    var total = $rows.length;
    var timer = null;

    function normalize(str) {
      return String(str || '')
        .toLowerCase()
        .replace(/ё/g, 'е')
        .replace(/\s+/g, ' ')
        .trim();
    }

    function rowHaystack($row) {
      var cached = $row.data('searchText');
      if (typeof cached === 'string') {
        return cached;
      }
      var clone = $row.clone();
      clone.find('.product-item_popup, script, style').remove();
      cached = normalize(clone.text());
      $row.data('searchText', cached);
      return cached;
    }

    function applyFilter() {
      var q = normalize($input.val());
      $clear.prop('hidden', !q);
      var visible = 0;
      if (!q) {
        $rows.show();
        visible = total;
        $meta.text('');
        if (typeof window.syncProductNameColumnWidth === 'function') {
          window.syncProductNameColumnWidth();
        }
        return;
      }
      var tokens = q.split(' ').filter(Boolean);
      $rows.each(function() {
        var hay = rowHaystack($(this));
        var ok = tokens.every(function(token) {
          return hay.indexOf(token) !== -1;
        });
        $(this).toggle(ok);
        if (ok) {
          visible++;
        }
      });
      if (visible === 0) {
        $meta.text('Ничего не найдено');
      } else if (visible === total) {
        $meta.text('');
      } else {
        $meta.text('Найдено: ' + visible + ' из ' + total);
      }
      if (typeof window.syncProductNameColumnWidth === 'function') {
        window.syncProductNameColumnWidth();
      }
    }

    $input.on('input', function() {
      clearTimeout(timer);
      timer = setTimeout(applyFilter, 120);
    });
    $input.on('keydown', function(e) {
      if (e.key === 'Escape') {
        $input.val('');
        applyFilter();
        $input.blur();
      }
    });
    $clear.on('click', function() {
      $input.val('').focus();
      applyFilter();
    });
    $wrap.find('form').on('submit', function(e) {
      e.preventDefault();
      applyFilter();
    });
  })();

  $(document).on('click', '[data-tip]', function(e) {
    e.stopPropagation();
    let $tip = $(this);
    $('[data-tip].is-tip-visible').not($tip).removeClass('is-tip-visible');
    // зелёный маркер — только показать (закрытие по mouseleave)
    if ($tip.hasClass('product-availability-marker')) {
      $tip.addClass('is-tip-visible');
      return;
    }
    $tip.toggleClass('is-tip-visible');
  });

  // Надёжный hover для зелёного маркера наличия
  $(document).on('mouseenter focusin', '.product-availability-marker', function() {
    $(this).addClass('is-tip-visible');
  });
  $(document).on('mouseleave focusout', '.product-availability-marker', function() {
    $(this).removeClass('is-tip-visible');
  });

  $(document).on('click', function(e) {
    if (!$(e.target).closest('[data-tip]').length) {
      $('[data-tip].is-tip-visible').removeClass('is-tip-visible');
    }
  });

  $('#success_msg').modal('show');

  function isOnlyPiecesRow($row) {
    return $row.data('only-pieces') == 1;
  }

  function isHalfPiecesRow($row) {
    return $row.data('half-pieces') == 1;
  }

  function isNoSurcharge1mRow($row) {
    return $row.data('no-surcharge-1m') == 1 && !isHalfPiecesRow($row);
  }

  function isBasicSheetRow($row) {
    return $row.data('basic-sheet') == 1;
  }

  function getBasicSheetWidthStepsFromRow($row) {
    let length = parseFloat($row.data('length')) || 0;
    let width = parseFloat($row.data('width')) || 0;
    if (length <= 0 || width <= 0) {
      return null;
    }

    // Шаг — 1 м по длине листа (Длина_Расчет)
    let lengthMeters = Math.max(1, Math.round(length));

    return {
      lengthMeters: lengthMeters,
      widthMeters: lengthMeters,
      piecesStep: 1 / lengthMeters,
      areaStep: width,
      metersStep: 1,
      fullArea: length * width
    };
  }

  function snapBasicSheetPiecesValue($row, pieces) {
    let steps = getBasicSheetWidthStepsFromRow($row);
    if (!steps) {
      return pieces;
    }

    let lengthUnits = Math.max(1, Math.round(pieces / steps.piecesStep));
    return parseFloat((lengthUnits * steps.piecesStep).toFixed(6));
  }

  function snapBasicSheetAreaValue($row, area) {
    let steps = getBasicSheetWidthStepsFromRow($row);
    if (!steps) {
      return area;
    }

    let lengthUnits = Math.max(1, Math.round(area / steps.areaStep));
    return parseFloat((lengthUnits * steps.areaStep).toFixed(3));
  }

  function getBasicSheetAreaPerPiece($row) {
    let steps = getBasicSheetWidthStepsFromRow($row);
    return steps ? steps.fullArea : 0;
  }

  function syncBasicSheetFromPieces($row) {
    let steps = getBasicSheetWidthStepsFromRow($row);
    if (!steps) {
      return;
    }

    let pieces = snapBasicSheetPiecesValue($row, parseFloat(String($row.find('[name="pieces"]').val()).replace(',', '.')) || steps.piecesStep);
    $row.find('[name="pieces"]').val(formatQty(pieces, 3));
    $row.find('[name="area_m2"]').val(formatQty(pieces * steps.fullArea, 3));
  }

  function syncBasicSheetFromArea($row, options) {
    options = options || {};
    let steps = getBasicSheetWidthStepsFromRow($row);
    if (!steps) {
      return;
    }

    let $area = $row.find('[name="area_m2"]');
    let area = snapBasicSheetAreaValue($row, parseFloat(String($area.val()).replace(',', '.')) || steps.areaStep);
    let pieces = snapBasicSheetPiecesValue($row, area / steps.fullArea);

    $row.find('[name="pieces"]').val(formatQty(pieces, 3));
    if (options.force || document.activeElement !== $area[0]) {
      $area.val(formatQty(area, 3));
    }
  }

  function isWeightEditableRow($row) {
    return $row.data('weight-editable') == 1;
  }

  function updateCalculatedWeightDisplay($row) {
    if (isWeightFrom500Row($row) || isWeightEditableRow($row)) {
      return;
    }

    let $display = $row.find('[data-weight-display]');
    let $hidden = $row.find('input[name="weight_kg"][type="hidden"]');
    if (!$display.length && !$hidden.length) {
      return;
    }

    let weightPerMeter = getWeightPerMeter($row);
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]')) || 0;
    let pieces = parseFloat(String($row.find('[name="pieces"]').val()).replace(',', '.')) || 0;
    let meters = parseFloat(String($row.find('[name="meters"]').val()).replace(',', '.')) || 0;
    let kg;

    // Нет Длина_Расчет: вес = штуки × вес штуки (data-weight-per-meter = вес 1 шт)
    if ((!metersInOnePiece || metersInOnePiece <= 0) && weightPerMeter > 0 && pieces > 0) {
      kg = formatQty(pieces * weightPerMeter, 3);
    } else if (weightPerMeter > 0 && meters > 0) {
      kg = formatQty(meters * weightPerMeter, 3);
    } else {
      if ($display.length) {
        $display.text('—');
      }
      if ($hidden.length) {
        $hidden.val('');
      }
      return;
    }

    if ($display.length) {
      $display.text(kg);
    }
    if ($hidden.length) {
      $hidden.val(kg);
    }
  }

  function isWeightFrom500Row($row) {
    return $row.data('weight-from-bulk') == 1 || $row.data('weight-from-500') == 1;
  }

  function getMinBulkWeight($row) {
    return parseFloat($row.data('min-bulk-weight')) || 500;
  }

  function getOrderMode($row) {
    return $row.data('order-mode') || 'pieces';
  }

  function setOrderMode($row, mode) {
    $row.data('order-mode', mode);
    $row.attr('data-order-mode', mode);
    applyOrderModeUi($row, mode);
  }

  function updateWeightFieldTip($row, mode) {
    let $weight = $row.find('[name="weight_kg"]');
    let piecesTip = $weight.data('tip-pieces');
    let bulkTip = $weight.data('tip-bulk');

    if (!piecesTip && !bulkTip) {
      return;
    }

    $weight.attr('data-tip', mode === 'bulk' ? (bulkTip || piecesTip) : (piecesTip || bulkTip));
  }

  function getWeightPerPiece($row) {
    let weightPerPiece = parseFloat($row.data('weight-per-piece'));

    if (!isNaN(weightPerPiece) && weightPerPiece > 0) {
      return weightPerPiece;
    }

    return getWeightPerMeter($row) * (parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]')));
  }

  function setFieldRestricted($input, restricted) {
    let $field = $input.closest('.product-table_field');
    if (!$field.length) {
      return;
    }

    let $lock = $field.children('.product-hint__icon--lock');

    if (restricted) {
      $field.addClass('product-table_field--restricted');
      $input.prop('readonly', true).addClass('is-readonly');
      if (!$lock.length) {
        $field.append('<span class="product-hint__icon--lock" aria-hidden="true"></span>');
      }
      return;
    }

    $field.removeClass('product-table_field--restricted');
    $input.prop('readonly', false).removeClass('is-readonly');
    $lock.remove();
  }

  function applyOrderModeUi($row, mode) {
    if (!isWeightFrom500Row($row)) {
      return;
    }

    let $weight = $row.find('[name="weight_kg"]');
    let $meters = $row.find('[name="meters"]');
    let $pieces = $row.find('[name="pieces"]');
    let minBulk = getMinBulkWeight($row);
    let lockMeters = isOnlyPiecesRow($row);

    if (mode === 'bulk') {
      $weight.prop('readonly', false).attr({min: minBulk, step: '0.001'}).removeClass('is-readonly is-synced');
      $weight.closest('.product-table_field').removeClass('product-table_field--restricted product-table_field--synced');
      setFieldRestricted($meters, lockMeters);
      setFieldRestricted($pieces, false);
      $pieces.attr({min: lockMeters ? 1 : 0.1, step: lockMeters ? '1' : '0.1'});
      $row.find('.product-table_cell-weight').removeClass('product-table_cell--locked');
      $row.find('[name="meters"]').closest('td').toggleClass('product-table_cell--locked', lockMeters);
      $row.find('[name="pieces"]').closest('td').removeClass('product-table_cell--locked');
      updateWeightFieldTip($row, mode);
      enhanceProductTableSteppers($row.closest('.product-table').length ? $row.closest('.product-table') : $row);
      return;
    }

    $weight.prop('readonly', false).attr({min: 0.01, step: '0.001'}).removeClass('is-readonly').addClass('is-synced');
    $weight.closest('.product-table_field').addClass('product-table_field--synced').removeClass('product-table_field--restricted');
    setFieldRestricted($meters, lockMeters);
    setFieldRestricted($pieces, false);
    $pieces.attr({min: lockMeters ? 1 : 0.1, step: lockMeters ? '1' : '0.1'});
    $row.find('.product-table_cell-weight').removeClass('product-table_cell--locked');
    $row.find('[name="meters"]').closest('td').toggleClass('product-table_cell--locked', lockMeters);
    $row.find('[name="pieces"]').closest('td').removeClass('product-table_cell--locked');
    updateWeightFieldTip($row, mode);
    enhanceProductTableSteppers($row.closest('.product-table').length ? $row.closest('.product-table') : $row);
  }

  function isWeightFieldEditing($row) {
    let $weight = $row.find('[name="weight_kg"]');
    return $weight.is(':focus') || $weight.data('is-editing') === 1;
  }

  function formatPiecesDisplay(pieces) {
    pieces = parseFloat(pieces);
    if (isNaN(pieces)) {
      return '';
    }
    if (Math.abs(pieces - Math.round(pieces)) < 1e-6) {
      return String(Math.round(pieces));
    }
    return formatQty(pieces, 1);
  }

  /** Длина кратно 0,1 м (1.1, 1.2…), не 1.35 */
  function snapMetersTenth(meters) {
    meters = parseFloat(meters);
    if (isNaN(meters) || meters <= 0) {
      return 0.1;
    }
    return Math.max(0.1, Math.round(meters * 10) / 10);
  }

  /** «Кратно 1м без наценки» — длина целыми метрами */
  function snapMetersWhole(meters) {
    meters = parseFloat(meters);
    if (isNaN(meters) || meters <= 0) {
      return 1;
    }
    return Math.max(1, Math.round(meters));
  }

  function snapMetersForRow(meters, $row) {
    if (isNoSurcharge1mRow($row) && !isBasicSheetRow($row) && !isOnlyPiecesRow($row)) {
      return snapMetersWhole(meters);
    }
    return snapMetersTenth(meters);
  }

  function formatMetersDisplay(meters) {
    meters = snapMetersTenth(meters);
    if (Math.abs(meters - Math.round(meters)) < 1e-6) {
      return String(Math.round(meters));
    }
    return formatQty(meters, 1);
  }

  function formatMetersDisplayForRow(meters, $row) {
    if (isNoSurcharge1mRow($row) && !isBasicSheetRow($row) && !isOnlyPiecesRow($row)) {
      return String(snapMetersWhole(meters));
    }
    return formatMetersDisplay(meters);
  }

  /** Штуки кратно 0,1 (1.1, 1.2, 1.3…), не 1.35 */
  function snapPiecesTenth(pieces) {
    pieces = parseFloat(pieces);
    if (isNaN(pieces) || pieces <= 0) {
      return 0.1;
    }
    return Math.max(0.1, Math.round(pieces * 10) / 10);
  }

  function syncFromPiecesMode($row) {
    let $piecesInput = $row.find('[name="pieces"]');
    let pieces;
    if (isOnlyPiecesRow($row)) {
      pieces = normalizePiecesValue($piecesInput);
    } else {
      pieces = snapPiecesTenth($piecesInput.val());
      $piecesInput.val(formatPiecesDisplay(pieces));
    }
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($piecesInput);
    let weightPerMeter = getWeightPerMeter($row);
    let meters = snapMetersForRow(pieces * metersInOnePiece, $row);
    let kg = meters * weightPerMeter;

    $row.find('[name="meters"]').val(formatMetersDisplayForRow(meters, $row));

    if (!isWeightFieldEditing($row)) {
      $row.find('[name="weight_kg"]').val(formatQty(kg, 3));
    }

    if (kg >= getMinBulkWeight($row)) {
      setOrderMode($row, 'bulk');
      return;
    }

    setOrderMode($row, 'pieces');
  }

  function syncFromMetersMode($row) {
    if (isOnlyPiecesRow($row)) {
      return;
    }

    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]'));
    let weightPerMeter = getWeightPerMeter($row);
    let meters = snapMetersTenth($row.find('[name="meters"]').val());
    let pieces = snapPiecesTenth(metersInOnePiece > 0 ? (meters / metersInOnePiece) : meters);
    meters = snapMetersTenth(pieces * metersInOnePiece);
    let kg = meters * weightPerMeter;

    $row.find('[name="meters"]').val(formatMetersDisplay(meters));
    $row.find('[name="pieces"]').val(formatPiecesDisplay(pieces));

    if (!isWeightFieldEditing($row)) {
      $row.find('[name="weight_kg"]').val(formatQty(kg, 3));
    }

    if (kg >= getMinBulkWeight($row)) {
      setOrderMode($row, 'bulk');
      return;
    }

    setOrderMode($row, 'pieces');
  }

  function syncFromBulkMode($row) {
    let kg = parseFloat($row.find('[name="weight_kg"]').val()) || 0;
    let minBulk = getMinBulkWeight($row);
    let weightPerMeter = getWeightPerMeter($row);
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]'));

    if (kg < minBulk) {
      let pieces = isOnlyPiecesRow($row)
        ? ceilOnlyPiecesValue(kg / getWeightPerPiece($row))
        : snapPiecesTenth(kg / getWeightPerPiece($row));
      $row.find('[name="pieces"]').val(formatPiecesDisplay(pieces));
      syncFromPiecesMode($row);
      return;
    }

    let meters = kg / weightPerMeter;
    let pieces = meters / metersInOnePiece;

    // «ТОЛЬКО ШТ»: штуки всегда вверх (58.48 → 59), метры/вес подтягиваем
    if (isOnlyPiecesRow($row)) {
      pieces = ceilOnlyPiecesValue(pieces);
      meters = pieces * metersInOnePiece;
      if (!isWeightFieldEditing($row)) {
        kg = meters * weightPerMeter;
        $row.find('[name="weight_kg"]').val(formatQty(kg, 3));
      }
    } else {
      pieces = snapPiecesTenth(pieces);
      meters = snapMetersTenth(pieces * metersInOnePiece);
      if (!isWeightFieldEditing($row)) {
        kg = meters * weightPerMeter;
        $row.find('[name="weight_kg"]').val(formatQty(kg, 3));
      }
    }

    $row.find('[name="meters"]').val(formatMetersDisplay(meters));
    $row.find('[name="pieces"]').val(formatPiecesDisplay(pieces));
    setOrderMode($row, 'bulk');
  }

  function ceilOnlyPiecesValue(pieces) {
    pieces = parseFloat(pieces);
    if (isNaN(pieces) || pieces <= 0) {
      return 1;
    }
    if (Math.abs(pieces - Math.round(pieces)) < 1e-6) {
      return Math.max(1, Math.round(pieces));
    }
    return Math.max(1, Math.ceil(pieces));
  }

  function resolveCartQuantity($row) {
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]'));

    if (isHalfPiecesRow($row)) {
      let pieces = normalizeHalfPiecesValue($row.find('[name="pieces"]'));
      syncHalfPiecesSheetArea($row, pieces);
      return pieces * metersInOnePiece;
    }

    if (isBasicSheetRow($row)) {
      let pieces = snapBasicSheetPiecesValue($row, parseFloat(String($row.find('[name="pieces"]').val()).replace(',', '.')) || 0);
      return pieces * metersInOnePiece;
    }

    if ($row.data('only-pieces') == 1) {
      let pieces = ceilOnlyPiecesValue($row.find('[name="pieces"]').val());
      // Нет Длина_Расчет (мотки и т.п.) — в корзину идут штуки, не метры
      if (!metersInOnePiece || metersInOnePiece <= 0) {
        return pieces;
      }
      return pieces * metersInOnePiece;
    }

    if (isWeightFrom500Row($row)) {
      if (getOrderMode($row) === 'bulk') {
        let kg = parseFloat($row.find('[name="weight_kg"]').val());
        let weightPerMeter = getWeightPerMeter($row);

        if (isNaN(kg) || kg < getMinBulkWeight($row) || !weightPerMeter) {
          return null;
        }

        let meters = kg / weightPerMeter;
        if (isOnlyPiecesRow($row) && metersInOnePiece > 0) {
          let pieces = ceilOnlyPiecesValue(meters / metersInOnePiece);
          return pieces * metersInOnePiece;
        }

        return meters;
      }

      let pieces = parseFloat(String($row.find('[name="pieces"]').val()).replace(',', '.'));

      if (isNaN(pieces) || pieces <= 0) {
        return null;
      }

      return pieces * metersInOnePiece;
    }

    return parseFloat($row.find('[name="meters"]').val());
  }

  function getWeightPerMeter($row) {
    let weightPerMeter = parseFloat($row.data('weight-per-meter'));

    if (isNaN(weightPerMeter) || weightPerMeter <= 0) {
      weightPerMeter = parseFloat($row.find('[name="weight_kg"]').data('weight-per-meter'));
    }

    return weightPerMeter > 0 ? weightPerMeter : 0;
  }

  function getMetersInOnePiece($obj) {
    return parseFloat($obj.attr("data-meters-in-one-piece"));
  }

  function formatQty(value, decimals) {
    if (isNaN(value)) {
      return '';
    }

    return parseFloat(value.toFixed(decimals));
  }

  function syncRowQuantities($row, source) {
    if (!isWeightEditableRow($row)) {
      return;
    }

    if (isWeightFrom500Row($row)) {
      if (source === 'weight') {
        syncFromBulkMode($row);
      } else if (source === 'pieces') {
        syncFromPiecesMode($row);
      }
      return;
    }

    let weightPerMeter = getWeightPerMeter($row);
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]'));

    if (!weightPerMeter || !metersInOnePiece) {
      return;
    }

    let $meters = $row.find('[name="meters"]');
    let $pieces = $row.find('[name="pieces"]');
    let $weight = $row.find('[name="weight_kg"]');

    if (source === 'weight') {
      let kg = parseFloat($weight.val()) || 0;
      let meters = snapMetersTenth(kg / weightPerMeter);
      let pieces = snapPiecesTenth(meters / metersInOnePiece);
      meters = snapMetersTenth(pieces * metersInOnePiece);
      if (isHalfPiecesRow($row)) {
        pieces = snapHalfPiecesUp(pieces);
        meters = pieces * metersInOnePiece;
        kg = meters * weightPerMeter;
        $weight.val(formatQty(kg, 2));
      } else {
        kg = meters * weightPerMeter;
        if (!isWeightFieldEditing($row)) {
          $weight.val(formatQty(kg, 3));
        }
      }
      $meters.val(isHalfPiecesRow($row) ? formatQty(meters, 2) : formatMetersDisplay(meters));
      $pieces.val(pieces % 1 === 0 ? String(Math.round(pieces)) : formatQty(pieces, 1));
      return;
    }

    if (source === 'meters') {
      if (isIncompleteQtyInput($meters.val())) {
        return;
      }
      let meters = isHalfPiecesRow($row)
        ? (parseFloat(String($meters.val()).replace(',', '.')) || 0)
        : snapMetersTenth($meters.val());
      let pieces = meters / metersInOnePiece;
      if (isHalfPiecesRow($row)) {
        pieces = snapHalfPiecesUp(pieces);
        meters = pieces * metersInOnePiece;
        $meters.val(formatQty(meters, 2));
      } else {
        pieces = snapPiecesTenth(pieces);
        meters = snapMetersTenth(pieces * metersInOnePiece);
        $meters.val(formatMetersDisplay(meters));
      }
      let kg = meters * weightPerMeter;
      $pieces.val(pieces % 1 === 0 ? String(Math.round(pieces)) : formatQty(pieces, 1));
      $weight.val(formatQty(kg, 2));
      return;
    }

    if (source === 'pieces') {
      if (isIncompleteQtyInput($pieces.val())) {
        return;
      }
      let pieces = parseFloat(String($pieces.val()).replace(',', '.')) || 0;
      if (isHalfPiecesRow($row)) {
        pieces = snapHalfPiecesUp(pieces);
        $pieces.val(pieces % 1 === 0 ? String(Math.round(pieces)) : pieces.toFixed(1));
      } else {
        pieces = snapPiecesTenth(pieces);
        $pieces.val(formatPiecesDisplay(pieces));
      }
      let meters = isHalfPiecesRow($row)
        ? pieces * metersInOnePiece
        : snapMetersTenth(pieces * metersInOnePiece);
      let kg = meters * weightPerMeter;
      $meters.val(isHalfPiecesRow($row) ? formatQty(meters, 2) : formatMetersDisplay(meters));
      $weight.val(formatQty(kg, 2));
    }
  }

  function normalizePiecesValue($input) {
    let pieces = parseInt($input.val(), 10);

    if (isNaN(pieces) || pieces < 1) {
      pieces = 1;
    }

    $input.val(pieces);
    return pieces;
  }

  function snapHalfPiecesUp(pieces) {
    pieces = parseFloat(pieces);
    if (isNaN(pieces) || pieces < 0.5) {
      return 0.5;
    }
    let steps = pieces * 2;
    let nearest = Math.round(steps);
    // Уже кратно 0,5 — не трогаем; иначе всегда вверх (3.7 → 4)
    if (Math.abs(steps - nearest) < 1e-6) {
      return Math.max(0.5, nearest / 2);
    }
    return Math.max(0.5, Math.ceil(steps) / 2);
  }

  function normalizeHalfPiecesValue($input) {
    let pieces = snapHalfPiecesUp(String($input.val()).replace(',', '.'));
    $input.val(pieces % 1 === 0 ? String(Math.round(pieces)) : pieces.toFixed(1));
    return pieces;
  }

  /** Значение уже «полное», но не кратно 0,5 (например 3.8) — пора округлить. */
  function needsHalfPiecesSnap(raw) {
    if (isIncompleteQtyInput(raw)) {
      return false;
    }
    let value = parseFloat(String(raw == null ? '' : raw).replace(',', '.'));
    if (isNaN(value)) {
      return false;
    }
    return Math.abs(value * 2 - Math.round(value * 2)) > 1e-6;
  }

  /** Пока пользователь набирает (пусто, «4.», «0») — не подменять значение на 0,5/1. */
  function isIncompleteQtyInput(raw) {
    let value = String(raw == null ? '' : raw).trim().replace(',', '.');
    if (value === '' || value === '.' || value === '-' || value === '-.') {
      return true;
    }
    if (/\.$/.test(value)) {
      return true;
    }
    // «0» / «0.» — промежуточный ввод перед 0,5 или 4,5
    if (value === '0') {
      return true;
    }
    return false;
  }

  function syncHalfPiecesSheetArea($row, pieces) {
    let $area = $row.find('[name="area_m2"]');
    if (!$area.length) {
      return;
    }
    let fullArea = parseFloat($area.data('full-area')) || 0;
    if (fullArea <= 0) {
      return;
    }
    $area.val(formatQty(pieces * fullArea, 3));
  }

  function syncHalfPiecesFromArea($row) {
    let $area = $row.find('[name="area_m2"]');
    let fullArea = parseFloat($area.data('full-area')) || 0;
    let areaStep = parseFloat($area.data('area-step')) || (fullArea * 0.5);
    if (fullArea <= 0 || areaStep <= 0) {
      return;
    }

    let area = parseFloat(String($area.val()).replace(',', '.'));
    if (isNaN(area) || area < areaStep) {
      area = areaStep;
    }
    let units = Math.max(1, Math.round(area / areaStep));
    area = parseFloat((units * areaStep).toFixed(3));
    let pieces = snapHalfPiecesUp(area / fullArea);
    area = parseFloat((pieces * fullArea).toFixed(3));

    $area.val(formatQty(area, 3));
    let $pieces = $row.find('[name="pieces"]');
    $pieces.val(pieces % 1 === 0 ? String(Math.round(pieces)) : pieces.toFixed(1));

    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($pieces) || 0;
    if (metersInOnePiece > 0) {
      $row.find('[name="meters"]').val(formatQty(pieces * metersInOnePiece, 2));
    }
  }

  $('.product-table').on('focus', '[name="weight_kg"]', function() {
    let $row = $(this).closest('tr');
    let $input = $(this);

    $input.data('is-editing', 1);

    if (isWeightFrom500Row($row) && getOrderMode($row) === 'pieces') {
      $input.removeClass('is-synced');
      $input.closest('.product-table_field').removeClass('product-table_field--synced');

      if (this.value && typeof this.select === 'function') {
        this.select();
      }
    }
  });

  $('.product-table').on('input', '[name="weight_kg"]', function() {
    let $row = $(this).closest('tr');
    let $input = $(this);

    if (isWeightFrom500Row($row)) {
      let kg = parseFloat($input.val());

      if (!isNaN(kg) && kg >= getMinBulkWeight($row)) {
        syncFromBulkMode($row);
      }

      return;
    }

    let minWeight = parseFloat($input.attr('min')) || 0.01;
    let kg = parseFloat($input.val());

    if (isNaN(kg) || kg < minWeight) {
      return;
    }

    syncRowQuantities($row, 'weight');
  });

  $('.product-table').on('blur', '[name="weight_kg"]', function() {
    let $row = $(this).closest('tr');
    let $input = $(this);

    $input.data('is-editing', 0);

    if (isWeightFrom500Row($row)) {
      syncFromBulkMode($row);
      return;
    }

    let minWeight = parseFloat($input.attr('min')) || 0.01;
    let kg = parseFloat($input.val());

    if (isNaN(kg) || kg < minWeight) {
      kg = minWeight;
      $input.val(kg);
    }

    syncRowQuantities($row, 'weight');
  });

  $('.product-table').on('input blur change', '[name="pieces"]', function(e) {
    let self = $(this);
    let $row = self.closest('tr');
    let metersInOnePiece = getMetersInOnePiece(self);
    let isCommit = e.type === 'blur' || e.type === 'change';
    let rawVal = self.val();

    // Во время набора не форсируем 0,5/1 — иначе нельзя стереть и ввести 4,5
    if (!isCommit && isIncompleteQtyInput(rawVal)) {
      return;
    }

    // 3.8 при шаге 0,5 — округляем сразу (4), не ждём blur
    let forceHalfSnap = isHalfPiecesRow($row) && needsHalfPiecesSnap(rawVal);
    if (forceHalfSnap) {
      isCommit = true;
    }

    if (isBasicSheetRow($row)) {
      syncBasicSheetFromPieces($row);
      updateCalculatedWeightDisplay($row);
      return;
    }

    if (isOnlyPiecesRow($row)) {
      if (isCommit) {
        normalizePiecesValue(self);
      }
    } else if (isHalfPiecesRow($row) && isCommit) {
      normalizeHalfPiecesValue(self);
    }

    if (isWeightFrom500Row($row)) {
      syncFromPiecesMode($row);
      return;
    }

    if (isWeightEditableRow($row)) {
      syncRowQuantities($row, 'pieces');
      if (isCommit && isHalfPiecesRow($row)) {
        normalizeHalfPiecesValue(self);
        syncRowQuantities($row, 'pieces');
      }
      return;
    }

    let pieces;
    if (isHalfPiecesRow($row)) {
      pieces = isCommit
        ? normalizeHalfPiecesValue(self)
        : parseFloat(String(self.val()).replace(',', '.'));
      if (!isCommit && (isNaN(pieces) || pieces < 0.5)) {
        return;
      }
      if (!isCommit) {
        pieces = snapHalfPiecesUp(pieces);
      }
    } else {
      pieces = snapPiecesTenth(self.val());
      if (isCommit) {
        self.val(formatPiecesDisplay(pieces));
      }
    }

    // Без длины — вес из штук, метры не трогаем
    if (!metersInOnePiece || metersInOnePiece <= 0) {
      updateCalculatedWeightDisplay($row);
      return;
    }

    let meters = isHalfPiecesRow($row)
      ? pieces * metersInOnePiece
      : snapMetersForRow(pieces * metersInOnePiece, $row);
    $row.find('[name="meters"]').val(
      isHalfPiecesRow($row) ? formatQty(meters, 2) : formatMetersDisplayForRow(meters, $row)
    );
    if (isHalfPiecesRow($row)) {
      syncHalfPiecesSheetArea($row, pieces);
    }
    updateCalculatedWeightDisplay($row);
  });

  $('.product-table').on('input', '[name="meters"]', function() {
    let self = $(this);
    let $row = self.closest('tr');

    if (isOnlyPiecesRow($row) || isBasicSheetRow($row)) {
      return;
    }

    if (isIncompleteQtyInput(self.val())) {
      return;
    }

    if (isWeightFrom500Row($row)) {
      syncFromMetersMode($row);
      return;
    }

    if (isWeightEditableRow($row)) {
      syncRowQuantities($row, 'meters');
      return;
    }

    let metersInOnePiece = getMetersInOnePiece(self);
    let meters = parseFloat(String(self.val()).replace(',', '.'));
    if (isNaN(meters) || meters <= 0 || !metersInOnePiece) {
      return;
    }
    let pieces = isHalfPiecesRow($row)
      ? snapHalfPiecesUp(meters / metersInOnePiece)
      : snapPiecesTenth(meters / metersInOnePiece);

    $row.find('[name="pieces"]').val(
      isHalfPiecesRow($row)
        ? (pieces % 1 === 0 ? String(Math.round(pieces)) : pieces.toFixed(1))
        : formatPiecesDisplay(pieces)
    );
    if (isHalfPiecesRow($row)) {
      syncHalfPiecesSheetArea($row, pieces);
    }
    updateCalculatedWeightDisplay($row);
  });

  $('.product-table').on('input blur change', '[name="area_m2"]', function() {
    let $row = $(this).closest('tr');
    if (isHalfPiecesRow($row) && parseFloat($row.data('width')) > 0) {
      syncHalfPiecesFromArea($row);
      return;
    }
    if (!isBasicSheetRow($row)) {
      return;
    }

    syncBasicSheetFromArea($row, { force: true });
  });

  $('.product-table').on('blur change', '[name="meters"]', function() {
    let self = $(this);
    let $row = self.closest('tr');

    if (isOnlyPiecesRow($row) || isBasicSheetRow($row)) {
      return;
    }

    if (isWeightFrom500Row($row)) {
      syncFromMetersMode($row);
      return;
    }

    let metersInOnePiece = getMetersInOnePiece(self) || parseFloat($row.data('length')) || 1;

    // «Только шт и 0,5 шт» — метры кратно 0,5 × длина штуки
    if (isHalfPiecesRow($row)) {
      let meters = parseFloat(String(self.val()).replace(',', '.'));
      if (isNaN(meters) || meters <= 0) {
        meters = metersInOnePiece * 0.5;
      }
      let pieces = snapHalfPiecesUp(meters / metersInOnePiece);
      meters = pieces * metersInOnePiece;
      self.val(formatQty(meters, 2));
      $row.find('[name="pieces"]').val(pieces % 1 === 0 ? String(Math.round(pieces)) : pieces.toFixed(1));
      syncHalfPiecesSheetArea($row, pieces);
      if (isWeightEditableRow($row)) {
        syncRowQuantities($row, 'meters');
      }
      updateCalculatedWeightDisplay($row);
      return;
    }

    // «Кратно 1м без наценки» — длина целыми метрами
    if (isNoSurcharge1mRow($row)) {
      let meters = snapMetersWhole(self.val());
      self.val(String(meters));
      let pieces = meters / metersInOnePiece;
      $row.find('[name="pieces"]').val(formatPiecesDisplay(pieces));
      if (isWeightEditableRow($row)) {
        syncRowQuantities($row, 'meters');
      }
      updateCalculatedWeightDisplay($row);
      return;
    }

    // трубы/прутки/круг — длина кратно 0,1 м
    let meters = snapMetersTenth(self.val());
    self.val(formatMetersDisplay(meters));
    let pieces = snapPiecesTenth(meters / metersInOnePiece);
    $row.find('[name="pieces"]').val(formatPiecesDisplay(pieces));

    if (isWeightEditableRow($row)) {
      syncRowQuantities($row, 'meters');
    }
    updateCalculatedWeightDisplay($row);
  });

  $('.product-table tr[data-basic-sheet="1"]').each(function() {
    syncBasicSheetFromArea($(this), { force: true });
  });

  $('.product-table tr[data-weight-from-bulk="1"], .product-table tr[data-weight-from-500="1"]').each(function() {
    syncFromPiecesMode($(this));
  });

  $('.product-table').on('input', '[name="width"]', function() {
    let $input = $(this);
    if (isOnlyPiecesRow($input.closest('tr'))) {
      $input.val($input.data('width-default'));
    }
  });

  function getProductInputStep($input) {
    let step = parseFloat(String($input.attr('step') || $input.data('step') || '').replace(',', '.'));
    if (!step || isNaN(step) || step <= 0) {
      let name = $input.attr('name');
      if (name === 'pieces') {
        return isHalfPiecesRow($input.closest('tr')) ? 0.5 : (isOnlyPiecesRow($input.closest('tr')) ? 1 : 0.1);
      }
      if (name === 'meters') {
        return isHalfPiecesRow($input.closest('tr'))
          ? Math.max(0.1, (parseFloat($input.data('meters-in-one-piece')) || 1) * 0.5)
          : (isNoSurcharge1mRow($input.closest('tr')) ? 1 : 0.1);
      }
      if (name === 'width') return 0.1;
      if (name === 'weight_kg') return 0.1;
      return 0.1;
    }
    return step;
  }

  function getProductInputMin($input) {
    let min = parseFloat(String($input.attr('min') || $input.data('min') || '').replace(',', '.'));
    if (isNaN(min)) {
      let name = $input.attr('name');
      if (name === 'pieces') {
        return isHalfPiecesRow($input.closest('tr')) ? 0.5 : (isOnlyPiecesRow($input.closest('tr')) ? 1 : 0.1);
      }
      return 0;
    }
    return min;
  }

  function formatSteppedProductValue(value, step) {
    let decimals = 0;
    let stepStr = String(step);
    if (stepStr.indexOf('.') >= 0) {
      decimals = stepStr.split('.')[1].replace(/0+$/, '').length;
    }
    decimals = Math.min(Math.max(decimals, 0), 3);
    let fixed = parseFloat(Number(value).toFixed(Math.max(decimals, 1)));
    if (Math.abs(fixed - Math.round(fixed)) < 1e-9) {
      return String(Math.round(fixed));
    }
    return String(fixed);
  }

  function stepProductTableInput($input, direction) {
    if (!$input.length || $input.prop('disabled') || $input.prop('readonly')) {
      return;
    }
    let $row = $input.closest('tr');
    // Сначала дотянуть до сетки шага (3.8 → 4), потом ±step
    if ($input.attr('name') === 'pieces' && isHalfPiecesRow($row) && needsHalfPiecesSnap($input.val())) {
      normalizeHalfPiecesValue($input);
    }
    let step = getProductInputStep($input);
    let min = getProductInputMin($input);
    let current = parseFloat(String($input.val()).replace(',', '.'));
    if (isNaN(current)) {
      current = min > 0 ? min : 0;
    }
    let next = current + direction * step;
    // привязка к сетке шага
    next = Math.round(next / step) * step;
    if (next < min) {
      next = min;
    }
    let display = formatSteppedProductValue(next, step);
    if ($input.attr('type') === 'number') {
      $input.val(parseFloat(display));
    } else {
      $input.val(display);
    }
    $input.trigger('input').trigger('change').trigger('blur');
  }

  function enhanceProductTableSteppers($root) {
    let $scope = $root && $root.length ? $root : $('.product-table');
    let spinHtml =
      '<span class="product-table_spin">' +
        '<button type="button" class="product-table_spin-btn product-table_spin-btn--up" tabindex="-1" aria-label="Увеличить">' +
          '<svg viewBox="0 0 10 6" aria-hidden="true"><path d="M5 0.6L9.4 5.2H0.6L5 0.6Z"/></svg>' +
        '</button>' +
        '<button type="button" class="product-table_spin-btn product-table_spin-btn--down" tabindex="-1" aria-label="Уменьшить">' +
          '<svg viewBox="0 0 10 6" aria-hidden="true"><path d="M5 5.4L0.6 0.8H9.4L5 5.4Z"/></svg>' +
        '</button>' +
      '</span>';

    $scope.find('.product-table_field').each(function() {
      let $field = $(this);
      if ($field.hasClass('product-table_field--restricted') || $field.hasClass('product-table_field--locked')) {
        return;
      }
      let $input = $field.find('.product-table-input:not([type="hidden"])').first();
      if (!$input.length || $input.prop('readonly') || $input.prop('disabled')) {
        return;
      }

      // Убираем нативные стрелки number — оставляем только наши
      if ($input.attr('type') === 'number') {
        let step = $input.attr('step');
        let min = $input.attr('min');
        $input.attr({
          type: 'text',
          inputmode: 'decimal',
          autocomplete: 'off'
        });
        if (step) {
          $input.attr('data-step', step);
        }
        if (min) {
          $input.attr('data-min', min);
        }
      }

      if (!$field.find('.product-table_spin').length) {
        $field.append(spinHtml);
      }
      $field.addClass('product-table_field--stepper');
    });
  }

  $('.product-table').on('click', '.product-table_spin-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    let $btn = $(this);
    let $input = $btn.closest('.product-table_field').find('.product-table-input:not([type="hidden"])').first();
    stepProductTableInput($input, $btn.hasClass('product-table_spin-btn--up') ? 1 : -1);
  });

  $('.product-table').on('keydown', '.product-table-input:not([type="hidden"])', function(e) {
    if (e.key !== 'ArrowUp' && e.key !== 'ArrowDown') {
      return;
    }
    let $input = $(this);
    if (!$input.closest('.product-table_field--stepper').length) {
      return;
    }
    e.preventDefault();
    stepProductTableInput($input, e.key === 'ArrowUp' ? 1 : -1);
  });

  enhanceProductTableSteppers();
  $(document).on('catalog:view-updated productTable:refreshed', function() {
    enhanceProductTableSteppers();
  });

  /**
   * Ширина колонки «Наименование» = длина самого длинного названия, max 300px.
   * Если длиннее — название переносится на 2+ строки.
   * Считаем только видимые строки (умный поиск скрывает остальные).
   */
  window.syncProductNameColumnWidth = function syncProductNameColumnWidth() {
    var $tables = $('.product-table');
    if (!$tables.length) {
      return;
    }
    var probe = document.createElement('span');
    probe.style.cssText = 'position:absolute;left:-9999px;top:0;visibility:hidden;white-space:nowrap;';
    document.body.appendChild(probe);

    $tables.each(function() {
      var $table = $(this);
      var $visibleRows = $table.find('tbody tr').filter(':visible');
      var maxText = 0;
      var sampleEl = $visibleRows.find('.product-item_name').not('.product-item_popup .product-item_name').get(0);
      if (!sampleEl) {
        sampleEl = $table.find('tbody .product-item_name').not('.product-item_popup .product-item_name').get(0);
      }
      if (sampleEl) {
        var cs = window.getComputedStyle(sampleEl);
        probe.style.font = cs.font;
        probe.style.letterSpacing = cs.letterSpacing;
      }
      var $names = $visibleRows.length
        ? $visibleRows.find('.product-item_name')
        : $table.find('tbody .product-item_name');
      $names.each(function() {
        if ($(this).closest('.product-item_popup').length) {
          return;
        }
        probe.textContent = (this.textContent || '').replace(/\s+/g, ' ').trim();
        if (probe.textContent) {
          maxText = Math.max(maxText, probe.offsetWidth);
        }
      });
      // заголовок «Наименование…» — не уже, чем подпись
      var $head = $table.find('thead tr:first-child th:first-child');
      if ($head.length) {
        var headCs = window.getComputedStyle($head.get(0));
        probe.style.font = headCs.font;
        probe.style.letterSpacing = headCs.letterSpacing;
        probe.style.fontWeight = headCs.fontWeight;
        probe.textContent = ($head.textContent || '').replace(/\s+/g, ' ').trim();
        maxText = Math.max(maxText, Math.min(probe.offsetWidth, 160));
        if (sampleEl) {
          var nameCs = window.getComputedStyle(sampleEl);
          probe.style.font = nameCs.font;
          probe.style.letterSpacing = nameCs.letterSpacing;
        }
      }
      var extras = 40;
      var nameW = Math.min(300, Math.max(80, Math.ceil(maxText || 80)));
      var colW = nameW + extras;
      $table.css('--product-name-col-w', colW + 'px');

      var colCount = $table.find('thead tr:first-child th').length || 1;
      var $colgroup = $table.children('colgroup.product-table_cols');
      if (!$colgroup.length) {
        $colgroup = $('<colgroup class="product-table_cols"/>');
        $table.prepend($colgroup);
      }
      $colgroup.empty();
      $('<col class="product-table_col-name"/>').css('width', colW + 'px').appendTo($colgroup);
      for (var i = 1; i < colCount; i++) {
        $('<col/>').appendTo($colgroup);
      }

      // если таблица шире контейнера — ужимаем колонку названия (текст переносится)
      var parentEl = ($table.closest('.catalog_right-column').get(0)) || $table.parent().get(0);
      var tableEl = $table.get(0);
      var guard = 0;
      while (parentEl && colW > 140 && guard++ < 60) {
        // только видимая строка: у display:none getBoundingClientRect = 0
        var lastCell = ($visibleRows.first().find('td:last-child').get(0))
          || tableEl.querySelector('tbody tr td:last-child');
        if (!lastCell) {
          break;
        }
        var parentRight = parentEl.getBoundingClientRect().right;
        var lastRight = lastCell.getBoundingClientRect().right;
        if (lastRight <= parentRight + 1) {
          break;
        }
        colW -= 6;
        $table.css('--product-name-col-w', colW + 'px');
        $colgroup.find('col.product-table_col-name').css('width', colW + 'px');
      }
    });

    document.body.removeChild(probe);
  };

  window.syncProductNameColumnWidth();
  var nameColResizeTimer;
  $(window).on('resize', function() {
    clearTimeout(nameColResizeTimer);
    nameColResizeTimer = setTimeout(window.syncProductNameColumnWidth, 120);
  });
});

if ($('.map-container').length) {
  YaMapsShown = false;
  $(window).on("scroll load resize", function() {
    if (!YaMapsShown) {
      if ($(window).scrollTop() + $(window).height() > $('.map-container').offset().top - 500) {
        showYaMaps();
        YaMapsShown = true;
      }
    }
  });

  function showYaMaps() {
    var script = document.createElement("script");
    script.type = "text/javascript";
    script.src = "https://api-maps.yandex.ru/2.1/?lang=ru_RU";
    document.getElementById("map").appendChild(script);
    script.onload = function() {
      ymaps.ready(init);
      var myMap,
        myPlacemark;

      function init() {

        // Создание экземпляра карты.
        var myMap = new ymaps.Map('map', {
              center: $($(".js-select option").get(0)).data('center').split(','),
              zoom: 18,
              behaviors: ['default', 'scrollZoom'],
            }, {
              searchControlProvider: 'yandex#search'
            });
            myMap.behaviors.disable('scrollZoom');

        collection = new ymaps.GeoObjectCollection(null, { preset: "islands#redIcon" }),
        myMap.geoObjects.add(collection)

        var arPlacemark = [];
        $(".js-select option").each(function(indx, element){

          if($(element).data('center')){
            placemark = new ymaps.Placemark($(element).data('center').split(','), {
              balloonContent: $(element).text()
            }, {
              balloonAutoPan: false
            });
            collection.add(placemark)
            arPlacemark[$(element).val()] = placemark;
          }
        });

        $('.js-select').bind('change', function () {
          var id = $(this).find('option:selected').val();
          if(arPlacemark[id]){
            if (!arPlacemark[id].balloon.isOpen()) {
              arPlacemark[id].balloon.open();
              myMap.setCenter($(this).find('option:selected').data('center').split(','), 18);
            } else {
              arPlacemark[id].balloon.close();
            }
          }
          return false;
        });

      }
    }
  }
}