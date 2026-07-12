<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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
<div class="category-section_mod">
	<div class="container">
		<div class="row">
			 <?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"sections",
	Array(
		"ADD_SECTIONS_CHAIN" => (isset($arParams["ADD_SECTIONS_CHAIN"])?$arParams["ADD_SECTIONS_CHAIN"]:''),
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
		"HIDE_SECTION_NAME" => (isset($arParams["SECTIONS_HIDE_SECTION_NAME"])?$arParams["SECTIONS_HIDE_SECTION_NAME"]:"N"),
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"SECTION_USER_FIELDS" => array("UF_ICON","UF_ICON_CATALOG"),
		"SHOW_PARENT_NAME" => $arParams["SECTIONS_SHOW_PARENT_NAME"],
		"TOP_DEPTH" => $arParams["SECTION_TOP_DEPTH"],
		"VIEW_MODE" => $arParams["SECTIONS_VIEW_MODE"]
	),
$component
);?>
			<div class="category-column">
				<div class="category-item_mod category-item_mod-other">
 <span class="category-icon category-icon_first"></span> <span class="text">Кровельные материалы</span> <a href="http://metprof-vrn.ru/" class="site-link">Перейти на сайт</a>
				</div>
				<div class="category-item_mod category-item_mod-other">
 <span class="category-icon category-icon_second"></span> <span class="text">Инженерная сантехника</span> <a href="https://polimer-vrn.ru/" class="site-link">Перейти на сайт</a>
				</div>
				<div class="category-item_mod category-item_mod-other">
 <span class="category-icon category-icon_third"></span> <span class="text">Художественная ковка</span> <a href="https://vrn-ehk.ru/" class="site-link">Перейти на сайт</a>
				</div>
			</div>
			<div class="category-column">
				<div class="price-list_box">
					<div class="price-list_box-content">
 <img alt="alt" src="/bitrix/templates/metplus/img/static/price-list_img.jpg" data-src="/bitrix/templates/metplus/img/static/sale.jpg">
						</div><div>
 <a href="/prays/price_metall.xls" class="download-price_btn main-btn"><span class="glipf-download"></span>Скачать прайс</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
 <br>