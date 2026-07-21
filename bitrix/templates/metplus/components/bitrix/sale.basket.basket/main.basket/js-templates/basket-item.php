<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $mobileColumns
 * @var array $arParams
 * @var string $templateFolder
 */
?>
<script id="basket-item-template" type="text/html">

	<tr class="basket-items-list-item-container{{#IS_CUTTING}} basket-items-list-item-container--cutting{{/IS_CUTTING}}" id="basket-item-{{ID}}" data-entity="basket-item" data-id="{{ID}}" data-length-per-piece="{{BASKET_LENGTH_PER_PIECE}}" data-basket-width="{{BASKET_WIDTH}}" data-cutting-stock="{{BASKET_CUTTING_STOCK}}" data-display-pieces="{{DISPLAY_PIECES}}" data-base-meter-price="{{BASE_METER_PRICE}}" data-is-sheet="{{#IS_SHEET}}1{{/IS_SHEET}}{{^IS_SHEET}}0{{/IS_SHEET}}" data-only-pieces="{{#ONLY_PIECES}}1{{/ONLY_PIECES}}{{^ONLY_PIECES}}0{{/ONLY_PIECES}}" data-half-pieces="{{#HALF_PIECES}}1{{/HALF_PIECES}}{{^HALF_PIECES}}0{{/HALF_PIECES}}" data-basic-sheet="{{#BASIC_SHEET}}1{{/BASIC_SHEET}}{{^BASIC_SHEET}}0{{/BASIC_SHEET}}" data-whole-sheet-pieces="{{#WHOLE_SHEET_PIECES}}1{{/WHOLE_SHEET_PIECES}}{{^WHOLE_SHEET_PIECES}}0{{/WHOLE_SHEET_PIECES}}" data-free-cutting="{{#FREE_CUTTING_1M}}1{{/FREE_CUTTING_1M}}{{^FREE_CUTTING_1M}}0{{/FREE_CUTTING_1M}}" data-half-piece-cut="{{#HALF_PIECE_CUT}}1{{/HALF_PIECE_CUT}}{{^HALF_PIECE_CUT}}0{{/HALF_PIECE_CUT}}" data-default-cut-price="{{DEFAULT_CUT_PRICE}}">

		{{^SHOW_RESTORE}}

            <td class="cart-table_col-name">
                <span class="cart-table_mobile-text">Название товара</span>
                <div class="basket-item-name" data-entity="basket-item-name">
                    {{#HAS_SECTION_LINK}}
                    <a class="basket-item-name__link" href="{{SECTION_PAGE_URL}}">{{NAME}}</a>
                    {{/HAS_SECTION_LINK}}
                    {{^HAS_SECTION_LINK}}
                    {{NAME}}
                    {{/HAS_SECTION_LINK}}
                </div>

                {{#ONLY_PIECES}}
                <div class="basket-item-cutting-actions">
                    <span class="cutting-plan-notice">Данный товар не режется, отпускается поштучно</span>
                </div>
                {{/ONLY_PIECES}}
                {{#IS_CUTTING}}
                <div class="basket-item-cutting-actions">
                    {{#BASIC_SHEET}}
                    {{#HALF_PIECES}}
                    <span class="cutting-plan-notice">Заказ в шт или м² — кратно 1 м длины. Резы всегда оплачиваются. Наценок (+10%) нет — ни за неполную, ни за сложную резку</span>
                    {{/HALF_PIECES}}
                    {{^HALF_PIECES}}
                    <span class="cutting-plan-notice">Заказ в шт или м² — кратно 1 м длины. Резы всегда оплачиваются. Больше резов — дополнительно +10% на резанные. Неполная штука — +10% на кусок и 1 рез</span>
                    {{/HALF_PIECES}}
                    {{/BASIC_SHEET}}
                    {{#IS_SHEET}}
                    {{^BASIC_SHEET}}
                    <span class="cutting-plan-notice">Режется кратно 0,1 м</span>
                    {{/BASIC_SHEET}}
                    {{/IS_SHEET}}
                    {{#FREE_CUTTING_1M}}
                    <span class="cutting-plan-notice cutting-plan-notice--free">Режется кратно 0,1 м без наценки</span>
                    {{/FREE_CUTTING_1M}}
                    {{^IS_SHEET}}
                    {{^FREE_CUTTING_1M}}
                    <span class="cutting-plan-notice">Неполная штука — 1 рез. Наценка +20% только на кусок, целые — по обычной цене</span>
                    {{/FREE_CUTTING_1M}}
                    {{/IS_SHEET}}
                    {{#HALF_PIECE_CUT}}
                    <span class="cutting-plan-notice cutting-plan-notice--half-cut">{{HALF_PIECE_CUT_NOTICE}}</span>
                    {{/HALF_PIECE_CUT}}
                    <button type="button" class="cutting-plan-toggle{{#CUTTING_ENABLED}} is-active{{/CUTTING_ENABLED}}" data-entity="cutting-plan-toggle" data-id="{{ID}}" aria-expanded="{{#CUTTING_ENABLED}}true{{/CUTTING_ENABLED}}{{^CUTTING_ENABLED}}false{{/CUTTING_ENABLED}}">
                        {{#CUTTING_ENABLED}}Изменить резку{{/CUTTING_ENABLED}}{{^CUTTING_ENABLED}}Хочу порезку{{/CUTTING_ENABLED}}
                    </button>
                </div>
                {{/IS_CUTTING}}
            </td>

            <td class="cart-table_col-steel">
                <span class="cart-table_mobile-text">Марка стали</span>
                <div class="basket-item-cell-inner">
                    <span>{{STEEL_GRADE}}</span>
                </div>
            </td>

            <td class="cart-table_col-price">
                <span class="cart-table_mobile-text">Цена</span>
                <div class="basket-item-cell-inner">
                    <span id="basket-item-price-{{ID}}">{{{PRICE_FORMATED}}}</span>
                </div>
            </td>

            <td class="cart-table_col-pieces">
                <span class="cart-table_mobile-text">Количество шт</span>
                <div class="basket-item-cell-inner">
                    <div class="wrapper-counter-btn" data-entity="basket-item-pieces-block">
                        <button type="button" class="counter-back" data-entity="basket-item-pieces-minus" aria-label="Уменьшить количество, шт"></button>
                        <input type="text" class="cart-table_qty-input" inputmode="decimal"
                            value="{{DISPLAY_PIECES}}"
                            data-entity="basket-item-pieces-{{ID}}"
                            aria-label="Количество, шт"
                            autocomplete="off"
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
                            <input type="number" class="cart-table_qty-input" min="0"
                                {{#IS_SHEET}}step="0.01" inputmode="decimal"{{/IS_SHEET}}
                                {{^IS_SHEET}}step="1" inputmode="numeric" min="1"{{/IS_SHEET}}
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
    <tr class="cutting-plan-row" id="basket-item-{{ID}}-cutting" data-entity="cutting-plan-row" data-id="{{ID}}" hidden>
        <td colspan="8" class="cutting-plan-cell">
            <div class="cutting-plan cutting-plan--wizard" data-entity="cutting-plan" data-id="{{ID}}" data-enabled="{{#CUTTING_ENABLED}}Y{{/CUTTING_ENABLED}}{{^CUTTING_ENABLED}}N{{/CUTTING_ENABLED}}" data-wizard-step="1">
                <div class="cutting-plan__head">
                    <div class="cutting-plan__title">Резка</div>
                    <button type="button" class="cutting-plan__cancel" data-entity="cutting-cancel" data-id="{{ID}}">Отменить</button>
                </div>

                <!-- прогресс шагов (новый UI) -->
                <div class="cutting-wizard-steps" data-entity="cutting-wizard-steps">
                    <div class="cutting-wizard-steps__item is-active" data-wizard-tab="1"><span>1</span> Что режем</div>
                    <div class="cutting-wizard-steps__item" data-wizard-tab="2"><span>2</span> Как режем</div>
                    <div class="cutting-wizard-steps__item" data-wizard-tab="3"><span>3</span> Итого</div>
                </div>

                <!-- ШАГ 1: что режем -->
                <div class="cutting-wizard-panel" data-entity="cutting-wizard-panel" data-wizard-panel="1">
                    <p class="cutting-wizard-lead">Выберите, что добавить к резке (мы режем по 1 метру в длину)</p>
                    <div class="cutting-target" data-entity="cutting-target" data-id="{{ID}}">
                        <div class="cutting-target__cards">
                            <button type="button" class="cutting-target__card is-active" data-entity="cutting-target-full" data-id="{{ID}}">
                                <strong>Целые штуки</strong>
                                <span data-entity="cutting-target-full-meta">—</span>
                            </button>
                            <button type="button" class="cutting-target__card" data-entity="cutting-target-incomplete" data-id="{{ID}}" hidden>
                                <strong>Неполная штука</strong>
                                <span data-entity="cutting-target-incomplete-meta">—</span>
                            </button>
                        </div>
                    </div>
                    <div class="cutting-wizard-nav">
                        <button type="button" class="cutting-wizard-btn cutting-wizard-btn--primary" data-entity="cutting-wizard-next" data-id="{{ID}}" data-to-step="2">Далее</button>
                    </div>
                </div>

                <!-- ШАГ 2: как режем -->
                <div class="cutting-wizard-panel" data-entity="cutting-wizard-panel" data-wizard-panel="2" hidden>
                    <p class="cutting-wizard-lead">Укажите тип резки и длины кусков</p>
                    <div class="cutting-plan__parts" data-entity="cutting-parts" data-id="{{ID}}" data-plan="{{CUTTING_PLAN_TEXT}}">
                        <div class="cutting-part" data-entity="cutting-part" data-target="full">
                            <div class="cutting-part__target-badge" data-entity="cutting-part-target-label">Целая</div>
                            <div class="cutting-part__row">
                                <label class="cutting-part__field" data-entity="cutting-part-qty-field">
                                    <span class="cutting-part__label">Сколько штук</span>
                                    <input class="cutting-part__input" type="number" min="1" step="1" value="1" data-entity="cutting-part-qty">
                                </label>
                                <div class="cutting-part__field" data-entity="cutting-part-qty-fixed" hidden>
                                    <span class="cutting-part__label">Количество</span>
                                    <div class="cutting-part__fixed-value" data-entity="cutting-part-qty-fixed-value">—</div>
                                </div>
                                <label class="cutting-part__field">
                                    <span class="cutting-part__label">Тип резки</span>
                                    <select class="cutting-part__input cutting-part__select" data-entity="cutting-part-type">
                                        {{#CUTTING_OPTIONS}}
                                        <option value="{{CODE}}" data-name="{{NAME}}" data-price="{{PRICE}}">{{LABEL}}</option>
                                        {{/CUTTING_OPTIONS}}
                                    </select>
                                </label>
                                <label class="cutting-part__field cutting-part__field--wide">
                                    <span class="cutting-part__label">Куски, м <span class="cutting-part__label-hint">(режем кратно 0.1 м)</span></span>
                                    <input class="cutting-part__input" type="text" placeholder="например: 1.2 3.5 2" data-entity="cutting-part-cuts" inputmode="decimal">
                                </label>
                                <button type="button" class="cutting-part__remove" data-entity="cutting-part-remove" aria-label="Удалить партию">×</button>
                            </div>
                            <div class="cutting-part__preview" data-entity="cutting-part-preview"></div>
                        </div>
                    </div>
                    <div class="cutting-plan__hint" data-entity="cutting-plan-hint" data-id="{{ID}}" hidden></div>
                    <div class="cutting-plan__error" data-entity="cutting-plan-error" data-id="{{ID}}" hidden></div>
                    <div class="cutting-wizard-nav">
                        <button type="button" class="cutting-wizard-btn" data-entity="cutting-wizard-back" data-id="{{ID}}" data-to-step="1">Назад</button>
                        <button type="button" class="cutting-plan__add" data-entity="cutting-part-add" data-id="{{ID}}" data-add-target="full">
                            <span class="cutting-plan__add-label--new">+ Ещё целые</span>
                            <span class="cutting-plan__add-label--original">+ Добавить партию</span>
                        </button>
                        <button type="button" class="cutting-plan__add cutting-plan__add--incomplete" data-entity="cutting-part-add-incomplete" data-id="{{ID}}" hidden>+ Неполная</button>
                        <button type="button" class="cutting-wizard-btn cutting-wizard-btn--primary" data-entity="cutting-wizard-next" data-id="{{ID}}" data-to-step="3">Далее</button>
                    </div>
                </div>

                <!-- ШАГ 3: итог -->
                <div class="cutting-wizard-panel" data-entity="cutting-wizard-panel" data-wizard-panel="3" hidden>
                    <p class="cutting-wizard-lead">Итоговый расчёт по позиции</p>
                    <div class="cutting-plan__cost-breakdown" data-entity="cutting-cost-breakdown" data-id="{{ID}}" hidden>
                        <div class="cutting-plan__cost-breakdown-lines" data-entity="cutting-cost-breakdown-lines"></div>
                        <div class="cutting-plan__cost-totals" data-entity="cutting-cost-breakdown-total">
                            <div class="cutting-cost-total-row" data-entity="cutting-total-metal"></div>
                            <div class="cutting-cost-total-row" data-entity="cutting-total-surcharge"></div>
                            <div class="cutting-cost-total-row" data-entity="cutting-total-cuts"></div>
                            <div class="cutting-cost-total-row cutting-cost-total-row--grand" data-entity="cutting-total-grand"></div>
                        </div>
                    </div>
                    <div class="cutting-wizard-done" data-entity="cutting-wizard-done-fallback">
                        <div class="cutting-wizard-done__line">Стоимость резки: <strong data-entity="cutting-summary-cost-copy">0 ₽</strong></div>
                    </div>
                    <div class="cutting-wizard-nav">
                        <button type="button" class="cutting-wizard-btn" data-entity="cutting-wizard-back" data-id="{{ID}}" data-to-step="2">Назад</button>
                        <button type="button" class="cutting-plan__add" data-entity="cutting-part-add" data-id="{{ID}}" data-add-target="full">
                            <span class="cutting-plan__add-label--new">+ Ещё целые</span>
                            <span class="cutting-plan__add-label--original">+ Добавить партию</span>
                        </button>
                        <button type="button" class="cutting-plan__add cutting-plan__add--incomplete" data-entity="cutting-part-add-incomplete" data-id="{{ID}}" hidden>+ Неполная</button>
                        <span class="cutting-plan__status" data-entity="cutting-plan-status" data-id="{{ID}}" hidden></span>
                    </div>
                </div>

                <!-- подсчёты — после выбора «что режем» -->
                <div class="cutting-plan__summary cutting-plan__summary--compact" data-entity="cutting-summary" data-id="{{ID}}">
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
                    <div class="cutting-plan__summary-item cutting-plan__summary-item--price">
                        <span>Резка</span>
                        <strong data-entity="cutting-summary-cost">0 ₽</strong>
                    </div>
                    <div class="cutting-plan__summary-item cutting-plan__summary-item--stock">
                        <span>Длина</span>
                        <strong data-entity="cutting-summary-stock">—</strong>
                    </div>
                </div>

                <!-- оригинал: подсказки (скрыты в новом) -->
                <div class="cutting-plan__hint cutting-plan__hint--legacy">
                    {{#BASIC_SHEET}}
                    {{#HALF_PIECES}}
                    Заказ в шт или м² — кратно 1 м длины. Резы всегда оплачиваются по тарифу. Наценок (+10%) нет ни за неполную штуку, ни за сложную резку.
                    {{/HALF_PIECES}}
                    {{^HALF_PIECES}}
                    Заказ в шт или м² — кратно 1 м длины. Резы всегда оплачиваются. Больше резов — +10% на резанные. Неполная — +10% и 1 рез.
                    {{/HALF_PIECES}}
                    {{/BASIC_SHEET}}
                    {{#FREE_CUTTING_1M}}
                    {{^BASIC_SHEET}}
                    Резка кратно 0,1 м — без +20%. Неполная — стоимость одного реза.
                    {{/BASIC_SHEET}}
                    {{/FREE_CUTTING_1M}}
                    {{^IS_SHEET}}
                    {{^FREE_CUTTING_1M}}
                    Неполная штука — 1 рез. Наценка +20% только на кусок.
                    {{/FREE_CUTTING_1M}}
                    {{/IS_SHEET}}
                </div>

                <!-- оригинал: плоский список партий дублируется через JS show — панели выше содержат parts -->
                <div class="cutting-stock-bar" data-entity="cutting-stock-bar" hidden>
                    <div class="cutting-stock-bar__seg cutting-stock-bar__seg--rest" data-entity="cutting-stock-rest" style="flex:1"></div>
                    <div class="cutting-stock-bar__seg cutting-stock-bar__seg--cut" data-entity="cutting-stock-cut" style="flex:0"></div>
                    <div class="cutting-stock-bar__seg cutting-stock-bar__seg--incomplete" data-entity="cutting-stock-incomplete" style="flex:0"></div>
                </div>
                <div class="cutting-stock-bar__legend" data-entity="cutting-stock-legend" hidden></div>
            </div>
        </td>
    </tr>
    {{/IS_CUTTING}}
</script>
