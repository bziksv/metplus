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
<div class="history-company_section">
    <div class="container">
        <div class="title-h3">
            <?=$arResult['BLOCK']['NAME']?>
        </div>
        <div class="row">
            <div class="col-xl-4">
                <div class="history-company_blue-box">
                    <?=$arResult['BLOCK']['DESCRIPTION']?>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="history-company_slider">
                    <?foreach($arResult["ITEMS"] as $key => $arItem): ?>
                    <?
                    $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <div class="history-company_slide">
                        <div class="history-company_slide-left">
                            <div class="history-company_slide-year">
                                <?=FormatDate("Y", MakeTimeStamp($arItem["ACTIVE_FROM"]))?> год
                            </div>
                            <span class="history-company_slide-title"><?=$arItem['NAME']?></span>
                            <div class="history-company_slide-nav">
                                <span class="slide-arrow slide-prev">Назад</span> <span class="slide-arrow slide-next">Далее</span>
                            </div>
                        </div>
                        <div class="history-company_slide-right">
                            <?=$arItem['PREVIEW_TEXT']?>
                        </div>
                    </div>
                    <?endforeach;?>
                </div>
                <ul class="year-slider">
                    <?foreach($arResult["ITEMS"] as $key => $arItem): ?>
                    <li data-type="<?=$key?>" class="<?if(!$key):?>is-active<?endif;?>">
                        <span class="year-slide_dots"></span>
                        <?=FormatDate("Y", MakeTimeStamp($arItem["ACTIVE_FROM"]))?>
                    </li>
                    <?endforeach;?>
                </ul>
            </div>
        </div>
    </div>
</div>


