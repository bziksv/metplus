<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $mobileColumns
 * @var array $arParams
 * @var string $templateFolder
 */
?>
<script id="basket-item-template" type="text/html">

	<tr class="basket-items-list-item-container{{#IS_CUTTING}} basket-items-list-item-container--cutting{{/IS_CUTTING}}" id="basket-item-{{ID}}" data-entity="basket-item" data-id="{{ID}}" data-length-per-piece="{{BASKET_LENGTH_PER_PIECE}}" data-display-pieces="{{DISPLAY_PIECES}}">

		{{^SHOW_RESTORE}}

            <td class="cart-table_col-name">
                <span class="cart-table_mobile-text">Название товара</span>
                <div class="basket-item-name">{{NAME}}</div>

                {{#IS_CUTTING}}
                <div class="basket-item-cutting-actions">
                    <button type="button" class="cutting-plan-toggle{{#CUTTING_ENABLED}} is-active{{/CUTTING_ENABLED}}" data-entity="cutting-plan-toggle" data-id="{{ID}}" aria-expanded="{{#CUTTING_ENABLED}}true{{/CUTTING_ENABLED}}{{^CUTTING_ENABLED}}false{{/CUTTING_ENABLED}}">
                        Хочу порезку
                    </button>
                </div>
                {{/IS_CUTTING}}
            </td>

            <td class="cart-table_col-price">
                <span class="cart-table_mobile-text">Цена</span>
                <div class="basket-item-cell-inner">
                    <span id="basket-item-price-{{ID}}">{{{PRICE_FORMATED}}}</span>
                    {{#NOTES}}
                    <div class="basket-item-custom-notes basket-item-custom-notes--price">
                        {{NOTES}}
                    </div>
                    {{/NOTES}}
                </div>
            </td>

            <td class="cart-table_col-pieces">
                <span class="cart-table_mobile-text">Количество шт</span>
                <div class="basket-item-cell-inner">
                    <div class="wrapper-counter-btn" data-entity="basket-item-pieces-block">
                        <button type="button" class="counter-back" data-entity="basket-item-pieces-minus" aria-label="Уменьшить количество, шт"></button>
                        <input type="number" class="cart-table_qty-input" min="0" step="any" inputmode="decimal"
                            value="{{DISPLAY_PIECES}}"
                            data-entity="basket-item-pieces-{{ID}}"
                            aria-label="Количество, шт"
                            {{#NOT_AVAILABLE}} disabled="disabled"{{/NOT_AVAILABLE}}>
                        <button type="button" class="counter-forward" data-entity="basket-item-pieces-plus" aria-label="Увеличить количество, шт"></button>
                    </div>
                </div>
            </td>

            <td class="cart-table_col-area">
                <span class="cart-table_mobile-text">м/м²</span>
                <div class="basket-item-cell-inner">
                    <div class="cart-table_area-cell">
                        <div class="wrapper-counter-btn" data-entity="basket-item-quantity-block">
                            <button type="button" class="counter-back" data-entity="basket-item-quantity-minus"></button>
                            <input type="hidden" value="{{QUANTITY}}" data-value="{{QUANTITY}}" data-entity="basket-item-quantity-field"
                            id="basket-item-quantity-{{ID}}" {{#NOT_AVAILABLE}} disabled="disabled"{{/NOT_AVAILABLE}}>
                            <input type="number" class="cart-table_qty-input" min="0" step="0.01" inputmode="decimal"
                                value="{{DISPLAY_AREA}}"
                                data-entity="basket-item-area-{{ID}}"
                                aria-label="Количество, м или м²"
                                {{#NOT_AVAILABLE}} disabled="disabled"{{/NOT_AVAILABLE}}>
                            <button type="button" class="counter-forward" data-entity="basket-item-quantity-plus"></button>
                        </div>
                        <span class="cart-table_qty-unit" data-entity="basket-item-area-unit-{{ID}}">{{DISPLAY_AREA_UNIT}}</span>
                    </div>
                </div>
            </td>

            <td class="cart-table_col-weight">
                <span class="cart-table_mobile-text">Вес, кг</span>
                <div class="basket-item-cell-inner">
                    <div class="wrapper-counter-btn" data-entity="basket-item-weight-block">
                        <button type="button" class="counter-back" data-entity="basket-item-weight-minus" aria-label="Уменьшить вес, кг"></button>
                        <input type="number" class="cart-table_qty-input" min="0" step="0.001" inputmode="decimal"
                            value="{{DISPLAY_WEIGHT}}"
                            data-entity="basket-item-weight-{{ID}}"
                            aria-label="Вес, кг"
                            {{#NOT_AVAILABLE}} disabled="disabled"{{/NOT_AVAILABLE}}>
                        <button type="button" class="counter-forward" data-entity="basket-item-weight-plus" aria-label="Увеличить вес, кг"></button>
                    </div>
                </div>
            </td>

            <td class="cart-table_col-sum">
                <span class="cart-table_mobile-text">Сумма</span>
                <div class="basket-item-cell-inner">
                    <span id="basket-item-sum-price-{{ID}}">{{{SUM_PRICE_FORMATED}}}</span>
                </div>
            </td>

            <td class="cart-table_col-delete">
                <div class="basket-item-cell-inner">
                    <div class="cart-item_delete">
                        <span class="glipf-delete" data-entity="basket-item-delete"></span>
                    </div>
                </div>
            </td>

		{{/SHOW_RESTORE}}
	</tr>

    {{#IS_CUTTING}}
    <tr class="cutting-plan-row{{#CUTTING_ENABLED}} is-open{{/CUTTING_ENABLED}}" id="basket-item-{{ID}}-cutting" data-entity="cutting-plan-row" data-id="{{ID}}"{{^CUTTING_ENABLED}} hidden{{/CUTTING_ENABLED}}>
        <td colspan="7" class="cutting-plan-cell">
                <div class="cutting-plan" data-entity="cutting-plan" data-id="{{ID}}" data-enabled="{{#CUTTING_ENABLED}}Y{{/CUTTING_ENABLED}}{{^CUTTING_ENABLED}}N{{/CUTTING_ENABLED}}">
                <div class="cutting-plan__head">
                    <div class="cutting-plan__title">Резка этого товара</div>
                    <button type="button" class="cutting-plan__cancel" data-entity="cutting-cancel" data-id="{{ID}}">Отменить резку</button>
                </div>

                <div class="cutting-plan__summary" data-entity="cutting-summary" data-id="{{ID}}">
                    <div class="cutting-plan__summary-item">
                        <span>В корзине</span>
                        <strong data-entity="cutting-summary-total">—</strong>
                    </div>
                    <div class="cutting-plan__summary-item">
                        <span>К резке</span>
                        <strong data-entity="cutting-summary-used">0 шт</strong>
                    </div>
                    <div class="cutting-plan__summary-item">
                        <span>Без резки</span>
                        <strong data-entity="cutting-summary-rest">—</strong>
                    </div>
                    <div class="cutting-plan__summary-item">
                        <span>Длина прутка</span>
                        <strong data-entity="cutting-summary-stock">—</strong>
                    </div>
                    <div class="cutting-plan__summary-item cutting-plan__summary-item--price">
                        <span>Стоимость резки</span>
                        <strong data-entity="cutting-summary-cost">0 ₽</strong>
                    </div>
                </div>

                <div class="cutting-plan__hint">
                    У каждой партии свой тип резки: сколько штук, чем резать и на какие длины. Остаток штук останется без резки.
                </div>

                <div class="cutting-plan__parts" data-entity="cutting-parts" data-id="{{ID}}" data-plan="{{CUTTING_PLAN_TEXT}}">
                    <div class="cutting-part" data-entity="cutting-part">
                        <div class="cutting-part__row">
                            <label class="cutting-part__field">
                                <span class="cutting-part__label">Сколько штук</span>
                                <input class="cutting-part__input" type="number" min="1" step="1" value="1" data-entity="cutting-part-qty">
                            </label>
                            <label class="cutting-part__field">
                                <span class="cutting-part__label">Тип резки</span>
                                <select class="cutting-part__input cutting-part__select" data-entity="cutting-part-type">
                                    {{#CUTTING_OPTIONS}}
                                    <option value="{{CODE}}" data-name="{{NAME}}" data-price="{{PRICE}}">{{LABEL}}</option>
                                    {{/CUTTING_OPTIONS}}
                                </select>
                            </label>
                            <label class="cutting-part__field cutting-part__field--wide">
                                <span class="cutting-part__label">Длины кусков, м</span>
                                <input class="cutting-part__input" type="text" placeholder="например: 2.3 + 3.1" data-entity="cutting-part-cuts">
                            </label>
                            <button type="button" class="cutting-part__remove" data-entity="cutting-part-remove" aria-label="Удалить партию">×</button>
                        </div>
                        <div class="cutting-part__preview" data-entity="cutting-part-preview"></div>
                    </div>
                </div>

                <div class="cutting-plan__error" data-entity="cutting-plan-error" data-id="{{ID}}" hidden></div>

                <div class="cutting-plan__actions">
                    <button type="button" class="cutting-plan__add" data-entity="cutting-part-add" data-id="{{ID}}">+ Добавить партию</button>
                    <span class="cutting-plan__status" data-entity="cutting-plan-status" data-id="{{ID}}" hidden></span>
                </div>
            </div>
        </td>
    </tr>
    {{/IS_CUTTING}}
</script>
