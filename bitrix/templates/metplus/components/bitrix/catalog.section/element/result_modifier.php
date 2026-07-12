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

foreach ($arResult['ITEMS'] as &$arItem) {
    foreach (array_keys($arItem["ITEM_ALL_PRICES"][0]["PRICES"]) as $catalog_price_id) {
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
        $arPrice['NAME'] = $arGroup['NAME'];
    }
}
