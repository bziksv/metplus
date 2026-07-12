<footer class="main-footer">
    <div class="container">
        <div class="row">
            <?$APPLICATION->IncludeComponent("bitrix:menu", "bottom.menu", Array(
                "ROOT_MENU_TYPE" => "bottom",	// Тип меню для первого уровня
                "MENU_CACHE_TYPE" => "A",	// Тип кеширования
                "MENU_CACHE_TIME" => "36000000",	// Время кеширования (сек.)
                "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                "MENU_THEME" => "site",	// Тема меню
                "CACHE_SELECTED_ITEMS" => "N",
                "MENU_CACHE_GET_VARS" => "",	// Значимые переменные запроса
                "MAX_LEVEL" => "2",	// Уровень вложенности меню
                "CHILD_MENU_TYPE" => "bottom_left",	// Тип меню для остальных уровней
                "USE_EXT" => "Y",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                "DELAY" => "N",	// Откладывать выполнение шаблона меню
                "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                "COMPONENT_TEMPLATE" => "catalog_horizontal"
            ),
                false
            );?>

            <?$APPLICATION->IncludeComponent("bitrix:menu", "bottom_useful.menu", Array(
                "ROOT_MENU_TYPE" => "bottom_useful",	// Тип меню для первого уровня
                "MENU_CACHE_TYPE" => "A",	// Тип кеширования
                "MENU_CACHE_TIME" => "36000000",	// Время кеширования (сек.)
                "MENU_CACHE_USE_GROUPS" => "Y",	// Учитывать права доступа
                "MENU_THEME" => "site",	// Тема меню
                "CACHE_SELECTED_ITEMS" => "N",
                "MENU_CACHE_GET_VARS" => "",	// Значимые переменные запроса
                "MAX_LEVEL" => "1",	// Уровень вложенности меню
                "CHILD_MENU_TYPE" => "bottom_useful",	// Тип меню для остальных уровней
                "USE_EXT" => "Y",	// Подключать файлы с именами вида .тип_меню.menu_ext.php
                "DELAY" => "N",	// Откладывать выполнение шаблона меню
                "ALLOW_MULTI_SELECT" => "N",	// Разрешить несколько активных пунктов одновременно
                "COMPONENT_TEMPLATE" => "catalog_horizontal"
            ),
                false
            );?>
            <div class="footer-column">
                <ul class="footer-contact_list">
                    <li><a href="tel:+74732075555"><span class="glipf-call-answer"></span>+7 (473) 207-55-55</a></li>
                    <li><a href="tel:info@metplus-vrn.ru" class="footer-mail"><span class="glipf-email"></span>info@metplus-vrn.ru</a></li>
                    <li>
						<span class="glipf-clock"></span>Будни: 8:00 - 17:00 <br>Суббота: 8:30 до 16:30<br>Воскресенье: выходной
                    </li>
					<!--<noindex><li><a href="https://vk.com/metplusvrn"  rel="nofollow"><span class="instagram-link"><img style="width:14px; height: 14px; margin-right: 6px;" src="https://metplus-osk.ru/upload/medialibrary/9f5/9f502909be683766dde6842684115a95.png" alt="vk"> Наш ВКонтакте</span></a></li></noindex>
					<noindex><li><a href="https://api.whatsapp.com/send/?phone=79515478597&text=%D0%97%D0%B4%D1%80%D0%B0%D0%B2%D1%81%D1%82%D0%B2%D1%83%D0%B9%D1%82%D0%B5!+%D0%9C%D0%B5%D0%BD%D1%8F+%D0%B8%D0%BD%D1%82%D0%B5%D1%80%D0%B5%D1%81%D1%83%D0%B5%D1%82...&type=phone_number&app_absent=0">
						<span class="instagram-link"><img style="width: 16px; height: 16px; margin-right: 6px;" src="/bitrix/whatsapp-logo1.png" alt="WhatsApp">Наш WhatsApp</span></a></li></noindex>
					<noindex><li><a href="https://t.me/Metallinvest36" rel="nofollow"><span class="instagram-link"><img style="width:16px; height: 16px; margin-right: 6px;" src="/upload/medialibrary/09b/haaevn1ln24j4a4fdw287nuul3g8m5hw.png" alt="Telegram"> Наш Telegram</span></a></li></noindex>-->

</ul>

				<p>Мы в социальных сетях:</p>
				<div style="display: flex; flex-wrap: nowrap; grid-gap: 10px;">
					<a href="https://vk.com/metplusvrn"><img width="45px" src="/upload/medialibrary/e1a/rd4hr9ktr2x9vc3aixelncehtwuxuk7m.png"></a>
					<a href="https://t.me/Metallinvest36"><img width="45px" src="/upload/medialibrary/9e6/h4ks1897jii7tpz4p2n5kt2q4msu8bgo.png"></a>
					<a href="https://max.ru/u/f9LHodD0cOJho6iA9qny6BeBDmXuS3C0OqAiYpeNlJgk2CiAa0mdgIl4Kdw"><img width="45px" src="/upload/medialibrary/0d7/jj6ka9s67gsrrog7qb6nqa938gharssj.png"></a>
				</div>

               <!-- <ul class="social-network">
                    <li><a href=""><span class="glipf-vk"></span></a></li>
                    <li><a href=""><span class="glipf-facebook"></span></a></li>
                    <li><a href=""><span class="glipf-odnoklassniki"></span></a></li>
                    <li><a href=""><span class="glipf-twitter"></span></a></li>
                </ul>-->
 </div>
		</div>
		<div style= "text-align= center">
<iframe align="middle" src="https://yandex.ru/sprav/widget/rating-badge/1288614717?type=rating&theme=dark"; width="150" height="50" frameborder="0"></iframe>
		</div>
        <div class="row">
            <div class="col-lg-10">
                <div class="rules">
                     @ 2006-2025. ООО «Корпорация Металлинвест»
                    <a href="/upload/politics.pdf" target="_blank">Политика конфиденциальности</a>
                    <a href="/upload/compliance.pdf" target="_blank">Согласие на обработку персональных данных</a>
                </div>
            </div>
            <div class="col-lg-2">
                <a class="prime-incut white colour" style="padding: 1.2em 0 0;"></a>
            </div>
</div>
		<hr style="margin: 25px 0;">
		<noindex><p style="font-size: 0.8rem;">На нашем сайте осуществляется сбор персональных данных и <a target="_blank" href="/upload/politika-ispolzovanija-cookies-metplus-vrn.pdf">cookies</a> для улучшения работы сайта, персонализации контента и анализа посещаемости. Продолжая пользоваться сайтом, вы соглашаетесь с использованием cookies и <a target="_blank" href="/upload/compliance.pdf">обработкой ваших данных</a> в соответствии с нашей <a target="_blank" href="/upload/politics.pdf">Политикой конфиденциальности</a>. Чтобы отказаться от обработки, отключите сохранение cookies в настройках вашего браузера.</p></noindex>

	</div>

</footer>
<!-- end main-footer -->
<div class="scroll-to-top"></div>
</div>
<!-- END GLOBAL-WRAPPER -->
<div class="cart-content">
    <div class="cart-content_first"></div>
    <div class="cart-content_second"></div>
</div>

<div aria-hidden="true" class="modal fade js-modal" id="citySelect" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-city" role="document">
        <div class="modal-content">
            <div class="modal-city_title section-title">Выберите ваш город</div>
            <button aria-label="Close" class="close uhified_close-btn" data-dismiss="modal" type="button"></button>
            <div class="row">
                <ul class="modal-city_list-unstyled col-sm-6">

                    <li>
                        Воронеж
                    </li>
                    <li>
                        Лиски
                    </li>

                </ul>
                <ul class="modal-city_list-unstyled col-sm-6">
                    <li><a href="http://metplus-osk.ru/">Старый Оскол</a></li>
					<li><a href="http://metplus-msk.ru/">Москва</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div aria-hidden="true" class="modal fade js-modal" id="callback" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-callback" role="document">
        <div class="modal-content">
            <button aria-label="Close" class="close uhified_close-btn" data-dismiss="modal" type="button"></button>

            <?$APPLICATION->IncludeComponent(
                "prime:main.feedback",
                "call",
                array(
                    "EVENT_MESSAGE_ID" => array(
                        0 => "86",
                    ),
                    "IBLOCK_ID" => "31",
                    "IBLOCK_TYPE" => "feedback",
                    "OK_TEXT" => "Спасибо, ваше сообщение принято.",
                    "PROPERTY_CODE" => array(
                        0 => "NAME",
                        1 => "PHONE",
                    ),
                    "USE_CAPTCHA" => "N",
                    "CAPTCHA_SITE_KEY" => "6Ld60c4UAAAAAGXC83w4_ZPy-Q6OErFzaVYjjNQl",
                    "CAPTCHA_SERVER_KEY" => "6Ld60c4UAAAAAP7qkcYtAQ_byWeHtD0kgGFiH0Q9",
                    "COMPONENT_TEMPLATE" => "call"
                ),
                false
            );?>
        </div>
    </div>
</div>

<? if($_REQUEST["success"]): ?>
    <div aria-hidden="true" class="modal fade js-modal" id="success_msg" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-callback" role="document">
            <div class="modal-content">
                <button aria-label="Close" class="close uhified_close-btn" data-dismiss="modal" type="button"></button>
                <div class="form-callback_title">
                    Сообщение отправлено!
                    <small>Мы обязательно <span class="min">вам перезвоним.</span></small>
                </div>
            </div>
        </div>
    </div>
<? endif; ?>

<link href="<?=SITE_TEMPLATE_PATH?>/css/min.css" rel="stylesheet" />
<link href="<?=SITE_TEMPLATE_PATH?>/css/main.css" rel="stylesheet" />
<link href="<?=SITE_TEMPLATE_PATH?>/css/custom.css" rel="stylesheet" />
<script src="<?=SITE_TEMPLATE_PATH?>/js/min.js"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/libs/fancyTable.js"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/libs/parallax.js"></script>
<script src="<?=SITE_TEMPLATE_PATH?>/js/main.js"></script>


<? if(isDebug()): ?>
<script>
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.ru/b7243579/crm/site_button/loader_2_s8ozty.js');
</script>
<? endif; ?>




<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js', 'ym');

    ym(29872139, 'init', {clickmap:true, referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/29872139" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->







<!-- Roistat Counter Start -->
<script>
(function(w, d, s, h, id) {
    w.roistatProjectId = id; w.roistatHost = h;
    var p = d.location.protocol == "https:" ? "https://" : "http://";
    var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init?referrer="+encodeURIComponent(d.location.href);
    var js = d.createElement(s); js.charset="UTF-8"; js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
})(window, document, 'script', 'cloud.roistat.com', 'ee592069cbec053b2a92fc92789d0975');
</script>
<!-- Roistat Counter End -->
</body>
</html>