<?php

function getProp($ID_BLOCK, $ID, $CODE)
{
    if (CModule::IncludeModule('iblock')) {
        $db_props = CIBlockElement::GetProperty($ID_BLOCK, $ID, array("sort" => "asc"), array("CODE" => $CODE));
        if ($ar_props = $db_props->Fetch()) {
            return $ar_props;
        }
    }
}

function getPropVal($ID_BLOCK, $ID, $CODE)
{
    if ($ar_props = getProp($ID_BLOCK, $ID, $CODE)) {
        return $ar_props['VALUE'];
    }

    return null;
}

function getCoefficientProduct($ID_BLOCK, $ID)
{
    return floatval(getPropVal($ID_BLOCK, $ID, 'KOEFFITSENT_RASCHET'));
}

function getLengthProduct($ID_BLOCK, $ID)
{
    return floatval(getPropVal($ID_BLOCK, $ID, 'DLINA_RASCHET'));
}

function isCustomPrice($ID_BLOCK, $ID)
{
    return getCoefficientProduct($ID_BLOCK, $ID) > 0 && getLengthProduct($ID_BLOCK, $ID) > 0;
}

/**
 * @param $ID
 * @return array
 */
function getProductCuttingServices($ID)
{
    $props = [];
    $codes = ['REZKA_GAZ_RASCHET', 'REZKA_ABRAZIV_RASCHET'];

    if (!CModule::IncludeModule('iblock')) {
        return [];
    }

    $res = CIBlockElement::GetByID($ID);
    if($ar_res = $res->GetNext()) {
        foreach ($codes as $code) {
            $prop = getProp($ar_res['IBLOCK_ID'], $ID, $code);
            if (!empty($prop['CODE'])) {
                $props[] = $prop;
            }
        }
    }

    return $props;
}

/**
 * Человекочитаемое название типа резки по коду свойства.
 */
function getCuttingServiceHumanName($code, $fallback = '')
{
    $map = [
        'REZKA_GAZ_RASCHET' => 'Газовая резка',
        'REZKA_ABRAZIV_RASCHET' => 'Абразивная резка',
    ];

    $code = (string)$code;
    if (isset($map[$code])) {
        return $map[$code];
    }

    $fallback = trim((string)$fallback);
    if ($fallback !== '') {
        $fallback = str_replace(['_', 'Расчет', 'расчет'], [' ', '', ''], $fallback);
        $fallback = preg_replace('/\s+/u', ' ', $fallback);
        return trim($fallback) !== '' ? trim($fallback) : $code;
    }

    return $code;
}

function isSquareMeterSection($sectionCode)
{
    return in_array($sectionCode, ['list_g_k'], true);
}

function shouldShowPlusPriceColumn($sectionCode)
{
    return isSquareMeterSection($sectionCode);
}

function isWeightSection($sectionCode)
{
    return in_array($sectionCode, ['stal_armaturnaya_a3'], true);
}

function isOnlyPiecesProduct($value)
{
    if ($value === null || $value === '') {
        return false;
    }

    $normalized = mb_strtolower(trim((string)$value));

    return in_array($normalized, ['да', 'y', 'yes', '1', 'true'], true);
}

function isWeightFrom500Product($value)
{
    return isOnlyPiecesProduct($value);
}

function getMinBulkWeightKg()
{
    return 500;
}

function getWeightFrom500TipText($minBulkWeight = null)
{
    $min = $minBulkWeight ?? getMinBulkWeightKg();

    return 'До ' . $min . ' кг — заказ только целыми штуками. От ' . $min . ' кг — можно указать вес с точностью до грамм.';
}

function getWeightFrom500PiecesTipText($minBulkWeight = null)
{
    $min = $minBulkWeight ?? getMinBulkWeightKg();

    return 'Вес считается из штук. Чтобы заказать от ' . $min . ' кг по весу — кликните в поле и введите значение.';
}

function getWeightFrom500BulkTipText($minBulkWeight = null)
{
    $min = $minBulkWeight ?? getMinBulkWeightKg();

    return 'Заказ по весу от ' . $min . ' кг. Метры и штуки пересчитываются автоматически.';
}

function formatBasketQtyNumber($value, $decimals = 2)
{
    if ($value === null || $value === '') {
        return '';
    }

    $formatted = number_format((float)$value, $decimals, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
}

function getBasketItemQuantityDisplay($productId, $metersQuantity)
{
    $iblockId = 36;
    $metersQty = (float)$metersQuantity;
    $lengthPerPiece = floatval(getPropVal($iblockId, $productId, 'DLINA_RASCHET'));
    $weightPerMeter = floatval(getPropVal($iblockId, $productId, '_3_VESPMSAYT'));
    $width = floatval(getPropVal($iblockId, $productId, 'SHIRINA_RASCHET'));

    $pieces = $lengthPerPiece > 0 ? $metersQty / $lengthPerPiece : null;
    $weight = $weightPerMeter > 0 ? $metersQty * $weightPerMeter : null;

    if ($width > 0) {
        $areaValue = $metersQty * $width;
        $areaUnit = 'м²';
    } else {
        $areaValue = $metersQty;
        $areaUnit = 'м';
    }

    $piecesFormatted = '';
    if ($pieces !== null) {
        $piecesFormatted = abs($pieces - round($pieces)) < 0.01
            ? (string)(int)round($pieces)
            : formatBasketQtyNumber($pieces, 2);
    }

    return [
        'PIECES' => $piecesFormatted,
        'AREA' => formatBasketQtyNumber($areaValue, $width > 0 ? 3 : 2),
        'AREA_UNIT' => $areaUnit,
        'WEIGHT' => $weight !== null ? formatBasketQtyNumber($weight, 3) : '',
    ];
}

function getSquareMeterSurchargePercent($sectionCode)
{
    if ($sectionCode === 'list_g_k') {
        return 10;
    }

    return null;
}

function formatCatalogPriceHeaderHtml($priceName, $xmlId, $isSquareMeter, $sectionCode = '')
{
    if (!$isSquareMeter) {
        return htmlspecialcharsbx($priceName);
    }

    $isPlusPrice = strpos($priceName, '+') !== false || $xmlId === 'PER_METER_PLUS20';
    $surcharge = $isPlusPrice ? getSquareMeterSurchargePercent($sectionCode) : null;
    $coef = $surcharge
        ? '<span class="price-header__coef">+' . $surcharge . '%</span>'
        : '';

    return '<span class="price-header">Цена за м<sup>2</sup>' . $coef . '</span>';
}