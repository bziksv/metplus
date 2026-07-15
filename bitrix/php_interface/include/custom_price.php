<?php

function quantityNeedsMeterSurcharge($quantity, $stepMeters)
{
    $step = (float)$stepMeters;
    $qty = (float)$quantity;

    if ($step <= 0 || $qty <= 0) {
        return false;
    }

    $ratio = $qty / $step;

    return abs($ratio - round($ratio)) > 0.0001;
}

function fetchCatalogPriceRow($productId, $priceTypeId)
{
    $row = \Bitrix\Catalog\PriceTable::getList([
        'filter' => [
            '=PRODUCT_ID' => (int)$productId,
            '=CATALOG_GROUP_ID' => (int)$priceTypeId,
        ],
        'select' => ['ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY'],
        'limit' => 1,
    ])->fetch();

    return $row ?: null;
}

function buildCustomOptimalPriceResult(array $arItemPrice, $priceValue, $notes)
{
    $priceValue = round((float)$priceValue, 2);

    return [
        'PRICE' => [
            'ID' => $arItemPrice['ID'],
            'CATALOG_GROUP_ID' => $arItemPrice['CATALOG_GROUP_ID'],
            'PRICE' => $priceValue,
            'CURRENCY' => $arItemPrice['CURRENCY'],
            'VAT_INCLUDED' => 'Y',
            'NOTES' => $notes,
        ],
        'DISCOUNT' => [],
        'RESULT_PRICE' => [
            'BASE_PRICE' => $priceValue,
            'DISCOUNT_PRICE' => $priceValue,
            'CURRENCY' => $arItemPrice['CURRENCY'],
            'VAT_INCLUDED' => 'Y',
        ],
    ];
}

function customBasketPriceTypeHandler($productId, $quantity = 1, $arUserGroups = [], $renewal = 'N', $arPrices = [], $siteId = false, $arDiscountCoupons = false)
{
    static $running = false;
    if ($running) {
        return true;
    }

    // D7-вызов с Event — берём аргументы из события
    if (is_object($productId) && method_exists($productId, 'getParameter')) {
        $event = $productId;
        $productId = $event->getParameter('PRODUCT_ID') ?? $event->getParameter('productId');
        $quantity = $event->getParameter('QUANTITY') ?? $event->getParameter('quantity') ?? 1;
    }

    $productId = (int)$productId;
    $quantity = (float)$quantity;

    if ($productId <= 0 || !\Bitrix\Main\Loader::includeModule('catalog')) {
        return true;
    }

    $ID_BLOCK = 36;
    $pricePerMeterId = 17;
    $pricePerMeterPlus20Id = 18;

    if (!isCustomPrice($ID_BLOCK, $productId)) {
        return true;
    }

    $running = true;

    try {
        $length = getLengthProduct($ID_BLOCK, $productId);
        $half = $length / 2;
        $isBasicSheet = isBasicSheetProduct($productId, $ID_BLOCK);

        $applyPlus10 = false;
        $applyPlus20 = false;

        $allowsFreeMeterCutting = productAllowsFreeMeterCutting($productId, $ID_BLOCK);

        if ($isBasicSheet) {
            if (basicSheetQuantityNeedsPlus10($productId, $quantity, $ID_BLOCK)) {
                $applyPlus10 = true;
            }

            if (
                !$applyPlus10
                && getBasketItemPropForPrice($productId, $quantity, 'CUTTING_SURCHARGE_10') === 'Y'
            ) {
                $applyPlus10 = true;
            }
        } elseif (
            !$allowsFreeMeterCutting
            && quantityNeedsMeterSurcharge($quantity, $half)
        ) {
            // не кратно 0,5 шт (= половине длины) → +20%
            $applyPlus20 = true;
        }

        $basePrice = fetchCatalogPriceRow($productId, $pricePerMeterId);
        if (!$basePrice) {
            $fallbackPrice = fetchCatalogPriceRow($productId, 16);
            $coefficient = getCoefficientProduct($ID_BLOCK, $productId);

            if ($fallbackPrice && $coefficient > 0) {
                $basePrice = $fallbackPrice;
                $basePrice['PRICE'] = round((float)$fallbackPrice['PRICE'] * $coefficient, 2);
            } else {
                return true;
            }
        }

        if ($applyPlus10) {
            $percent = getBasicSheetSurchargePercent();
            $price = (float)$basePrice['PRICE'] * (1 + $percent / 100);

            return buildCustomOptimalPriceResult(
                $basePrice,
                $price,
                'Цена за метр +' . $percent . '%'
            );
        }

        if ($applyPlus20) {
            $plus20 = fetchCatalogPriceRow($productId, $pricePerMeterPlus20Id);
            $price = $plus20
                ? (float)$plus20['PRICE']
                : (float)$basePrice['PRICE'] * 1.2;
            $source = $plus20 ?: $basePrice;

            return buildCustomOptimalPriceResult(
                $source,
                $price,
                'Цена за метр +20%'
            );
        }

        return buildCustomOptimalPriceResult(
            $basePrice,
            $basePrice['PRICE'],
            'Цена за метр'
        );
    } finally {
        $running = false;
    }
}
