<?php
$res = CIBlock::GetByID($arResult['LAST_ITEM_IBLOCK_ID']);
if($ar_res = $res->GetNext())
    $arResult['BLOCK'] = $ar_res;
