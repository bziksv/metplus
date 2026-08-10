<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;
$aMenuLinksExt = array();

if(CModule::IncludeModule('iblock'))
{
	$catalogIblockId = 36;
	$arIBlock = CIBlock::GetArrayByID($catalogIblockId);

	if (is_array($arIBlock) && $arIBlock["ACTIVE"] == "Y")
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("iblock_id_".$catalogIblockId);

		$sectionPageUrl = $arIBlock['SECTION_PAGE_URL'] ?: '#SITE_DIR#/catalog/#SECTION_CODE#/';
		$detailPageUrl = $arIBlock['DETAIL_PAGE_URL'] ?: '#SITE_DIR#/catalog/#SECTION_CODE#/#ELEMENT_CODE#/';

		$aMenuLinksExt = $APPLICATION->IncludeComponent("prime:menu.sections", "bootstrap_v4", array(
			"IS_SEF" => "Y",
			"SEF_BASE_URL" => "",
			"SECTION_PAGE_URL" => $sectionPageUrl,
			"DETAIL_PAGE_URL" => $detailPageUrl,
			"IBLOCK_TYPE" => $arIBlock['IBLOCK_TYPE_ID'],
			"IBLOCK_ID" => $catalogIblockId,
			"DEPTH_LEVEL" => "3",
			"CACHE_TYPE" => "N",
		), false, Array('HIDE_ICONS' => 'Y'));
	}

	if(defined("BX_COMP_MANAGED_CACHE"))
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("iblock_id_new");
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>
