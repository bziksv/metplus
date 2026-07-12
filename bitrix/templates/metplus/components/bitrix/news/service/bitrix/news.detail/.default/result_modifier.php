<?php
$arResult['ITEMS'] = [];

foreach ($arResult['PROPERTIES']['DESC']['~VALUE'] as $key => $text){
    if(empty($text))
        continue;

    $arResult['ITEMS'][$key]['TEXT'] = $text['TEXT'];
    $arResult['ITEMS'][$key]['IMG'] = (CFile::GetPath($arResult['PROPERTIES']['PICS']['VALUE'][$key])) ?: null;
}

$arResult['IBLOCK']['PICTURE'] = CFile::GetPath($arResult['IBLOCK']['PICTURE']);
