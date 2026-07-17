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
            <th class="product-table_col-steel">Марка стали</th>
            <?php foreach ($arResult['CATALOG_PRICE'] as $price): ?>
                <th class="product-table_col-price"><?=$price['NAME_HTML'] ?? htmlspecialcharsbx($price['NAME'])?></th>
            <?php endforeach; ?>
            <?php if (!empty($arResult['SHOW_WEIGHT_COLUMN'])): ?>
            <th class="product-table_col-qty"<?=!empty($arResult['EDITABLE_WEIGHT_COLUMN']) ? ' data-tip="Укажите вес заказа в кг — метры и штуки пересчитаются автоматически"' : ' data-tip="Вес погонного метра, кг"'?>><?=!empty($arResult['EDITABLE_WEIGHT_COLUMN']) ? 'Вес, кг' : 'Вес'?></th>
            <?php endif; ?>
            <th class="product-table_col-qty">Длина (метры)</th>
            <?php if (!empty($arResult['SHOW_WIDTH_COLUMN'])): ?>
            <th class="product-table_col-qty">Ширина (метры)</th>
            <?php endif; ?>
            <?php if (!empty($arResult['HAS_BASIC_SHEET_ROWS'])): ?>
            <th class="product-table_col-qty" data-tip="Кратно 1 м длины листа">м²</th>
            <?php endif; ?>
            <th class="product-table_col-qty">Штуки</th>
            <th>Купить</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($arResult['ITEMS'] as $arItem):
            $onlyPieces = !empty($arItem['ONLY_PIECES']);
            $halfPieces = !empty($arItem['HALF_PIECES']);
            $isSheet = !empty($arItem['IS_SHEET']);
            $basicSheet = !empty($arItem['BASIC_SHEET']);
            $metersInPiece = $arItem['PROPERTIES']['DLINA_RASCHET']['VALUE'];
            $widthValue = $arItem['PROPERTIES']['SHIRINA_RASCHET']['VALUE'];
            $weightPerMeter = getProductWeightPerMeterKg((int)$arItem['ID'], (int)$arItem['IBLOCK_ID']);
            $weightPerPiece = getProductPieceWeightKg((int)$arItem['ID'], (int)$arItem['IBLOCK_ID']);
            $initialWeightKg = $weightPerPiece > 0 ? round($weightPerPiece, 2) : '';
            $weightValue = $weightPerMeter > 0 ? (string)$weightPerMeter : '';
            $piecesStep = $onlyPieces || $basicSheet ? '1' : '0.1';
            $piecesMin = $onlyPieces || $basicSheet ? '1' : '0.1';
            $lockedTitle = 'Продажа только целыми штуками. Метры и ширина заданы производителем.';
            $basicSheetTip = ($basicSheet && $halfPieces)
                ? getBasicSheetHalfPiecesCuttingTipText()
                : getBasicSheetCuttingTipText();
            $basicSheetDimensionsTip = getBasicSheetDimensionsTipText();
            $sheetAreaPerPiece = ($basicSheet && $metersInPiece && $widthValue)
                ? round(floatval($metersInPiece) * floatval($widthValue), 3)
                : 0;
            $basicSheetSteps = ($basicSheet && $metersInPiece && $widthValue)
                ? getBasicSheetWidthMeterSteps($metersInPiece, $widthValue)
                : null;
            if ($basicSheetSteps) {
                $piecesStep = $basicSheetSteps['PIECE_STEP'];
                $piecesMin = $basicSheetSteps['PIECE_STEP'];
            }
            $halfPiecesCuttingTip = ($basicSheet && $halfPieces)
                ? getBasicSheetHalfPiecesCuttingTipText()
                : ($isSheet ? getSheetCuttingTipText() : getFreeCuttingTipText());
            $weightEditable = !empty($arResult['EDITABLE_WEIGHT_COLUMN']) && $weightPerMeter > 0;
            $weightFrom500 = !empty($arItem['WEIGHT_FROM_500']);
            $minBulkWeight = getMinBulkWeightKg();
            $weightFrom500Tip = getWeightFrom500TipText($minBulkWeight);
            if ($weightFrom500) {
                $piecesStep = '1';
                $piecesMin = '1';
            }
        ?>
        <tr data-price="<?=$arItem['RETAIL_PRICE']?>" data-length="<?=$metersInPiece?>" data-width="<?=$widthValue?>" data-only-pieces="<?=$onlyPieces ? '1' : '0'?>" data-half-pieces="<?=$halfPieces ? '1' : '0'?>" data-basic-sheet="<?=$basicSheet ? '1' : '0'?>"<?=$onlyPieces ? ' class="product-table_row--only-pieces"' : ''?><?=$weightEditable ? ' data-weight-editable="1" data-weight-per-meter="' . htmlspecialcharsbx($weightPerMeter) . '"' : ''?><?=$weightFrom500 ? ' data-weight-from-500="1" data-min-bulk-weight="' . $minBulkWeight . '" data-weight-per-piece="' . htmlspecialcharsbx($weightPerPiece) . '" data-order-mode="pieces"' : ''?>>
            <td class="product-table_first-cell">
                <button type="button" class="product-availability-marker" title="В наличии на складе." aria-label="В наличии на складе.">
                    <span class="product-availability-marker__tip" aria-hidden="true">В наличии на складе.</span>
                </button>
                <span class="product-item_name"><?=$arItem["NAME"];?></span>
                <?php if ($onlyPieces): ?>
                <button type="button" class="product-hint product-hint--lock" data-tip="<?=$lockedTitle?>" aria-label="<?=$lockedTitle?>">
                    <span class="product-hint__icon--lock" aria-hidden="true"></span>
                </button>
                <?php elseif ($weightFrom500): ?>
                <button type="button" class="product-hint product-hint--bulk" data-tip="<?=$weightFrom500Tip?>" aria-label="<?=$weightFrom500Tip?>">
                    <span class="product-hint__label">500+</span>
                </button>
                <?php elseif ($basicSheet && $halfPieces): ?>
                <button type="button" class="product-hint product-hint--cut-free" data-tip="<?=$basicSheetTip?>" aria-label="<?=$basicSheetTip?>">
                    <span class="product-hint__label">1м</span>
                </button>
                <?php elseif ($basicSheet): ?>
                <button type="button" class="product-hint product-hint--cut-paid" data-tip="<?=$basicSheetTip?>" aria-label="<?=$basicSheetTip?>">
                    <span class="product-hint__label">1м</span>
                </button>
                <?php elseif ($halfPieces): ?>
                <button type="button" class="product-hint product-hint--cut-free" data-tip="<?=$halfPiecesCuttingTip?>" aria-label="<?=$halfPiecesCuttingTip?>">
                    <span class="product-hint__label">1м</span>
                </button>
                <?php endif; ?>
                <span class="product-availability">В наличии на складе.</span>
                <div class="product-item_popup">
                    <div class="product-item_popup-close"><span class="glipf-reset"></span></div>
                    <ul class="product-item_popup-list">
                        <li>
                            <strong>Наименование товара</strong>
                            <span class="product-item_name"><?=$arItem["NAME"];?></span>
                        </li>
                        <?php if (!empty($arItem['STEEL_GRADE'])): ?>
                        <li>
                            <strong>Марка стали</strong>
                            <span><?=htmlspecialcharsbx($arItem['STEEL_GRADE'])?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <a href="javascript:void(0)" class="main-btn product-item_buy-btn">Купить</a>
                </div>
            </td>
            <td class="product-table_cell-steel"><?=htmlspecialcharsbx($arItem['STEEL_GRADE'] ?? '')?></td>
            <?php foreach ($arResult['CATALOG_PRICE'] as $price): ?>
                <td><?=$arItem["ITEM_ALL_PRICES"][0]["PRICES"][$price['CATALOG_GROUP_ID']]['PRINT_PRICE'] ?? 0?></td>
            <?php endforeach; ?>
            <?php if (!empty($arResult['SHOW_WEIGHT_COLUMN'])): ?>
            <td class="product-table_cell-qty product-table_cell-weight">
                <?php if ($weightEditable): ?>
                <div class="product-table_field"<?=$weightFrom500 ? ' data-tip="' . htmlspecialcharsbx(getWeightFrom500PiecesTipText($minBulkWeight)) . '"' : ' data-tip="Вес шт = Коэф. × Ширина × Длина · ' . $weightPerMeter . ' кг/м"'?>>
                    <input type="number" class="product-table-input product-table-input_weight" min="0.01" step="<?=$weightFrom500 ? '0.001' : '0.01'?>" placeholder="0" name="weight_kg" value="<?=$initialWeightKg?>" data-weight-per-meter="<?=$weightPerMeter?>"<?=$weightFrom500 ? ' data-tip-pieces="' . htmlspecialcharsbx(getWeightFrom500PiecesTipText($minBulkWeight)) . '" data-tip-bulk="' . htmlspecialcharsbx(getWeightFrom500BulkTipText($minBulkWeight)) . '"' : ''?>>
                </div>
                <?php else: ?>
                <span data-tip="Вес погонного метра, кг"><?=htmlspecialcharsbx($weightValue)?></span>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <td class="product-table_cell-qty<?=($onlyPieces || $basicSheet) ? ' product-table_cell--locked' : ''?>">
                <?php if ($onlyPieces || $basicSheet): ?>
                <div class="product-table_field product-table_field--restricted" data-tip="<?=$basicSheet ? $basicSheetDimensionsTip : $lockedTitle?>">
                    <?php if ($onlyPieces): ?>
                    <span class="product-hint__icon--lock" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="product-table_field-value"><?=$metersInPiece?></span>
                    <input type="hidden" name="meters" value="<?=$metersInPiece?>" data-meters-in-one-piece="<?=$metersInPiece?>">
                </div>
                <?php else: ?>
                <div class="product-table_field">
                    <input type="number" class="product-table-input" min="1" step="<?=$isSheet ? '0.1' : '1'?>" placeholder="0" name="meters" value="<?=$metersInPiece?>" data-meters-in-one-piece="<?=$metersInPiece?>">
                </div>
                <?php endif; ?>
            </td>
            <?php if (!empty($arResult['SHOW_WIDTH_COLUMN'])): ?>
            <td class="product-table_cell-qty<?=($onlyPieces || $basicSheet) ? ' product-table_cell--locked' : ''?>">
                <?php if ($onlyPieces || $basicSheet): ?>
                <div class="product-table_field product-table_field--restricted" data-tip="<?=$basicSheet ? $basicSheetDimensionsTip : $lockedTitle?>">
                    <?php if ($onlyPieces): ?>
                    <span class="product-hint__icon--lock" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="product-table_field-value"><?=$widthValue?></span>
                    <input type="hidden" name="width" value="<?=$widthValue?>" data-width-default="<?=$widthValue?>">
                </div>
                <?php else: ?>
                <div class="product-table_field">
                    <input type="number" class="product-table-input product-table-input_width" min="0.1" step="0.1" placeholder="0.0" name="width" value="<?=$widthValue?>" data-width-default="<?=$widthValue?>">
                </div>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <?php if (!empty($arResult['HAS_BASIC_SHEET_ROWS'])): ?>
            <td class="product-table_cell-qty<?=$basicSheet ? '' : ' product-table_cell--locked'?>">
                <?php if ($basicSheet && $basicSheetSteps): ?>
                <div class="product-table_field" data-tip="<?=htmlspecialcharsbx(getBasicSheetPiecesTipText())?>">
                    <input type="number" class="product-table-input product-table-input_area" min="<?=$basicSheetSteps['AREA_STEP']?>" step="<?=$basicSheetSteps['AREA_STEP']?>" placeholder="0" name="area_m2" value="<?=$basicSheetSteps['AREA_STEP']?>" data-area-step="<?=$basicSheetSteps['AREA_STEP']?>" data-full-area="<?=$basicSheetSteps['FULL_AREA']?>">
                </div>
                <?php else: ?>
                <span class="product-table_field-value">—</span>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <td class="product-table_cell-qty">
                <div class="product-table_field">
                    <input type="number" class="product-table-input" min="<?=$piecesMin?>" step="<?=$piecesStep?>" placeholder="0" name="pieces" value="<?=$basicSheetSteps ? $basicSheetSteps['PIECE_STEP'] : 1?>" data-meters-in-one-piece="<?=$metersInPiece?>">
                </div>
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
        <div class="product-hint-legend product-hint-legend--stock">Наличие товара на складе</div>
        <div class="product-hint-legend product-hint-legend--limited">Количество ограничено, уточняйте у менеджера</div>
        <?php if (!empty($arResult['HAS_WEIGHT_FROM_500_ROWS'])): ?>
        <div class="product-hint-legend product-hint-legend--badge product-hint-legend--bulk" data-tip="<?=htmlspecialcharsbx(getWeightFrom500TipText())?>">
            <span class="product-hint-legend__badge product-hint product-hint--bulk" aria-hidden="true"><span class="product-hint__label">500+</span></span>
            <span class="product-hint-legend__text">От 500 кг можно заказать по весу</span>
        </div>
        <?php endif; ?>
        <?php if (!empty($arResult['EDITABLE_WEIGHT_COLUMN']) && empty($arResult['HAS_WEIGHT_FROM_500_ROWS'])): ?>
        <div class="product-hint-legend product-hint-legend--neutral">Вес в кг: метры = кг ÷ вес пог.м, штуки = метры ÷ длина прутка</div>
        <?php endif; ?>
        <?php if (!empty($arResult['HAS_ONLY_PIECES_ROWS'])): ?>
        <div class="product-hint-legend product-hint-legend--badge product-hint-legend--lock" data-tip="Продажа только целыми штуками. Метры и ширина заданы производителем.">
            <span class="product-hint-legend__badge product-hint product-hint--lock" aria-hidden="true"><span class="product-hint__icon--lock"></span></span>
            <span class="product-hint-legend__text">Только целыми штуками</span>
        </div>
        <?php endif; ?>
        <?php if (!empty($arResult['HAS_BASIC_SHEET_PAID_ROWS'])): ?>
        <div class="product-hint-legend product-hint-legend--badge product-hint-legend--cut-paid" data-tip="<?=htmlspecialcharsbx(getBasicSheetCuttingTipText())?>">
            <span class="product-hint-legend__badge product-hint product-hint--cut-paid" aria-hidden="true"><span class="product-hint__label">1шт</span></span>
            <span class="product-hint-legend__text">Заказ в шт или м² · кратно 1 м длины · +10% на кусок / резанные</span>
        </div>
        <?php endif; ?>
        <?php if (!empty($arResult['HAS_HALF_PIECES_SHEET_ROWS'])): ?>
        <div class="product-hint-legend product-hint-legend--badge product-hint-legend--cut-free" data-tip="<?=htmlspecialcharsbx(getBasicSheetHalfPiecesCuttingTipText())?>">
            <span class="product-hint-legend__badge product-hint product-hint--cut-free" aria-hidden="true"><span class="product-hint__label">1м</span></span>
            <span class="product-hint-legend__text"><?=htmlspecialcharsbx(getHalfPiecesCuttingLegendText(true))?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($arResult['HAS_HALF_PIECES_FREE_ROWS'])): ?>
        <div class="product-hint-legend product-hint-legend--badge product-hint-legend--cut-free" data-tip="<?=htmlspecialcharsbx(getFreeCuttingTipText())?>">
            <span class="product-hint-legend__badge product-hint product-hint--cut-free" aria-hidden="true"><span class="product-hint__label">1м</span></span>
            <span class="product-hint-legend__text"><?=htmlspecialcharsbx(getHalfPiecesCuttingLegendText(false))?></span>
        </div>
        <?php endif; ?>
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
