<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Общий вызов корзины для /cart/ и AJAX-overlay.
 * @var string $cartDisplayMode 'page'|'overlay'
 */
$cartDisplayMode = isset($cartDisplayMode) && $cartDisplayMode === 'page' ? 'page' : 'overlay';

$APPLICATION->IncludeComponent(
    'bitrix:sale.basket.basket',
    'main.basket',
    [
        'ACTION_VARIABLE' => 'basketAction',
        'ADDITIONAL_PICT_PROP_11' => '-',
        'ADDITIONAL_PICT_PROP_13' => '-',
        'AUTO_CALCULATION' => 'Y',
        'BASKET_IMAGES_SCALING' => 'adaptive',
        'COLUMNS_LIST_HEADER' => [
            0 => 'Название товара',
            1 => 'Марка стали',
            2 => 'Цена',
            3 => 'Количество шт',
            4 => 'м/м²',
            5 => 'Вес, кг',
            6 => 'Сумма',
            7 => '',
        ],
        'COLUMNS_LIST_EXT' => [
            0 => 'DELETE',
            1 => 'SUM',
        ],
        'COLUMNS_LIST_MOBILE' => [],
        'COMPATIBLE_MODE' => 'Y',
        'CORRECT_RATIO' => 'N',
        'DEFERRED_REFRESH' => 'N',
        'DISCOUNT_PERCENT_POSITION' => 'bottom-right',
        'DISPLAY_MODE' => 'extended',
        'EMPTY_BASKET_HINT_PATH' => '/catalog/',
        'GIFTS_BLOCK_TITLE' => 'Выберите один из подарков',
        'GIFTS_CONVERT_CURRENCY' => 'N',
        'GIFTS_HIDE_BLOCK_TITLE' => 'N',
        'GIFTS_HIDE_NOT_AVAILABLE' => 'N',
        'GIFTS_MESS_BTN_BUY' => 'Выбрать',
        'GIFTS_MESS_BTN_DETAIL' => 'Подробнее',
        'GIFTS_PAGE_ELEMENT_COUNT' => '4',
        'GIFTS_PLACE' => 'BOTTOM',
        'GIFTS_PRODUCT_PROPS_VARIABLE' => 'prop',
        'GIFTS_PRODUCT_QUANTITY_VARIABLE' => 'quantity',
        'GIFTS_SHOW_DISCOUNT_PERCENT' => 'Y',
        'GIFTS_SHOW_OLD_PRICE' => 'N',
        'GIFTS_TEXT_LABEL_GIFT' => 'Подарок',
        'HIDE_COUPON' => 'N',
        'LABEL_PROP' => [],
        'PATH_TO_ORDER' => '/personal/order/make/',
        'PRICE_DISPLAY_MODE' => 'Y',
        'PRICE_VAT_SHOW_VALUE' => 'N',
        'PRODUCT_BLOCKS_ORDER' => 'props,sku',
        'QUANTITY_FLOAT' => 'Y',
        'SET_TITLE' => $cartDisplayMode === 'page' ? 'Y' : 'N',
        'SHOW_DISCOUNT_PERCENT' => 'Y',
        'SHOW_FILTER' => 'Y',
        'SHOW_RESTORE' => 'Y',
        'TEMPLATE_THEME' => 'blue',
        'TOTAL_BLOCK_DISPLAY' => [
            0 => 'top',
        ],
        'USE_DYNAMIC_SCROLL' => 'Y',
        'USE_ENHANCED_ECOMMERCE' => 'N',
        'USE_GIFTS' => 'N',
        'USE_PREPAYMENT' => 'N',
        'USE_PRICE_ANIMATION' => 'Y',
        'COMPONENT_TEMPLATE' => 'main.basket',
        'CART_DISPLAY_MODE' => $cartDisplayMode,
    ],
    false
);
