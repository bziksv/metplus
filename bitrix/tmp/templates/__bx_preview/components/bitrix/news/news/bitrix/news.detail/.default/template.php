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

<?if($arParams["DISPLAY_NAME"]!="N" && $arResult["NAME"]):?>
    <h3 class="news-detailed_title"><?=$arResult["NAME"]?></h3>
<?endif;?>

<?if($arParams["DISPLAY_DATE"]!="N" && $arResult["DISPLAY_ACTIVE_FROM"]):?>
    <span class="news-detailed_date"><?=$arResult["DISPLAY_ACTIVE_FROM"]?></span>
<?endif;?>
<p><?echo $arResult["DETAIL_TEXT"];?></p>

<?foreach($arResult['IMAGES'] as $img):?>
<figure class="news-detailed_img">
    <img data-src="<?=$img?>" alt="<?=$arResult["NAME"]?>">
</figure>
<?endforeach;?>
