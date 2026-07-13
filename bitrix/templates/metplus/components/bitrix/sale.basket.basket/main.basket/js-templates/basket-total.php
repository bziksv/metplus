<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 */
?>
<script id="basket-total-template" type="text/html">

    <div class="left-column">
        <a href="" class="gray-btn back-site_btn js_back-site">Вернуться к сайту</a>
    </div>
    <div class="right-column">
        <div class="cart-total-price">
            <span class="cart-total-price__label">Итоговая стоимость:</span>
            <span class="cart-total_sum" data-entity="basket-total-price">{{{PRICE_FORMATED}}}</span>
        </div>
        <div class="cart-total-price cart-total-price--cutting" style="display:none;">
            <span class="cart-total-price__label">Резка:</span>
            <span class="cart-total_sum" data-entity="basket-total-cutting">0 ₽</span>
        </div>
        <div class="cart-total-price cart-total-price--with-cutting" style="display:none;">
            <span class="cart-total-price__label">Итого с резкой:</span>
            <span class="cart-total_sum" data-entity="basket-total-with-cutting">0 ₽</span>
        </div>
        <a href="" class="main-btn checkout-btn js-checkout"><?=Loc::getMessage('SBB_ORDER')?></a>
    </div>

</script>
