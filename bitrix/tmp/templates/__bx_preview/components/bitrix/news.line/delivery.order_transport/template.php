<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="order-transport_section">
    <div class="container">
        <div class="title-h3"><?=$arResult['BLOCK']['NAME']?></div>
        <div class="row">
            <?foreach($arResult["ITEMS"] as $key => $arItem): ?>
            <?
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            ?>
            <div class="col-md-6 order-transport_column">
                <div class="order-transport_item">
                    <div class="order-transport_item-img">
                        <img data-src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>">
                    </div>
                    <span class="order-transport_item-title"><?=$arItem['NAME']?></span>
                    <a href="<?=$arItem['CODE']?>" class="main-btn order-transport_item-btn">Заказать</a>
                </div>
            </div>
            <?endforeach;?>
        </div>
    </div>
</div>

