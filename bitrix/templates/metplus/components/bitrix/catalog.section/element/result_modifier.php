<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Catalog\GroupLangTable;

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 * @var array $arResult
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

$arResult['CATALOG_PRICE'] = [];
$arResult['BULK_WEIGHT_THRESHOLDS'] = [];
$isSquareMeter = isSquareMeterSection($arParams['SECTION_CODE'] ?? '');
$arResult['SHOW_WIDTH_COLUMN'] = $isSquareMeter;
$arResult['SHOW_WEIGHT_COLUMN'] = true;
$arResult['EDITABLE_WEIGHT_COLUMN'] = false; // ввод веса — только у товаров с от 500/1000 кг
$iblockId = (int)($arParams['IBLOCK_ID'] ?? 36);

foreach ($arResult['ITEMS'] as &$arItem) {
    if (empty($arItem['PROPERTIES']['TOLKO_SHT']['VALUE'])) {
        $tolkoSht = getProp($iblockId, $arItem['ID'], 'TOLKO_SHT');
        if ($tolkoSht) {
            $arItem['PROPERTIES']['TOLKO_SHT'] = $tolkoSht;
        }
    }

    if ($arResult['SHOW_WEIGHT_COLUMN'] && empty($arItem['PROPERTIES']['_3_VESPMSAYT']['VALUE'])) {
        $weightProp = getProp($iblockId, $arItem['ID'], '_3_VESPMSAYT');
        if ($weightProp) {
            $arItem['PROPERTIES']['_3_VESPMSAYT'] = $weightProp;
        }
    }

    if (empty($arItem['PROPERTIES']['SHT_M_VES_OT500_KG']['VALUE'])) {
        $weightFrom500Prop = getProp($iblockId, $arItem['ID'], 'SHT_M_VES_OT500_KG');
        if ($weightFrom500Prop) {
            $arItem['PROPERTIES']['SHT_M_VES_OT500_KG'] = $weightFrom500Prop;
        }
    }

    if (empty($arItem['PROPERTIES']['SHT_M_VES_OT_1000_KG']['VALUE'])) {
        $weightFrom1000Prop = getProp($iblockId, $arItem['ID'], 'SHT_M_VES_OT_1000_KG');
        if ($weightFrom1000Prop) {
            $arItem['PROPERTIES']['SHT_M_VES_OT_1000_KG'] = $weightFrom1000Prop;
        }
    }

    if (empty($arItem['PROPERTIES']['TOLKO_SHT_I_0_5_SHT']['VALUE'])) {
        $halfPiecesProp = getProp($iblockId, $arItem['ID'], 'TOLKO_SHT_I_0_5_SHT');
        if ($halfPiecesProp) {
            $arItem['PROPERTIES']['TOLKO_SHT_I_0_5_SHT'] = $halfPiecesProp;
        }
    }

    if (empty($arItem['PROPERTIES']['_5_MARKASAYT_ILI_RAZMER_SETKI']['VALUE'])) {
        $steelGradeProp = getProp($iblockId, $arItem['ID'], '_5_MARKASAYT_ILI_RAZMER_SETKI');
        if ($steelGradeProp) {
            $arItem['PROPERTIES']['_5_MARKASAYT_ILI_RAZMER_SETKI'] = $steelGradeProp;
        }
    }

    $arItem['STEEL_GRADE'] = trim((string)($arItem['PROPERTIES']['_5_MARKASAYT_ILI_RAZMER_SETKI']['VALUE'] ?? ''));
    $arItem['ONLY_PIECES'] = isOnlyPiecesProduct($arItem['PROPERTIES']['TOLKO_SHT']['VALUE'] ?? '');
    $arItem['HALF_PIECES'] = isHalfPiecesProduct($arItem['PROPERTIES']['TOLKO_SHT_I_0_5_SHT']['VALUE'] ?? '');
    $arItem['IS_SHEET'] = isSheetProduct($arItem['PROPERTIES']['SHIRINA_RASCHET']['VALUE'] ?? 0);
    // Лист: шаг кратно 1 м длины. Флаг 0,5 шт на листе → без +10% за кусок (только резы)
    $arItem['BASIC_SHEET'] = $arItem['IS_SHEET'] && !$arItem['ONLY_PIECES'];
    $arItem['FREE_CUTTING_1M'] = $arItem['HALF_PIECES'] && !$arItem['IS_SHEET'];
    $arItem['MIN_BULK_WEIGHT'] = getProductMinBulkWeightKg($arItem['PROPERTIES']);
    $arItem['WEIGHT_FROM_500'] = ($arItem['MIN_BULK_WEIGHT'] === 500);
    $arItem['WEIGHT_FROM_1000'] = ($arItem['MIN_BULK_WEIGHT'] === 1000);
    $arItem['WEIGHT_FROM_BULK'] = $arItem['MIN_BULK_WEIGHT'] !== null;

    if ($arItem['WEIGHT_FROM_BULK']) {
        $arResult['HAS_WEIGHT_FROM_BULK_ROWS'] = true;
        $arResult['EDITABLE_WEIGHT_COLUMN'] = true;
        $arResult['BULK_WEIGHT_THRESHOLDS'][$arItem['MIN_BULK_WEIGHT']] = true;
        if ($arItem['WEIGHT_FROM_500']) {
            $arResult['HAS_WEIGHT_FROM_500_ROWS'] = true;
        }
        if ($arItem['WEIGHT_FROM_1000']) {
            $arResult['HAS_WEIGHT_FROM_1000_ROWS'] = true;
        }
    }

    if ($arItem['ONLY_PIECES']) {
        $arResult['HAS_ONLY_PIECES_ROWS'] = true;
    }

    if ($arItem['HALF_PIECES']) {
        if ($arItem['IS_SHEET']) {
            $arResult['HAS_HALF_PIECES_SHEET_ROWS'] = true;
        } else {
            $arResult['HAS_HALF_PIECES_FREE_ROWS'] = true;
        }
    }

    if ($arItem['BASIC_SHEET']) {
        $arResult['HAS_BASIC_SHEET_ROWS'] = true;
        if (!$arItem['HALF_PIECES']) {
            $arResult['HAS_BASIC_SHEET_PAID_ROWS'] = true;
        }
    }

    $pricePerKg = getProductPricePerKg((int)$arItem['ID'], $iblockId);
    $arItem['PRICE_PER_KG'] = $pricePerKg;
    if ($pricePerKg !== null && class_exists('CCurrencyLang')) {
        $arItem['PRINT_PRICE_PER_KG'] = CCurrencyLang::CurrencyFormat($pricePerKg, 'RUB', true);
    } elseif ($pricePerKg !== null) {
        $arItem['PRINT_PRICE_PER_KG'] = number_format($pricePerKg, 2, '.', ' ') . ' руб.';
    } else {
        $arItem['PRINT_PRICE_PER_KG'] = '—';
    }

    $prices = $arItem['ITEM_ALL_PRICES'][0]['PRICES'] ?? null;
    if (!is_array($prices)) {
        continue;
    }

    foreach (array_keys($prices) as $catalog_price_id) {
        if (!isset($arResult['CATALOG_PRICE'][$catalog_price_id])) {
            $arResult['CATALOG_PRICE'][$catalog_price_id] = [
                'CATALOG_GROUP_ID' => $catalog_price_id,
            ];
        }
    }
}
unset($arItem);

foreach ($arResult['CATALOG_PRICE'] as $id => &$arPrice) {
    $rsGroup = GroupLangTable::getList([
        'filter' => [
            '=LANG' => LANGUAGE_ID,
            '=CATALOG_GROUP_ID' => $id
        ],
        'select' => [
            'NAME',
            'XML_ID' => 'CATALOG_GROUP.XML_ID'
        ]
    ]);

    if ($arGroup = $rsGroup->fetch()) {
        if ($arGroup['XML_ID'] === 'PER_METER_PLUS20' && !shouldShowPlusPriceColumn($arParams['SECTION_CODE'] ?? '')) {
            unset($arResult['CATALOG_PRICE'][$id]);
            continue;
        }

        $arPrice['NAME'] = $arGroup['NAME'];
        $arPrice['XML_ID'] = $arGroup['XML_ID'];
        $arPrice['NAME_HTML'] = formatCatalogPriceHeaderHtml(
            $arGroup['NAME'],
            $arGroup['XML_ID'] ?? '',
            $isSquareMeter,
            $arParams['SECTION_CODE'] ?? ''
        );
    }
}
unset($arPrice);

// «Цена за кг» сразу после «Цена за метр»
$pricePerKgColumn = [
    'CATALOG_GROUP_ID' => 'PRICE_PER_KG',
    'NAME' => 'Цена за кг',
    'XML_ID' => 'PRICE_PER_KG',
    'NAME_HTML' => formatCatalogPriceHeaderHtml('Цена за кг', 'PRICE_PER_KG', $isSquareMeter, $arParams['SECTION_CODE'] ?? ''),
    'IS_PRICE_PER_KG' => true,
];
$orderedPrices = [];
$kgInserted = false;
foreach ($arResult['CATALOG_PRICE'] as $id => $arPrice) {
    $orderedPrices[$id] = $arPrice;
    $xmlId = (string)($arPrice['XML_ID'] ?? '');
    if (!$kgInserted && ($xmlId === 'PER_METER' || (int)$id === 17)) {
        $orderedPrices['PRICE_PER_KG'] = $pricePerKgColumn;
        $kgInserted = true;
    }
}
if (!$kgInserted) {
    if (empty($orderedPrices)) {
        $orderedPrices['PRICE_PER_KG'] = $pricePerKgColumn;
    } else {
        $withKg = [];
        $index = 0;
        foreach ($orderedPrices as $id => $arPrice) {
            $withKg[$id] = $arPrice;
            if ($index === 0) {
                $withKg['PRICE_PER_KG'] = $pricePerKgColumn;
            }
            $index++;
        }
        $orderedPrices = $withKg;
    }
}
$arResult['CATALOG_PRICE'] = $orderedPrices;
