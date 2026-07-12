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
<form action="<?=$arResult["FORM_ACTION"]?>" class="head_form-search mobile-hidden">
    <input type="text" name="q" class="head_form-search_input" value="<?=$_REQUEST['q']?>" placeholder="Введите ваш запрос">
    <div class="wrapper_submit-btn">
        <span class="glipf-search"></span>
        <input type="submit" name="s" class="form-search_submit-btn" value="">
    </div>
</form>
