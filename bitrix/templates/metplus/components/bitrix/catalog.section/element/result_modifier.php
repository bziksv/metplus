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
$isSquareMeter = isSquareMeterSection($arParams['SECTION_CODE'] ?? '');
$arResult['SHOW_WIDTH_COLUMN'] = $isSquareMeter;
$arResult['SHOW_WEIGHT_COLUMN'] = isWeightSection($arParams['SECTION_CODE'] ?? '');
$arResult['EDITABLE_WEIGHT_COLUMN'] = $arResult['SHOW_WEIGHT_COLUMN'];
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
    $arItem['WEIGHT_FROM_500'] = isWeightFrom500Product($arItem['PROPERTIES']['SHT_M_VES_OT500_KG']['VALUE'] ?? '');

    if ($arItem['WEIGHT_FROM_500']) {
        $arResult['HAS_WEIGHT_FROM_500_ROWS'] = true;
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
