<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

foreach ($arResult['ITEMS'] as &$arItem) {
    $dbProps = CIBlockElement::GetProperty($arItem['IBLOCK_ID'], $arItem['ID'], [], []);

    while ($prop = $dbProps->GetNext()) {
        $arItem['PROPERTIES'][$prop['CODE']] = $prop;
    }

    foreach (['IMG_DESKTOP', 'IMG_TABLET', 'IMG_MOBILE'] as $code) {
        $fileId = (int)($arItem['PROPERTIES'][$code]['VALUE'] ?? 0);
        $arItem['SLIDER_IMAGES'][$code] = $fileId > 0 ? CFile::GetPath($fileId) : '';
    }

    if (empty($arItem['SLIDER_IMAGES']['IMG_TABLET'])) {
        $arItem['SLIDER_IMAGES']['IMG_TABLET'] = $arItem['SLIDER_IMAGES']['IMG_DESKTOP'];
    }

    if (empty($arItem['SLIDER_IMAGES']['IMG_MOBILE'])) {
        $arItem['SLIDER_IMAGES']['IMG_MOBILE'] = $arItem['SLIDER_IMAGES']['IMG_TABLET']
            ?: $arItem['SLIDER_IMAGES']['IMG_DESKTOP'];
    }

    $arItem['SLIDER_LINK'] = trim((string)($arItem['PROPERTIES']['LINK']['VALUE'] ?? ''));
}
unset($arItem);
