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
$this->setFrameMode(true);?>
<div class="title-h3">Введити запрос для поиска нужного документа</div>
<form action="<?=$arResult["FORM_ACTION"]?>" class="form_document-search">
    <input type="text" name="q" class="form_document-search_input" placeholder="Введите ваш запрос">
    <div class="wrapper_submit-btn">
        <span class="glipf-search"></span>
        <input type="submit" name="s" class="form-search_submit-btn" value="">
    </div>
</form>