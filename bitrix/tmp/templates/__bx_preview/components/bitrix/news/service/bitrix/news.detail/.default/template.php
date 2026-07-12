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


<? foreach ($arResult['ITEMS'] as $key => $arItem): ?>
<div class="row services-detailed_box">
    <div class="left-column <? if($key % 2): ?>order-md-2<? endif;?>">
        <div class="services-detailed_img">
            <img data-src="<?=$arItem['IMG']?>" alt="<?=$arResult['NAME']?>">
        </div>
    </div>
    <div class="right-column"><?=$arItem['TEXT']?></div>
</div>
<?endforeach;?>

<div class="services-detailed_footer">
    <span class="text">Узнайте стоимость всех услуг</span>
    <a href="<?=CFile::GetPath($arResult['PROPERTIES']['PRICE']['VALUE'])?>" class="download-price_btn main-btn" download><span class="glipf-download"></span>Скачать прайс</a>
</div>