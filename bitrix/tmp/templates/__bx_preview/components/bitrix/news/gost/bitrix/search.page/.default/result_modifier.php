<?php
foreach($arResult["SEARCH"] as &$arItem){
    if($arParams['IBLOCK_ID'] && $arItem["ITEM_ID"]){
        $db_props = CIBlockElement::GetProperty($arParams['IBLOCK_ID'], $arItem["ITEM_ID"], array("sort" => "asc"), Array("CODE" => $arParams['LIST_PROPERTY_CODE']));
        while($ar_props = $db_props->Fetch())
            $arItem['PROPERTIES'][$ar_props['CODE']] = $ar_props;

    }
}
