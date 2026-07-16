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
    $('.dropdown-menu_item').hover(function() {
      var height1 = $(this).find('.dropdown-submenu').outerHeight();
      var height2 = $(this).find('.dropdown-submenu_img').outerHeight();
      console.log(height2)
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

  $('.head-cart').on('click', 'a', function() {

    $.get("/ajax/", { component: "cart" }).done(function(data) {
      $('.cart-content > .cart-content_first').html(data);
      $('.cart-content').addClass('is-open');
      if (!is_mobile()) {
        $('html').addClass('is-hidden');
      }
    });

    return false;
  });

  $('.cart-content').on('click', '.js-checkout', function() {
    var comment = $(this).closest('.container').find('.cart-table_textarea').val();
    $.get("/ajax/", { component: "order" }).done(function(data) {
      $('.cart-content > .cart-content_second').html(data);
      $('.cart-content > .cart-content_second').find('textarea[name="COMMENT"]').val(comment);
      $('.cart-content_second').addClass('is-open');
    });
    return false;
  });


  $('.cart-content').on('click', '.cart-content_second .js_back-site', function() {
    $('.cart-content_second').removeClass('is-open');
    $('.cart-content_first').addClass('is-open');
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

  $('input[type="tel"]').inputmask("+7 (999) 999 99 99", {
    "clearIncomplete": true,
    showMaskOnHover: false,
  });

  $("#product-table").fancyTable({
    sortColumn: 1,
    nColumns: $("#product-table thead tr:first-child th").length || 8,
    sortable: false,
    searchable: true,
    globalSearch: true,
    inputPlaceholder: "Введите название или марку стали",
  });

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

  function isBasicSheetRow($row) {
    return $row.data('basic-sheet') == 1;
  }

  function getBasicSheetWidthStepsFromRow($row) {
    let length = parseFloat($row.data('length')) || 0;
    let width = parseFloat($row.data('width')) || 0;
    if (length <= 0 || width <= 0) {
      return null;
    }

    let widthMeters = Math.max(1, Math.round(width));

    return {
      widthMeters: widthMeters,
      piecesStep: 1 / widthMeters,
      areaStep: length,
      metersStep: length / widthMeters,
      fullArea: length * width
    };
  }

  function snapBasicSheetPiecesValue($row, pieces) {
    let steps = getBasicSheetWidthStepsFromRow($row);
    if (!steps) {
      return pieces;
    }

    let widthUnits = Math.max(1, Math.round(pieces / steps.piecesStep));
    return parseFloat((widthUnits * steps.piecesStep).toFixed(6));
  }

  function snapBasicSheetAreaValue($row, area) {
    let steps = getBasicSheetWidthStepsFromRow($row);
    if (!steps) {
      return area;
    }

    let widthUnits = Math.max(1, Math.round(area / steps.areaStep));
    return parseFloat((widthUnits * steps.areaStep).toFixed(3));
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

  function isWeightFrom500Row($row) {
    return $row.data('weight-from-500') == 1;
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
        $field.prepend('<span class="product-hint__icon--lock" aria-hidden="true"></span>');
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

    if (mode === 'bulk') {
      $weight.prop('readonly', false).attr({min: minBulk, step: '0.001'}).removeClass('is-readonly is-synced');
      $weight.closest('.product-table_field').removeClass('product-table_field--restricted product-table_field--synced');
      setFieldRestricted($meters, true);
      setFieldRestricted($pieces, true);
      $pieces.attr({min: 1, step: '1'});
      $row.find('.product-table_cell-weight').removeClass('product-table_cell--locked');
      $row.find('[name="meters"]').closest('td').addClass('product-table_cell--locked');
      $row.find('[name="pieces"]').closest('td').addClass('product-table_cell--locked');
      updateWeightFieldTip($row, mode);
      return;
    }

    $weight.prop('readonly', false).attr({min: 0.01, step: '0.001'}).removeClass('is-readonly').addClass('is-synced');
    $weight.closest('.product-table_field').addClass('product-table_field--synced').removeClass('product-table_field--restricted');
    setFieldRestricted($meters, true);
    setFieldRestricted($pieces, false);
    $pieces.attr({min: 1, step: '1'});
    $row.find('.product-table_cell-weight').removeClass('product-table_cell--locked');
    $row.find('[name="meters"]').closest('td').addClass('product-table_cell--locked');
    $row.find('[name="pieces"]').closest('td').removeClass('product-table_cell--locked');
    updateWeightFieldTip($row, mode);
  }

  function isWeightFieldEditing($row) {
    let $weight = $row.find('[name="weight_kg"]');
    return $weight.is(':focus') || $weight.data('is-editing') === 1;
  }

  function syncFromPiecesMode($row) {
    let pieces = normalizePiecesValue($row.find('[name="pieces"]'));
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]'));
    let weightPerMeter = getWeightPerMeter($row);
    let meters = pieces * metersInOnePiece;
    let kg = meters * weightPerMeter;

    $row.find('[name="meters"]').val(formatQty(meters, 2));

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
      let pieces = Math.max(1, Math.round(kg / getWeightPerPiece($row)));
      $row.find('[name="pieces"]').val(pieces);
      syncFromPiecesMode($row);
      return;
    }

    let meters = kg / weightPerMeter;
    let pieces = meters / metersInOnePiece;

    $row.find('[name="meters"]').val(formatQty(meters, 2));
    $row.find('[name="pieces"]').val(formatQty(pieces, 3));
    setOrderMode($row, 'bulk');
  }

  function resolveCartQuantity($row) {
    let metersInOnePiece = parseFloat($row.data('length')) || getMetersInOnePiece($row.find('[name="pieces"]'));

    if (isBasicSheetRow($row)) {
      let pieces = snapBasicSheetPiecesValue($row, parseFloat(String($row.find('[name="pieces"]').val()).replace(',', '.')) || 0);
      return pieces * metersInOnePiece;
    }

    if ($row.data('only-pieces') == 1) {
      let pieces = parseInt($row.find('[name="pieces"]').val(), 10) || 1;
      return pieces * metersInOnePiece;
    }

    if (isWeightFrom500Row($row)) {
      if (getOrderMode($row) === 'bulk') {
        let kg = parseFloat($row.find('[name="weight_kg"]').val());
        let weightPerMeter = getWeightPerMeter($row);

        if (isNaN(kg) || kg < getMinBulkWeight($row) || !weightPerMeter) {
          return null;
        }

        return kg / weightPerMeter;
      }

      let pieces = parseInt($row.find('[name="pieces"]').val(), 10);

      if (isNaN(pieces) || pieces < 1) {
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
      let meters = kg / weightPerMeter;
      let pieces = meters / metersInOnePiece;
      $meters.val(formatQty(meters, 2));
      $pieces.val(formatQty(pieces, 2));
      return;
    }

    if (source === 'meters') {
      let meters = parseFloat($meters.val()) || 0;
      let pieces = meters / metersInOnePiece;
      let kg = meters * weightPerMeter;
      $pieces.val(formatQty(pieces, 2));
      $weight.val(formatQty(kg, 2));
      return;
    }

    if (source === 'pieces') {
      let pieces = parseFloat($pieces.val()) || 0;
      let meters = pieces * metersInOnePiece;
      let kg = meters * weightPerMeter;
      $meters.val(formatQty(meters, 2));
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

  function normalizeHalfPiecesValue($input) {
    let pieces = parseFloat(String($input.val()).replace(',', '.'));

    if (isNaN(pieces) || pieces < 0.5) {
      pieces = 0.5;
    }

    pieces = Math.round(pieces * 2) / 2;
    $input.val(pieces % 1 === 0 ? String(Math.round(pieces)) : pieces.toFixed(1));
    return pieces;
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

  $('.product-table').on('input blur', '[name="pieces"]', function() {
    let self = $(this);
    let $row = self.closest('tr');
    let metersInOnePiece = getMetersInOnePiece(self);

    if (isBasicSheetRow($row)) {
      syncBasicSheetFromPieces($row);
      return;
    }

    if (isOnlyPiecesRow($row) || isWeightFrom500Row($row)) {
      normalizePiecesValue(self);
    } else if (isHalfPiecesRow($row)) {
      normalizeHalfPiecesValue(self);
    }

    if (isWeightFrom500Row($row)) {
      syncFromPiecesMode($row);
      return;
    }

    if (isWeightEditableRow($row)) {
      syncRowQuantities($row, 'pieces');
      return;
    }

    let pieces = parseFloat(self.val());
    let meters = pieces * metersInOnePiece;
    $row.find('[name="meters"]').val(meters);
  });

  $('.product-table').on('input', '[name="meters"]', function() {
    let self = $(this);
    let $row = self.closest('tr');

    if (isOnlyPiecesRow($row) || isWeightFrom500Row($row) || isBasicSheetRow($row)) {
      return;
    }

    if (isWeightEditableRow($row)) {
      syncRowQuantities($row, 'meters');
      return;
    }

    let metersInOnePiece = getMetersInOnePiece(self);
    let meters = parseFloat(self.val());
    let pieces = isBasicSheetRow($row)
      ? Math.max(1, Math.round(meters / metersInOnePiece))
      : (meters / metersInOnePiece).toFixed(2);

    $row.find('[name="pieces"]').val(pieces);
  });

  $('.product-table').on('input blur change', '[name="area_m2"]', function() {
    let $row = $(this).closest('tr');
    if (!isBasicSheetRow($row)) {
      return;
    }

    syncBasicSheetFromArea($row, { force: true });
  });

  $('.product-table').on('blur change', '[name="meters"]', function() {
    let self = $(this);
    let $row = self.closest('tr');

    if (isOnlyPiecesRow($row) || isWeightFrom500Row($row) || isBasicSheetRow($row)) {
      return;
    }

    // трубы/прутки — метры только целыми
    if ($row.data('width') > 0) {
      return;
    }

    let meters = parseFloat(String(self.val()).replace(',', '.'));
    if (isNaN(meters) || meters < 1) {
      meters = 1;
    } else {
      meters = Math.round(meters);
    }

    self.val(meters);
    let metersInOnePiece = getMetersInOnePiece(self) || parseFloat($row.data('length')) || 1;
    $row.find('[name="pieces"]').val((meters / metersInOnePiece).toFixed(2));

    if (isWeightEditableRow($row)) {
      syncRowQuantities($row, 'meters');
    }
  });

  $('.product-table tr[data-basic-sheet="1"]').each(function() {
    syncBasicSheetFromArea($(this), { force: true });
  });

  $('.product-table tr[data-weight-from-500="1"]').each(function() {
    syncFromPiecesMode($(this));
  });

  $('.product-table').on('input', '[name="width"]', function() {
    let $input = $(this);
    if (isOnlyPiecesRow($input.closest('tr'))) {
      $input.val($input.data('width-default'));
    }
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