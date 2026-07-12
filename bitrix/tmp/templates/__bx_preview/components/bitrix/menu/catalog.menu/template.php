<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
<ul class="catalog-menu">
<?foreach($arResult["MENU_STRUCTURE"] as $itemID => $arColumns):?>
    <?$existPictureDescColomn = ($arResult["ALL_ITEMS"][$itemID]["PARAMS"]["picture_src"] || $arResult["ALL_ITEMS"][$itemID]["PARAMS"]["description"]) ? true : false;?>
    <li class="catalog-menu_item">
        <a href="<?=$arResult["ALL_ITEMS"][$itemID]["LINK"]?>">
            <span class="menu-icon <?=$arResult["ALL_ITEMS"][$itemID]["PARAMS"]["ICON_MENU"]?>"></span>
            <?=mb_strtoupper($arResult["ALL_ITEMS"][$itemID]["TEXT"])?>
        </a>
    <?if (is_array($arColumns) && count($arColumns) > 0):?>
        <?foreach($arColumns as $key=>$arRow):?>
            <ul class="catalog-submenu">
            <?foreach($arRow as $itemIdLevel_2=>$arLevel_3):?>
                <li>
                    <a href="<?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["LINK"]?>">
                        <?=$arResult["ALL_ITEMS"][$itemIdLevel_2]["TEXT"]?>
                    </a>
                </li>
            <?endforeach;?>
            </ul>
        <?endforeach;?>
    <?endif?>
    </li>
<?endforeach;?>
</ul>
