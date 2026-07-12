<?php

function customBasketPriceTypeHandler($productId, $quantity = 1, $arUserGroups = [], $renewal = 'N', $arPrices = [], $siteId = false, $arDiscountCoupons = false)
{
    // Защита от бесконечной рекурсии
    static $running = false;
    if ($running) {
        return true;
    }

    if (!\Bitrix\Main\Loader::includeModule('catalog')) {
        return true;
    }

    $ID_BLOCK = 36;
    $pricePerMeterId = 17;
    $pricePerMeterPlus20Id = 18;
    $PriceTypeId = $pricePerMeterId;

    if (!isCustomPrice($ID_BLOCK, $productId)) {
        return true;
    }

    $running = true;

    $length = getLengthProduct($ID_BLOCK, $productId);
    $half = $length / 2;

    if ($quantity % $half != 0) {
        $PriceTypeId = $pricePerMeterPlus20Id;
    }

    // Получаем цену товара через штатное D7 ORM каталога
    $arItemPrice = \Bitrix\Catalog\PriceTable::getList([
        'filter' => [
            '=PRODUCT_ID' => $productId,
            '=CATALOG_GROUP_ID' => $PriceTypeId
        ],
        'select' => ['ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'CATALOG_GROUP_NAME' => 'CATALOG_GROUP.NAME'],
        'limit' => 1
    ])->fetch();

    $running = false;

    // Если у товара успешно найдена цена выбранного типа
    if ($arItemPrice) {

        $priceName = $arItemPrice['CATALOG_GROUP_NAME'];

        if ($PriceTypeId == $pricePerMeterPlus20Id) {
            $priceName = $arItemPrice['CATALOG_GROUP_NAME'] . " рассчитан коэффициент +20%";
        }

        return array(
            'PRICE' => array(
                "ID" => $arItemPrice["ID"],
                "CATALOG_GROUP_ID" => $arItemPrice["CATALOG_GROUP_ID"],
                "PRICE" => $arItemPrice["PRICE"],
                "CURRENCY" => $arItemPrice["CURRENCY"],
                "VAT_INCLUDED" => "Y",
                "NOTES" => $priceName,
            ),
            'DISCOUNT' => array(),
            'RESULT_PRICE' => array(
                'BASE_PRICE' => $arItemPrice["PRICE"],
                'DISCOUNT_PRICE' => $arItemPrice["PRICE"],
                'CURRENCY' => $arItemPrice["CURRENCY"],
                'VAT_INCLUDED' => "Y",
            )
        );
    }

    return true;
}