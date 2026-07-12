<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 *
 *  _________________________________________________________________________
 * |	Attention!
 * |	The following comments are for system use
 * |	and are required for the component to work correctly in ajax mode:
 * |	<!-- items-container -->
 * |	<!-- pagination-container -->
 * |	<!-- component-end -->
 */

$this->setFrameMode(true);

if(count($arResult['ITEMS'])) :
?>

<p>Уважаемый покупатель! Конечная цена товара увеличивается, если вы выбираете нестандартный метраж. Вы можете покупать товар поштучно по обычной цене.</p>

<table class="product-table" id="product-table">
    <thead>
        <tr>
            <th>Наименование товара</th>
            <?php foreach ($arResult['CATALOG_PRICE'] as $price): ?>
                <th><?=$price['NAME']?></th>
            <?php endforeach; ?>
            <th>Метры</th>
            <th>Штуки</th>
            <th>Купить</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($arResult['ITEMS'] as $arItem): ?>
        <tr data-price="<?=$arItem['RETAIL_PRICE']?>" data-length="<?=$arItem["PROPERTIES"]["DLINA_RASCHET"]["VALUE"]?>">
            <td class="product-table_first-cell">
                <span class="product-item_name"><?=$arItem["NAME"];?></span>
                <span class="product-availability">В наличии на складе.</span>
                <div class="product-item_popup">
                    <div class="product-item_popup-close"><span class="glipf-reset"></span></div>
                    <ul class="product-item_popup-list">
                        <li>
                            <strong>Наименование товара</strong>
                            <span class="product-item_name"><?=$arItem["NAME"];?></span>
                        </li>
                    </ul>
                    <a href="javascript:void(0)" class="main-btn product-item_buy-btn">Купить</a>
                </div>
            </td>
            <?php foreach ($arResult['CATALOG_PRICE'] as $price): ?>
                <td><?=$arItem["ITEM_ALL_PRICES"][0]["PRICES"][$price['CATALOG_GROUP_ID']]['PRINT_PRICE'] ?? 0?></td>
            <?php endforeach; ?>
            <td>
                <input type="number" class="product-table-input" style="max-width: 70px;" min="1" placeholder="0.0" name="meters" value="<?=$arItem["PROPERTIES"]["DLINA_RASCHET"]["VALUE"]?>" data-meters-in-one-piece="<?=$arItem["PROPERTIES"]["DLINA_RASCHET"]["VALUE"]?>">
            </td>
            <td>
                <input type="number" class="product-table-input" style="max-width: 70px;" min="0.1" step="0.1" placeholder="0.0" name="pieces" value="1" data-meters-in-one-piece="<?=$arItem["PROPERTIES"]["DLINA_RASCHET"]["VALUE"]?>">
            </td>
            <td>
                <a href="javascript:void(0)" class="add-to-cart-action product-item_cart-btn main-btn" id="<?=$arItem['ID']?>">
                    <span class="glipf-cart"></span>
                </a>
            </td>
        </tr>
        <?php endforeach;?>
    </tbody>
</table>

<div class="row">
    <div class="col-md-6">
        <div class="product-availability_text">— Наличие товара на складе</div>
        <div class="product-availability_text yellow">— Количество ограничено, уточняйте у менеджера</div>
    </div>
    <div class="col-md-6">
        <?php if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
            <?=$arResult["NAV_STRING"]?>
        <?php endif;?>
    </div>
</div>

<?php endif; ?>

<?php if($arParams["DEPTH_LEVEL"] == "1"): ?>
	<div class="unified-text-section"><?=$arResult['DESCRIPTION'];?></div>
<?php endif; ?>
