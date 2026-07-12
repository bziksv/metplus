<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//You may customize user card fields to display
$arResult['USER_PROPERTY'] = array(
	"UF_DEPARTMENT",
);

//Code below searches for appropriate icon for search index item.
//All filenames should be lowercase.

//1
//Check if index item is information block element with property DOC_TYPE set.
//This property should be type list and we'll take it's values XML_ID as parameter
//iblock_doc_type_<xml_id>.png

//2
//When no such fle found we'll check for section attributes
//iblock_section_<code>.png
//iblock_section_<id>.png
//iblock_section_<xml_id>.png

//3
//Next we'll try to detect icon by "extention".
//where extension is all a-z between dot and end of title
//iblock_type_<iblock type id>_<extension>.png

//4
//If we still failed. Try to match information block attributes.
//iblock_iblock_<code>.png
//iblock_iblock_<id>.png
//iblock_iblock_<xml_id>.png

//5
//If indexed item is section when checkj for
//iblock_section.png
//If it is an element when chek for
//iblock_element.png

//6
//If item belongs to main module (static file)
//when check is done by it's extention
//main_<extention>.png

//7
//For blog module we'll check if icon for post or user exists
//blog_post.png
//blog_user.png

//8, 9 and 10
//forum_message.png
//intranet_user.png
//socialnetwork_group.png

//11
//In case we still failed to find an icon
//<module_id>_default.png

//12
//default.png

CModule::IncludeModule("iblock");

$arIBlocks = array();

$image_path = $this->GetFolder()."/images/";
$abs_path = $_SERVER["DOCUMENT_ROOT"].$image_path;

$arResult["SEARCH"] = array();

if($arResult['query']){

	$arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","DETAIL_PAGE_URL");
	$arFilter = Array("IBLOCK_ID" => $arParams['IBLOCK_ID'], "IBLOCK_TYPE" => $arParams['IBLOCK_TYPE'], "ACTIVE"=>"Y", "?NAME" => $arResult['query']);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize" => 20), $arSelect);
	while($ob = $res->GetNextElement()){
		$arFields = $ob->GetFields();

		if($arFields['IBLOCK_SECTION_ID']){

			$arResult["SEARCH"][$arFields['ID']]['NAME'] = $arFields['NAME'];

			$res_section = CIBlockSection::GetByID($arFields['IBLOCK_SECTION_ID']);
			if($section = $res_section->GetNext())
				$arResult["SEARCH"][$arFields['ID']]['URL'] = $section['SECTION_PAGE_URL'];
		}
	}

}

?>
