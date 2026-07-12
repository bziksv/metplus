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

$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));
?>

<? foreach ($arResult['SECTIONS'] as &$arSection):
    $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
    $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
    ?>
    <div class="category-column">
        <div class="category-item_other">
            <span class="category-icon <?=$arSection['UF_ICON_CATALOG']?>"></span>
            <a href="<?=$arSection['SECTION_PAGE_URL']?>" class="category-item_other-title"><?=$arSection['NAME']?></a>
            <ul class="category-item_other-list">
                <? foreach($arSection['SUB_SECTION'] as $sub): ?>
                <li><a href="<?=$sub['SECTION_PAGE_URL']?>"><?=$sub['NAME']?></a></li>
                <? endforeach; ?>
            </ul>
        </div>
    </div>
<?endforeach;?>



