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
?><div class="category-section">
    <div class="container">
        <ul class="category-list">
            <? foreach ($arResult['SECTIONS'] as &$arSection):
                $this->AddEditAction($arSection['ID'], $arSection['EDIT_LINK'], $strSectionEdit);
                $this->AddDeleteAction($arSection['ID'], $arSection['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);
                ?>
                <li class="category-item">
                    <a href="<?=$arSection['SECTION_PAGE_URL']?>"><span class="category-icon <?=$arSection['UF_ICON']?>"></span><?=$arSection['NAME']?></a>
                </li>
            <?endforeach;?>
            <li class="category-item_mod">
                <div class="category-item_mod-content">
                    <span class="category-icon category-icon_thirst"></span>
                    <span class="text">Инженерная <br>сантехника</span>
                    <a href="https://polimer-vrn.ru/" class="site-link">Перейти на сайт</a>
                </div>
            </li>
            <li class="category-item_mod">
                <div class="category-item_mod-content">
                    <span class="category-icon category-icon_fifth"></span>
                    <span class="text">Кровельные <br>материалы</span>
                    <a href="http://metprof-vrn.ru/" class="site-link">Перейти на сайт</a>
                </div>
            </li>
            <li class="category-item_mod">
                <div class="category-item_mod-content">
                    <span class="category-icon category-icon_sixteen"></span>
                    <span class="text">Художественная <br>ковка</span>
                    <a href="https://vrn-ehk.ru/" class="site-link">Перейти на сайт</a>
                </div>
            </li>
        </ul>
		<a href="/prays/price_metall.xls" class="download-price_btn main-btn"><span class="glipf-download"></span>Скачать прайс</a>
    </div>
</div>