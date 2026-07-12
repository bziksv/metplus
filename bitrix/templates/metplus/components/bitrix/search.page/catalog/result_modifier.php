<?php

if(count($arResult["SEARCH"])>0){

    foreach($arResult["SEARCH"] as &$arItem){

        $arSelect = Array("ID", "IBLOCK_ID", "NAME","PREVIEW_PICTURE", "CATALOG_QUANTITY", "PROPERTY_*");
        $arFilter = Array("IBLOCK_ID" => $arItem['PARAM2'],"ID" => $arItem['ITEM_ID'], "ACTIVE" => "Y");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        if($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();

            $price = priceDiscount($arFields['ID']);

            $arItem['PRICE'] = CurrencyFormat($price['DISCOUNT_PRICE'], 'RUB');
            $arItem['TYPE_METALL'] = $arProps['TYPE_METALL'];
            $arItem['_3_VESPMSAYT'] = $arProps['_3_VESPMSAYT'];
            $arItem['PRICE_CUTTING'] = $arProps['PRICE_CUTTING'];
            $arItem['CATALOG_QUANTITY'] = $arFields['CATALOG_QUANTITY'];
        }
    }
}