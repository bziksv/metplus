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
            <th class="product-table_col-steel"><?=formatCatalogColumnHeaderHtml('Марка стали', 'свойство Марка (сайт)')?></th>
            <?php foreach ($arResult['CATALOG_PRICE'] as $price): ?>
                <th class="product-table_col-price"><?=$price['NAME_HTML'] ?? htmlspecialcharsbx($price['NAME'])?></th>
            <?php endforeach; ?>
            <?php if (!empty($arResult['SHOW_WEIGHT_COLUMN'])): ?>
            <th class="product-table_col-qty" data-tip="Вес заказа в кг. Ввод доступен при свойствах «шт, м, вес от 500/1000 кг»"><?=formatCatalogColumnHeaderHtml(
                'Вес, кг',
                'Коэффициент_Расчет × Ширина_Расчет × Длина_Расчет'
            )?></th>
            <?php endif; ?>
            <th class="product-table_col-qty"><?=formatCatalogColumnHeaderHtml('Длина (метры)', 'метры = штуки × Длина_Расчет')?></th>
            <?php if (!empty($arResult['SHOW_WIDTH_COLUMN'])): ?>
            <th class="product-table_col-qty"><?=formatCatalogColumnHeaderHtml('Ширина (метры)', 'свойство Ширина_Расчет')?></th>
            <?php endif; ?>
            <?php if (!empty($arResult['HAS_BASIC_SHEET_ROWS'])): ?>
            <th class="product-table_col-qty" data-tip="Кратно 1 м длины листа"><?=formatCatalogColumnHeaderHtml('м²', 'длина × Ширина_Расчет')?></th>
            <?php endif; ?>
            <th class="product-table_col-qty"><?=formatCatalogColumnHeaderHtml('Штуки', 'штуки = метры ÷ Длина_Расчет')?></th>
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
            $piecesStep = $onlyPieces || ($basicSheet && !$halfPieces) ? '1' : ($halfPieces ? '0.5' : '0.1');
            $piecesMin = $onlyPieces || ($basicSheet && !$halfPieces) ? '1' : ($halfPieces ? '0.5' : '0.1');
            $lockedTitle = 'Продажа только целыми штуками. Метры и ширина заданы производителем.';
            $basicSheetTip = ($basicSheet && $halfPieces)
                ? getBasicSheetHalfPiecesCuttingTipText()
                : getBasicSheetCuttingTipText();
            $basicSheetDimensionsTip = getBasicSheetDimensionsTipText();
            $sheetAreaPerPiece = ($basicSheet && $metersInPiece && $widthValue)
                ? round(floatval($metersInPiece) * floatval($widthValue), 3)
                : 0;
            $basicSheetSteps = ($basicSheet && !$halfPieces && $metersInPiece && $widthValue)
                ? getBasicSheetWidthMeterSteps($metersInPiece, $widthValue)
                : null;
            if ($basicSheetSteps) {
                $piecesStep = $basicSheetSteps['PIECE_STEP'];
                $piecesMin = $basicSheetSteps['PIECE_STEP'];
            }
            $halfPiecesCuttingTip = ($basicSheet && $halfPieces)
                ? getBasicSheetHalfPiecesCuttingTipText()
                : ($isSheet ? getSheetCuttingTipText() : getFreeCuttingTipText());
            $weightFromBulk = !empty($arItem['WEIGHT_FROM_BULK']);
            $weightInputAllowed = $weightFromBulk && $weightPerMeter > 0;
            $minBulkWeight = (int)($arItem['MIN_BULK_WEIGHT'] ?? 0);
            $weightFromBulkTip = $weightFromBulk ? getWeightFrom500TipText($minBulkWeight) : '';
            $bulkBadgeLabel = $weightFromBulk ? formatBulkWeightBadgeLabel($minBulkWeight) : '';
            $displayWeightKg = $initialWeightKg !== '' ? $initialWeightKg : '—';
            if ($weightFromBulk && $onlyPieces) {
                $piecesStep = '1';
                $piecesMin = '1';
            }
            $lockSheetMeters = $onlyPieces || ($basicSheet && !$halfPieces);
            $halfSheetAreaStep = ($basicSheet && $halfPieces && $sheetAreaPerPiece > 0)
                ? round($sheetAreaPerPiece * 0.5, 3)
                : 0;
        ?>
        <tr data-price="<?=$arItem['RETAIL_PRICE']?>" data-length="<?=$metersInPiece?>" data-width="<?=$widthValue?>" data-only-pieces="<?=$onlyPieces ? '1' : '0'?>" data-half-pieces="<?=$halfPieces ? '1' : '0'?>" data-basic-sheet="<?=($basicSheet && !$halfPieces) ? '1' : '0'?>"<?=$onlyPieces ? ' class="product-table_row--only-pieces"' : ''?><?=$weightPerMeter > 0 ? ' data-weight-per-meter="' . htmlspecialcharsbx($weightPerMeter) . '"' : ''?><?=$weightInputAllowed ? ' data-weight-editable="1"' : ''?><?=$weightFromBulk ? ' data-weight-from-bulk="1" data-weight-from-500="1" data-min-bulk-weight="' . $minBulkWeight . '" data-weight-per-piece="' . htmlspecialcharsbx($weightPerPiece) . '" data-order-mode="pieces"' : ''?>>
            <td class="product-table_first-cell">
                <button type="button" class="product-availability-marker" title="В наличии на складе." aria-label="В наличии на складе.">
                    <span class="product-availability-marker__tip" aria-hidden="true">В наличии на складе.</span>
                </button>
                <span class="product-item_name"><?=$arItem["NAME"];?></span>
                <?php if ($onlyPieces): ?>
                <button type="button" class="product-hint product-hint--lock" data-tip="<?=$lockedTitle?>" aria-label="<?=$lockedTitle?>">
                    <span class="product-hint__icon--lock" aria-hidden="true"></span>
                </button>
                <?php elseif ($weightFromBulk && $minBulkWeight >= 1000): ?>
                <button type="button" class="product-hint product-hint--bulk" data-tip="<?=$weightFromBulkTip?>" aria-label="<?=$weightFromBulkTip?>">
                    <span class="product-hint__label"><?=$bulkBadgeLabel?></span>
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
                <td><?php
                    if (!empty($price['IS_PRICE_PER_KG'])) {
                        echo $arItem['PRINT_PRICE_PER_KG'] ?? '—';
                    } else {
                        echo $arItem['ITEM_ALL_PRICES'][0]['PRICES'][$price['CATALOG_GROUP_ID']]['PRINT_PRICE'] ?? 0;
                    }
                ?></td>
            <?php endforeach; ?>
            <?php if (!empty($arResult['SHOW_WEIGHT_COLUMN'])): ?>
            <td class="product-table_cell-qty product-table_cell-weight">
                <?php if ($weightInputAllowed): ?>
                <div class="product-table_field" data-tip="<?=htmlspecialcharsbx(getWeightFrom500PiecesTipText($minBulkWeight))?>">
                    <input type="number" class="product-table-input product-table-input_weight" min="0.01" step="0.001" placeholder="0" name="weight_kg" value="<?=$initialWeightKg?>" data-weight-per-meter="<?=$weightPerMeter?>" data-tip-pieces="<?=htmlspecialcharsbx(getWeightFrom500PiecesTipText($minBulkWeight))?>" data-tip-bulk="<?=htmlspecialcharsbx(getWeightFrom500BulkTipText($minBulkWeight))?>">
                </div>
                <?php else: ?>
                <div class="product-table_field product-table_field--restricted" data-tip="Вес шт = Коэффициент_Расчет × Ширина_Расчет × Длина_Расчет<?=$weightPerMeter > 0 ? ' · ' . $weightPerMeter . ' кг/м' : ''?>">
                    <span class="product-table_field-value" data-weight-display><?=htmlspecialcharsbx((string)$displayWeightKg)?></span>
                    <input type="hidden" name="weight_kg" value="<?=htmlspecialcharsbx((string)$initialWeightKg)?>" data-weight-per-meter="<?=htmlspecialcharsbx((string)$weightPerMeter)?>">
                </div>
                <?php endif; ?>
            </td>
            <?php endif; ?>
            <td class="product-table_cell-qty<?=$lockSheetMeters ? ' product-table_cell--locked' : ''?>">
                <?php if ($lockSheetMeters): ?>
                <div class="product-table_field product-table_field--restricted" data-tip="<?=$basicSheet ? $basicSheetDimensionsTip : $lockedTitle?>">
                    <?php if ($onlyPieces): ?>
                    <span class="product-hint__icon--lock" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="product-table_field-value"><?=$metersInPiece?></span>
                    <input type="hidden" name="meters" value="<?=$metersInPiece?>" data-meters-in-one-piece="<?=$metersInPiece?>">
                </div>
                <?php else: ?>
                <div class="product-table_field">
                    <input type="number" class="product-table-input" min="<?=$halfPieces && $metersInPiece ? htmlspecialcharsbx(round((float)$metersInPiece * 0.5, 3)) : '0.1'?>" step="<?=$halfPieces && $metersInPiece ? htmlspecialcharsbx(round((float)$metersInPiece * 0.5, 3)) : '0.1'?>" placeholder="0" name="meters" value="<?=$metersInPiece?>" data-meters-in-one-piece="<?=$metersInPiece?>">
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
            <td class="product-table_cell-qty<?=($basicSheet || ($halfPieces && $sheetAreaPerPiece > 0)) ? '' : ' product-table_cell--locked'?>">
                <?php if ($basicSheet && $halfPieces && $halfSheetAreaStep > 0): ?>
                <div class="product-table_field" data-tip="м² = штуки × площадь листа · шаг 0,5 шт">
                    <input type="number" class="product-table-input product-table-input_area" min="<?=$halfSheetAreaStep?>" step="<?=$halfSheetAreaStep?>" placeholder="0" name="area_m2" value="<?=$sheetAreaPerPiece?>" data-area-step="<?=$halfSheetAreaStep?>" data-full-area="<?=$sheetAreaPerPiece?>">
                </div>
                <?php elseif ($basicSheet && $basicSheetSteps): ?>
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
        <?php if (!empty($arResult['BULK_WEIGHT_THRESHOLDS'])): ?>
            <?php ksort($arResult['BULK_WEIGHT_THRESHOLDS']); ?>
            <?php foreach (array_keys($arResult['BULK_WEIGHT_THRESHOLDS']) as $bulkThreshold): ?>
                <?php if ((int)$bulkThreshold < 1000) { continue; } ?>
        <div class="product-hint-legend product-hint-legend--badge product-hint-legend--bulk" data-tip="<?=htmlspecialcharsbx(getWeightFrom500TipText($bulkThreshold))?>">
            <span class="product-hint-legend__badge product-hint product-hint--bulk" aria-hidden="true"><span class="product-hint__label"><?=formatBulkWeightBadgeLabel($bulkThreshold)?></span></span>
            <span class="product-hint-legend__text"><?=htmlspecialcharsbx(getBulkWeightLegendText($bulkThreshold))?></span>
        </div>
            <?php endforeach; ?>
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
