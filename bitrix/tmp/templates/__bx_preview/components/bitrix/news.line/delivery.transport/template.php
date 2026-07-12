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
<div class="recommended-transportation_content">
    <div class="title-h3"><?=$arResult['BLOCK']['NAME']?></div>
    <div class="row">
        <?foreach($arResult["ITEMS"] as $key => $arItem): ?>
        <?
        $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
        $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
        ?>
        <div class="col-md-4 transport-column">
            <div class="transport-item">
                <span class="transport-item_title"><?=$arItem['NAME']?> </span>
                <div class="transport-item_img transport-item_img-mod">
                    <img data-src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>">
                </div>
            </div>
        </div>
        <?endforeach;?>
    </div>
    <div class="recommended-transportation_footer">
        <div class="left-column">
            <?=$arResult['BLOCK']['DESCRIPTION']?>
        </div>
        <div class="right-column">
            <div class="price-list_box">
                <div class="price-list_box-content">
                    <img alt="alt" src="<?=SITE_TEMPLATE_PATH?>/img/static/price-list_img.jpg">
					<a href="/prays/prays_dostavka.pdf" class="download-price_btn main-btn"><span class="glipf-download"></span>Скачать прайс</a>
                </div>
            </div>
        </div>
    </div>
</div>

