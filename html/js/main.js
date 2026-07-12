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
    var text = $(this).html();
    $('.select-city_field').text(text);
    $('#citySelect').modal('hide');
  });
  if (!is_mobile()) {
    $('.text-section').paroller();
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
  $(".product-item_cart-btn").on("click", function() {
    $(this).clone().css({
      'position': 'absolute',
      'z-index': '1000',
      'width': '57px',
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
  $(".wrapper_select-office").on("change", function() {
    var number = $(this).find('option:selected').index();
    $(this).closest('.tab-item').find('.contact-section-desc').removeClass('is-active').eq(number).addClass('is-active');
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
  $('.head-cart > a').on('click', function() {
    $('.cart-content').addClass('is-open');
    if (!is_mobile()) {
      $('html').addClass('is-hidden');
    }
    return false;
  });
  $('.js-checkout').on('click', function() {
    $('.cart-content_second').addClass('is-open');
    return false;
  });
  $('.js-checkout-2').on('click', function() {
    $('.cart-content_third').addClass('is-open');
    return false;
  });
  $('.js_back-site').on('click', function() {
    $('.cart-content').removeClass('is-open');
    $('html').removeClass('is-hidden');
    return false;
  });
  $('.cart-close').on('click', function() {
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
    $('.product-table_first-cell').on('click', function(e) {
      $(this).closest('tr').siblings('tr').find('.product-item_popup').fadeOut(100);
      if ($(e.target).closest(".product-item_popup").length == 0) $(this).find('.product-item_popup').fadeIn(100);
    });
    $('.product-item_popup-close').on('click', function() {
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
        myMap = new ymaps.Map("map", {
          center: [51.64389407230797, 39.29436749999995],
          zoom: 13,
          behaviors: ['default', 'scrollZoom'],
        });
        myMap.behaviors.disable('scrollZoom');
        myMap.geoObjects.add(new ymaps.Placemark([51.64389407230797, 39.29436749999995], {
          balloonContent: 'г. Воронеж, Монтажный проезд, д. 26',
        }, {
          // Опции.
          // Необходимо указать данный тип макета.
          iconLayout: 'default#image',
          preset: 'islands#redIcon',
          iconImageHref: 'img/icons/map.svg',
          // Размеры метки.
        }));
        myMap.geoObjects.add(myPlacemark);
        // }
      }
    }
  }
}