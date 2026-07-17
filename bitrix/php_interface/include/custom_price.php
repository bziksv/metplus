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

        $allowsFreeMeterCutting = productAllowsFreeMeterCutting($productId, $ID_BLOCK);

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

        $baseUnit = (float)$basePrice['PRICE'];

        // Базовый лист: целые / резанные(+10% при сложной) / неполная(+10%) / резы → цена за м
        if ($isBasicSheet) {
            $breakdown = buildBasicSheetPositionBreakdown($productId, $quantity, $ID_BLOCK);
            if ($breakdown) {
                $unitPrice = (float)$breakdown['BLENDED_PRICE'];
                $cutsFee = (float)($breakdown['CUTS_FEE'] ?? 0);
                $grandTotal = (float)($breakdown['GRAND_TOTAL'] ?? 0);
                // оплата резов входит в стоимость позиции (как в «Итого» мастера)
                if ($cutsFee > 0.0001 && $quantity > 0 && $grandTotal > 0) {
                    $unitPrice = round($grandTotal / $quantity, 2);
                }
                $notes = (string)$breakdown['NOTE'];
                if ($cutsFee > 0.0001) {
                    $notes = 'С резкой · ' . formatBasketMoney($unitPrice) . ' ₽/м';
                }

                return buildCustomOptimalPriceResult(
                    $basePrice,
                    $unitPrice,
                    $notes
                );
            }

            return buildCustomOptimalPriceResult(
                $basePrice,
                $baseUnit,
                'Цена за метр'
            );
        }

        // Трубы/арматура: не кратно 0,5 шт → +20% только на кусок
        if (
            !$allowsFreeMeterCutting
            && quantityNeedsMeterSurcharge($quantity, $half)
        ) {
            $split = splitQuantityForPieceSurcharge($quantity, $half);
            $plus20 = fetchCatalogPriceRow($productId, $pricePerMeterPlus20Id);
            $surchargeUnit = $plus20
                ? (float)$plus20['PRICE']
                : round($baseUnit * 1.2, 2);
            $source = $plus20 ?: $basePrice;

            $price = blendMeterPriceWithPieceSurcharge(
                $baseUnit,
                $surchargeUnit,
                $split['base_meters'],
                $split['surcharge_meters']
            );

            return buildCustomOptimalPriceResult(
                $source,
                $price,
                formatPieceSurchargePriceNote($split, $length, 20, $baseUnit, $surchargeUnit, $price)
            );
        }

        return buildCustomOptimalPriceResult(
            $basePrice,
            $baseUnit,
            'Цена за метр'
        );
    } finally {
        $running = false;
    }
}
