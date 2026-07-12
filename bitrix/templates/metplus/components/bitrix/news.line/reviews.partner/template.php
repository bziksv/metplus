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
<div class="partners-section">
    <div class="container">
        <div class="title-h3"><?=$arResult['BLOCK']['NAME']?></div>
        <div class="row wrapper_partner-item">
            <?foreach($arResult["ITEMS"] as $key => $arItem): ?>
            <?
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="partner-item">
                    <span class="partner-item_logo">
                        <img data-src="<?=$arItem['PREVIEW_PICTURE']['SRC']?>" alt="<?=$arItem['NAME']?>">
                    </span>
                    <p><?=$arItem['PREVIEW_TEXT']?></p>
                </div>
            </div>
            <?endforeach;?>
        </div>
    </div>
</div>


