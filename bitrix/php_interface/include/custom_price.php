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
        // Листы: тип 17 = ₽/м², QUANTITY в корзине = длина (м) → в Bitrix нужна ₽/пог.м
        $sheetWidth = getProductSheetWidthMeters($productId, $ID_BLOCK);
        $isSheet = $sheetWidth > 0;
        $baseUnitBasket = $isSheet
            ? catalogSheetPriceToBasketMeterPrice($productId, $baseUnit, $ID_BLOCK)
            : $baseUnit;

        // Базовый лист: целые / резанные(+10% при сложной) / неполная(+10%) / резы → цена за пог.м
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
                    $notes = 'С резкой · ' . formatBasketMoney(
                        $isSheet ? basketMeterPriceToCatalogSheetPrice($productId, $unitPrice, $ID_BLOCK) : $unitPrice
                    ) . ($isSheet ? ' ₽/м²' : ' ₽/м');
                }

                return buildCustomOptimalPriceResult(
                    $basePrice,
                    $unitPrice,
                    $notes
                );
            }

            return buildCustomOptimalPriceResult(
                $basePrice,
                $baseUnitBasket,
                $isSheet ? 'Цена за м²' : 'Цена за метр'
            );
        }

        // Трубы/арматура без «0,5 шт»: не кратно полной штуке → вся позиция +20%
        $meterStep = getPipeMeterSurchargeStepMeters($length);
        if (
            !$allowsFreeMeterCutting
            && quantityNeedsMeterSurcharge($quantity, $meterStep)
        ) {
            $plus20 = fetchCatalogPriceRow($productId, $pricePerMeterPlus20Id);
            $surchargeUnit = $plus20
                ? (float)$plus20['PRICE']
                : round($baseUnit * 1.2, 2);
            if ($isSheet) {
                $surchargeUnit = catalogSheetPriceToBasketMeterPrice($productId, $surchargeUnit, $ID_BLOCK);
            }
            $source = $plus20 ?: $basePrice;
            $split = [
                'base_meters' => 0.0,
                'surcharge_meters' => $quantity,
                'base_steps' => 0,
            ];

            return buildCustomOptimalPriceResult(
                $source,
                $surchargeUnit,
                formatPieceSurchargePriceNote(
                    $split,
                    $length,
                    20,
                    $isSheet ? $baseUnit : $baseUnitBasket,
                    $isSheet ? basketMeterPriceToCatalogSheetPrice($productId, $surchargeUnit, $ID_BLOCK) : $surchargeUnit,
                    $isSheet ? basketMeterPriceToCatalogSheetPrice($productId, $surchargeUnit, $ID_BLOCK) : $surchargeUnit
                )
            );
        }

        // Листы «только шт» и прочие: ₽/пог.м = ₽/м² × ширина
        return buildCustomOptimalPriceResult(
            $basePrice,
            $baseUnitBasket,
            $isSheet ? 'Цена за м²' : 'Цена за метр'
        );
    } finally {
        $running = false;
    }
}
