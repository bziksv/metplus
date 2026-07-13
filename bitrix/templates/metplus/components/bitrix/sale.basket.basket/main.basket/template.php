<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.fonts.ruble");

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 */

if (!isset($arParams['DISPLAY_MODE']) || !in_array($arParams['DISPLAY_MODE'], array('extended', 'compact')))
{
	$arParams['DISPLAY_MODE'] = 'extended';
}

$arParams['USE_DYNAMIC_SCROLL'] = isset($arParams['USE_DYNAMIC_SCROLL']) && $arParams['USE_DYNAMIC_SCROLL'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_FILTER'] = isset($arParams['SHOW_FILTER']) && $arParams['SHOW_FILTER'] === 'N' ? 'N' : 'Y';

$arParams['PRICE_DISPLAY_MODE'] = isset($arParams['PRICE_DISPLAY_MODE']) && $arParams['PRICE_DISPLAY_MODE'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['TOTAL_BLOCK_DISPLAY']) || !is_array($arParams['TOTAL_BLOCK_DISPLAY']))
{
	$arParams['TOTAL_BLOCK_DISPLAY'] = array('top');
}

if (empty($arParams['PRODUCT_BLOCKS_ORDER']))
{
	$arParams['PRODUCT_BLOCKS_ORDER'] = 'props,sku,columns';
}

if (is_string($arParams['PRODUCT_BLOCKS_ORDER']))
{
	$arParams['PRODUCT_BLOCKS_ORDER'] = explode(',', $arParams['PRODUCT_BLOCKS_ORDER']);
}

$arParams['USE_PRICE_ANIMATION'] = isset($arParams['USE_PRICE_ANIMATION']) && $arParams['USE_PRICE_ANIMATION'] === 'N' ? 'N' : 'Y';
$arParams['EMPTY_BASKET_HINT_PATH'] = isset($arParams['EMPTY_BASKET_HINT_PATH']) ? (string)$arParams['EMPTY_BASKET_HINT_PATH'] : '/';
$arParams['USE_ENHANCED_ECOMMERCE'] = isset($arParams['USE_ENHANCED_ECOMMERCE']) && $arParams['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
$arParams['DATA_LAYER_NAME'] = isset($arParams['DATA_LAYER_NAME']) ? trim($arParams['DATA_LAYER_NAME']) : 'dataLayer';
$arParams['BRAND_PROPERTY'] = isset($arParams['BRAND_PROPERTY']) ? trim($arParams['BRAND_PROPERTY']) : '';

\CJSCore::Init(array('fx', 'popup', 'ajax'));

$this->addExternalJs($templateFolder.'/js/mustache.js');
$this->addExternalJs($templateFolder.'/js/action-pool.js');
$this->addExternalJs($templateFolder.'/js/filter.js');
$this->addExternalJs($templateFolder.'/js/component.js');
$this->addExternalJs($templateFolder.'/js/basket-qty.js');

$mobileColumns = isset($arParams['COLUMNS_LIST_MOBILE'])
	? $arParams['COLUMNS_LIST_MOBILE']
	: $arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);

$jsTemplates = new Main\IO\Directory(Main\Application::getDocumentRoot().$templateFolder.'/js-templates');
/** @var Main\IO\File $jsTemplate */
foreach ($jsTemplates->getChildren() as $jsTemplate)
{
	include($jsTemplate->getPath());
}

$displayModeClass = $arParams['DISPLAY_MODE'] === 'compact' ? ' basket-items-list-wrapper-compact' : '';

if (empty($arResult['ERROR_MESSAGE']))
{

	if ($arResult['BASKET_ITEM_MAX_COUNT_EXCEEDED'])
	{
		?>
		<div id="basket-item-message">
			<?=Loc::getMessage('SBB_BASKET_ITEM_MAX_COUNT_EXCEEDED', array('#PATH#' => $arParams['PATH_TO_BASKET']))?>
		</div>
		<?
	}
	?>

	<div class="container" id="basket-root">

        <div class="cart-close"></div>
        <div class="cart-content_header">
            <div class="cart-content_title">Состав корзины</div>
            <ul class="cart-steps">
                <li class="cart-step_item active active-mod">Шаг 1</li>
                <li class="cart-step_item">Шаг 2</li>
                <li class="cart-step_item">Шаг 3</li>
            </ul>
        </div>

        <div class="alert alert-warning alert-dismissable" id="basket-warning" style="display: none;">
            <span class="close" data-entity="basket-items-warning-notification-close">&times;</span>
            <div data-entity="basket-general-warnings"></div>
            <div data-entity="basket-item-warnings">
                <?=Loc::getMessage('SBB_BASKET_ITEM_WARNING')?>
            </div>
        </div>

        <div class="cart-content_body">
            <div class="wrapper_cart-table">
                <table class="cart-table" id="basket-item-table">
                    <tr>
                        <? foreach($arParams['COLUMNS_LIST_HEADER'] as $col): ?>
                            <th><?=$col?></th>
                        <? endforeach;?>
                    </tr>

                </table>
            </div>
            <textarea class="cart-table_textarea" placeholder="Комментарий к заказу"></textarea>
            <p>
                <small>
                Расчеты и размеры товара являются предварительными. После оформления заказа с вами свяжутся наши операторы.
                * цена за единицу товара будет уточнена оператором
                </small>
            </p>

			<noindex><small><p>На нашем сайте осуществляется сбор персональных данных и <span style="color: #073e71;"><a target="_blank" href="/upload/politika-ispolzovanija-cookies-metplus-vrn.pdf">cookies</a></span> для улучшения работы сайта, персонализации контента и анализа посещаемости. Продолжая, вы соглашаетесь с использованием cookies и <span style="color: #073e71;"><a target="_blank" href="/upload/compliance.pdf">обработкой ваших данных</a></span> в соответствии с нашей <span style="color: #073e71;"><a target="_blank" href="/upload/politics.pdf">Политикой конфиденциальности</a></span>.</p></small></noindex>
        </div>

        <div class="cart-content_footer" data-entity="basket-total-block"></div>

	</div>
	<?
	if (!empty($arResult['CURRENCIES']) && Main\Loader::includeModule('currency'))
	{
		CJSCore::Init('currency');

		?>
		<script>
			BX.Currency.setCurrencies(<?=CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)?>);
		</script>
		<?
	}

	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
	$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
	$messages = Loc::loadLanguageFile(__FILE__);
	?>
	<script>
		BX.message(<?=CUtil::PhpToJSObject($messages)?>);
		BX.Sale.BasketComponent.init({
			result: <?=CUtil::PhpToJSObject($arResult, false, false, true)?>,
			params: <?=CUtil::PhpToJSObject($arParams)?>,
			template: '<?=CUtil::JSEscape($signedTemplate)?>',
			signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
			siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
			siteTemplateId: '<?=CUtil::JSEscape($component->getSiteTemplateId())?>',
			templateFolder: '<?=CUtil::JSEscape($templateFolder)?>'
		});

        jQuery(document).ready(function($) {
            $("#basket-root").on("click", ".cutting-service-options", function(e) {
                e.preventDefault();

                let $self = $(this);
                let id = $self.data('id');
                let product_id = $self.data('product_id');

                $.fancybox.open({
                    src  : `/ajax/cutting_services_options.php?product_id=${product_id}`,
                    type : 'ajax',
                    opts : {
                        afterShow : function( instance, current ) {
                            let $self = current.$content;
                            $self.find('.update-action').click(function (e) {
                                e.preventDefault();

                                let type = $self.find('select').val();

                                $.get("/ajax/update_cutting_type_in_cart.php", {
                                    id : id,
                                    product_id: product_id,
                                    type : type,
                                }, function(data) {
                                    if (data.success === true) {
                                        BX.Sale.BasketComponent.sendRequest('refreshAjax', {
                                            fullRecalculation: 'Y',
                                            otherParams: {
                                                param: 'N'
                                            }
                                        });
                                    }
                                    instance.close();
                                }, "json");
                            });
                        }
                    }
                });

                return false;
            });

            function parseCutLengths(raw) {
                return String(raw || '')
                    .replace(/,/g, ' ')
                    .replace(/\+/g, ' ')
                    .split(/\s+/)
                    .map(function(v) { return parseFloat(String(v).replace(',', '.')); })
                    .filter(function(v) { return !isNaN(v) && v > 0; });
            }

            function formatMeters(value) {
                var n = Math.round(value * 1000) / 1000;
                return String(n).replace(/\.0+$/, '').replace(/(\.\d*?)0+$/, '$1');
            }

            function getAvailablePieces($item) {
                var id = $item.attr('data-id') || $item.data('id');
                var lengthPerPiece = parseFloat($item.attr('data-length-per-piece') || '0') || 0;
                var exact = 0;

                // Надёжный источник: метры из скрытого quantity / длину прутка
                var qtyEl = document.getElementById('basket-item-quantity-' + id);
                if (qtyEl && lengthPerPiece > 0) {
                    var meters = parseFloat(String(qtyEl.getAttribute('data-value') || qtyEl.value || '').replace(/\s/g, '').replace(',', '.'));
                    if (!isNaN(meters) && meters > 0) {
                        exact = meters / lengthPerPiece;
                    }
                }

                // fallback: поле «шт» / data-атрибут
                if (!(exact > 0)) {
                    var raw = $item.attr('data-display-pieces') || '';
                    var piecesNode = $item.find('[data-entity="basket-item-pieces-' + id + '"]');
                    if (piecesNode.length) {
                        var fromInput = piecesNode.is('input') ? piecesNode.val() : piecesNode.text();
                        raw = fromInput || raw;
                    }
                    exact = parseFloat(String(raw).replace(/\s/g, '').replace(',', '.'));
                }

                if (isNaN(exact) || exact <= 0) {
                    return { exact: 0, full: 0, fraction: 0 };
                }

                var full = Math.floor(exact + 1e-9);
                var fraction = Math.max(0, exact - full);
                return { exact: exact, full: full, fraction: fraction };
            }

            function getStockLength($item) {
                var len = parseFloat($item.attr('data-length-per-piece') || '0');
                return isNaN(len) ? 0 : len;
            }

            function buildPlanText($plan) {
                var lines = [];
                $plan.find('[data-entity="cutting-part"]').each(function() {
                    var qty = parseInt($(this).find('[data-entity="cutting-part-qty"]').val(), 10) || 0;
                    var $type = $(this).find('[data-entity="cutting-part-type"]');
                    var typeCode = String($type.val() || '');
                    var typeName = String($type.find('option:selected').data('name') || $type.find('option:selected').text() || typeCode);
                    var lengths = parseCutLengths($(this).find('[data-entity="cutting-part-cuts"]').val());
                    if (qty > 0 && lengths.length && typeCode) {
                        lines.push(qty + ' шт | ' + typeCode + ' | ' + lengths.map(formatMeters).join(' + ') + ' | ' + typeName);
                    }
                });
                return lines.join('\n');
            }

            function fillPartsFromPlan($parts) {
                var plan = String($parts.attr('data-plan') || '').trim();
                if (!plan) {
                    return;
                }

                var rows = plan.split(/\n+/).map(function(line) { return line.trim(); }).filter(Boolean);
                if (!rows.length) {
                    return;
                }

                var $first = $parts.find('[data-entity="cutting-part"]').first();
                $parts.find('[data-entity="cutting-part"]').slice(1).remove();

                rows.forEach(function(line, index) {
                    var $part = index === 0 ? $first : $first.clone();
                    if (index > 0) {
                        $parts.append($part);
                    }

                    // new format: 1 шт | CODE | 2.3 + 3.1 | Name
                    var parts = line.split('|').map(function(v) { return v.trim(); });
                    if (parts.length >= 3 && /^\d+\s*шт$/i.test(parts[0])) {
                        $part.find('[data-entity="cutting-part-qty"]').val(parseInt(parts[0], 10) || 1);
                        $part.find('[data-entity="cutting-part-type"]').val(parts[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(parts[2].replace(/\s*м\s*$/i, '').trim());
                        return;
                    }

                    // old format fallback: 1 шт — 2.3 + 3.1 м  OR  1 - 1+1+10
                    var m = line.match(/^(\d+)\s*шт\s*[—\-:]?\s*(.+)$/i);
                    if (m) {
                        $part.find('[data-entity="cutting-part-qty"]').val(m[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(m[2].replace(/\s*м\s*$/i, '').trim());
                        return;
                    }

                    m = line.match(/^(\d+)\s*[-–—]\s*(.+)$/);
                    if (m) {
                        $part.find('[data-entity="cutting-part-qty"]').val(m[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(m[2].trim());
                        return;
                    }

                    $part.find('[data-entity="cutting-part-qty"]').val('1');
                    $part.find('[data-entity="cutting-part-cuts"]').val(line);
                });
            }

            function formatMoney(value) {
                var n = Math.round(Number(value) || 0);
                return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
            }

            function getCutCount(lengths) {
                // 1+1+10 → 3 куска / 3 реза (как ожидает пользователь)
                return Array.isArray(lengths) ? lengths.length : 0;
            }

            function parseMoneyFromText(text) {
                var s = String(text || '').replace(/\s/g, '');
                // берём только числа и точку/запятую
                var m = s.match(/([\d.,]+)/);
                if (!m) return 0;
                var num = parseFloat(m[1].replace(',', '.'));
                return isNaN(num) ? 0 : num;
            }

            function updateBasketTotalWithCutting() {
                var cuttingTotal = 0;
                $('[data-entity="cutting-plan"][data-enabled="Y"]').each(function() {
                    var $cost = $(this).find('[data-entity="cutting-summary-cost"]');
                    cuttingTotal += parseMoneyFromText($cost.text());
                });

                var $base = $('[data-entity="basket-total-price"]').first();
                if (!$base.length) {
                    return;
                }

                var baseNum = parseMoneyFromText($base.text());
                var withCutting = baseNum + cuttingTotal;

                var $cut = $('[data-entity="basket-total-cutting"]').first();
                var $with = $('[data-entity="basket-total-with-cutting"]').first();

                if (cuttingTotal > 0) {
                    $cut.text(formatMoney(cuttingTotal)).closest('.cart-total-price--cutting').show();
                    $with.text(formatMoney(withCutting)).closest('.cart-total-price--with-cutting').show();
                } else {
                    $cut.closest('.cart-total-price--cutting').hide();
                    $with.closest('.cart-total-price--with-cutting').hide();
                }
            }

            function refreshCuttingPlan(id, isRetry) {
                var $item = $('#basket-item-' + id);
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$plan.length) {
                    return { ok: true };
                }

                var availableInfo = getAvailablePieces($item);
                var availableFull = availableInfo.full;
                var stock = getStockLength($item);
                var used = 0;
                var totalCost = 0;
                var totalCuts = 0;
                var errors = [];
                var hasInvalidPart = false;
                var needRecalc = false;

                // Визуализация: 11.92 => 11 целых + неполная 0.92 шт
                if (availableInfo.fraction > 0.0001) {
                    $plan.find('[data-entity="cutting-summary-total"]').text(
                        availableFull + ' шт + неполная ' + formatMeters(availableInfo.fraction) + ' шт'
                    );
                } else {
                    $plan.find('[data-entity="cutting-summary-total"]').text(availableFull + ' шт');
                }
                $plan.find('[data-entity="cutting-summary-stock"]').text(stock > 0 ? (formatMeters(stock) + ' м') : '—');

                $plan.find('[data-entity="cutting-part"]').each(function(index) {
                    var $part = $(this);
                    var $qty = $part.find('[data-entity="cutting-part-qty"]');
                    var $type = $part.find('[data-entity="cutting-part-type"]');
                    var $cuts = $part.find('[data-entity="cutting-part-cuts"]');
                    var $preview = $part.find('[data-entity="cutting-part-preview"]');
                    var qty = parseInt($qty.val(), 10) || 0;
                    var typeCode = String($type.val() || '');
                    var $opt = $type.find('option:selected');
                    var typeName = String($opt.data('name') || $opt.text() || 'тип не выбран');
                    var pricePerCut = parseFloat($opt.data('price')) || 0;
                    var lengths = parseCutLengths($cuts.val());
                    var sum = lengths.reduce(function(acc, v) { return acc + v; }, 0);
                    var pieceError = false;
                    var lengthError = false;
                    var typeError = false;

                    if (qty < 0) {
                        qty = 0;
                        $qty.val('0');
                    }

                    used += qty;

                    if (qty > 0 && !typeCode) {
                        typeError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': выберите тип резки');
                    }

                    if (lengths.length && stock > 0 && sum - stock > 0.0001) {
                        lengthError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': сумма кусков ' + formatMeters(sum) + ' м больше длины прутка ' + formatMeters(stock) + ' м');
                    }

                    if (qty > 0 && !lengths.length && String($cuts.val() || '').trim() !== '') {
                        pieceError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': укажите длины кусков числом');
                    }

                    $part.toggleClass('is-invalid', lengthError || pieceError || typeError);
                    $qty.toggleClass('is-invalid', false);
                    $type.toggleClass('is-invalid', typeError);
                    $cuts.toggleClass('is-invalid', lengthError || pieceError);

                    if (!lengths.length) {
                        $preview.html('<span class="cutting-part__preview-empty">Укажите длины кусков — появится расчёт</span>');
                        return;
                    }

                    var cutsPerPiece = getCutCount(lengths);
                    var cutsTotal = cutsPerPiece * Math.max(qty, 0);
                    var partCost = cutsTotal * pricePerCut;
                    totalCuts += cutsTotal;
                    totalCost += partCost;

                    var remainder = stock > 0 ? Math.max(0, stock - sum) : null;
                    var chips = lengths.map(function(len) {
                        return '<span class="cutting-chip">' + formatMeters(len) + ' м</span>';
                    }).join('<span class="cutting-chip-sep">+</span>');

                    var meta = '<span class="cutting-chip cutting-chip--type">' + typeName + '</span> · ' +
                        'с одной штуки: ' + chips +
                        ' = <strong>' + formatMeters(sum) + ' м</strong>';
                    if (remainder !== null) {
                        meta += ', остаток прутка: <strong>' + formatMeters(remainder) + ' м</strong>';
                    }
                    meta += '<br>Резов: <strong>' + cutsPerPiece + '</strong>';
                    if (pricePerCut > 0) {
                        meta += ' × ' + formatMoney(pricePerCut);
                        if (qty > 1) {
                            meta += ' × ' + qty + ' шт';
                        }
                        meta += ' = <strong class="cutting-part__cost">' + formatMoney(partCost) + '</strong>';
                    } else {
                        meta += ' (цена типа резки не задана)';
                    }
                    if (qty > 1) {
                        meta += '<br>Всего по партии: <strong>' + formatMeters(sum * qty) + ' м</strong> (' + qty + ' × ' + formatMeters(sum) + ' м)';
                    }

                    $preview.html(meta);
                });

                if (used > availableFull) {
                    hasInvalidPart = true;
                    errors.unshift('К резке выбрано ' + used + ' шт, а в корзине только ' + availableFull + ' целых шт. Лишнее будет урезано.');
                    var overflow = used - availableFull;
                    $($plan.find('[data-entity="cutting-part"]').get().reverse()).each(function() {
                        if (overflow <= 0) {
                            return false;
                        }
                        var $qty = $(this).find('[data-entity="cutting-part-qty"]');
                        var qty = parseInt($qty.val(), 10) || 0;
                        var cut = Math.min(qty, overflow);
                        $qty.val(String(qty - cut)).addClass('is-invalid');
                        overflow -= cut;
                    });
                    needRecalc = true;
                }

                if (needRecalc && !isRetry) {
                    return refreshCuttingPlan(id, true);
                }

                var rest = Math.max(0, availableFull - used);
                $plan.find('[data-entity="cutting-summary-used"]').text(used + ' шт');
                $plan.find('[data-entity="cutting-summary-rest"]').text(rest + ' шт');
                var $cost = $plan.find('[data-entity="cutting-summary-cost"]');
                if (totalCuts > 0) {
                    $cost.text(formatMoney(totalCost) + ' (' + totalCuts + ' рез.)');
                } else {
                    $cost.text('0 ₽');
                }
                updateBasketTotalWithCutting();

                var $error = $plan.find('[data-entity="cutting-plan-error"]');
                if (errors.length) {
                    $error.html(errors.join('<br>')).prop('hidden', false);
                } else {
                    $error.empty().prop('hidden', true);
                }

                return { ok: !hasInvalidPart || used <= available, errors: errors };
            }

            function attachCuttingRows() {
                $('[data-entity="cutting-plan-row"]').each(function() {
                    var id = $(this).data('id');
                    var $item = $('#basket-item-' + id);
                    if ($item.length && $item.next()[0] !== this) {
                        $item.after(this);
                    }
                });
            }

            var cuttingSaveTimers = {};
            var cuttingSaveXhrs = {};
            var cuttingStatusTimers = {};

            function setCuttingStatus(id, text, kind) {
                var $status = $('[data-entity="cutting-plan-status"][data-id="' + id + '"]');
                if (!$status.length) {
                    return;
                }
                clearTimeout(cuttingStatusTimers[id]);
                if (!text) {
                    $status.prop('hidden', true).text('').removeClass('is-saving is-saved is-error');
                    return;
                }
                $status
                    .text(text)
                    .prop('hidden', false)
                    .removeClass('is-saving is-saved is-error')
                    .addClass(kind ? 'is-' + kind : '');
                if (kind === 'saved') {
                    cuttingStatusTimers[id] = setTimeout(function() {
                        setCuttingStatus(id, '');
                    }, 1800);
                }
            }

            function hasHardCuttingErrors(errors) {
                return (errors || []).some(function(msg) {
                    return msg.indexOf('больше длины прутка') !== -1
                        || msg.indexOf('укажите длины') !== -1
                        || msg.indexOf('выберите тип резки') !== -1;
                });
            }

            function saveCuttingPlan(id, options) {
                options = options || {};
                var immediate = !!options.immediate;
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$plan.length) {
                    return;
                }

                var run = function() {
                    var state = refreshCuttingPlan(id);
                    var enabled = options.enabled
                        || ($plan.attr('data-enabled') === 'Y' ? 'Y' : 'N');

                    // пока план явно кривой — не пишем в корзину, чтобы не сохранить мусор
                    if (enabled === 'Y' && hasHardCuttingErrors(state.errors)) {
                        setCuttingStatus(id, 'Исправьте ошибки', 'error');
                        return;
                    }

                    var planText = enabled === 'Y' ? buildPlanText($plan) : '';
                    setCuttingStatus(id, 'Сохраняем…', 'saving');

                    if (cuttingSaveXhrs[id] && cuttingSaveXhrs[id].abort) {
                        cuttingSaveXhrs[id].abort();
                    }

                    cuttingSaveXhrs[id] = $.get("/ajax/update_cutting_plan_in_cart.php", {
                        id: id,
                        enabled: enabled,
                        plan: planText
                    }, function(data) {
                        if (data && data.success === true) {
                            $plan.attr('data-enabled', enabled);
                            $plan.find('[data-entity="cutting-parts"]').attr('data-plan', planText);
                            var $btn = $('#basket-item-' + id).find('[data-entity="cutting-plan-toggle"]');
                            if (enabled === 'Y') {
                                $btn.addClass('is-active');
                            } else {
                                $btn.removeClass('is-active').attr('aria-expanded', 'false');
                            }
                            setCuttingStatus(id, 'Сохранено', 'saved');
                        } else {
                            setCuttingStatus(id, 'Не удалось сохранить', 'error');
                        }
                    }, "json").fail(function(xhr, status) {
                        if (status === 'abort') {
                            return;
                        }
                        setCuttingStatus(id, 'Не удалось сохранить', 'error');
                    });
                };

                clearTimeout(cuttingSaveTimers[id]);
                if (immediate) {
                    run();
                } else {
                    setCuttingStatus(id, 'Сохраним…', 'saving');
                    cuttingSaveTimers[id] = setTimeout(run, 650);
                }
            }

            function openCuttingPlan(id, open) {
                var $item = $('#basket-item-' + id);
                var $btn = $item.find('[data-entity="cutting-plan-toggle"]');
                var $row = $('#basket-item-' + id + '-cutting');
                var $plan = $row.find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$row.length || !$plan.length) {
                    return;
                }

                attachCuttingRows();

                if (open) {
                    $row.prop('hidden', false).addClass('is-open');
                    $plan.addClass('is-open').attr('data-enabled', 'Y');
                    $btn.addClass('is-active').attr('aria-expanded', 'true');
                    fillPartsFromPlan($plan.find('[data-entity="cutting-parts"]'));
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id, { immediate: true, enabled: 'Y' });
                } else {
                    // свернуть панель — резка остаётся включённой
                    $row.prop('hidden', true).removeClass('is-open');
                    $plan.removeClass('is-open');
                    $btn.attr('aria-expanded', 'false');
                }
            }

            $("#basket-root")
                .on("click", '[data-entity="cutting-plan-toggle"]', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var $row = $('#basket-item-' + id + '-cutting');
                    openCuttingPlan(id, $row.prop('hidden'));
                })
                .on("click", '[data-entity="cutting-part-add"]', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var $parts = $('[data-entity="cutting-parts"][data-id="' + id + '"]');
                    var $tpl = $parts.find('[data-entity="cutting-part"]').first().clone();
                    $tpl.removeClass('is-invalid');
                    $tpl.find('input').val('').removeClass('is-invalid');
                    $tpl.find('[data-entity="cutting-part-qty"]').val('1');
                    $tpl.find('[data-entity="cutting-part-preview"]').empty();
                    $parts.append($tpl);
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id);
                })
                .on("click", '[data-entity="cutting-part-remove"]', function(e) {
                    e.preventDefault();
                    var $part = $(this).closest('[data-entity="cutting-part"]');
                    var $parts = $part.closest('[data-entity="cutting-parts"]');
                    var id = $parts.data('id');
                    if ($parts.find('[data-entity="cutting-part"]').length > 1) {
                        $part.remove();
                    } else {
                        $part.find('[data-entity="cutting-part-qty"]').val('1');
                        $part.find('[data-entity="cutting-part-cuts"]').val('');
                        $part.find('[data-entity="cutting-part-preview"]').empty();
                        $part.removeClass('is-invalid');
                    }
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id, { immediate: true });
                })
                .on("input change", '[data-entity="cutting-part-qty"], [data-entity="cutting-part-cuts"], [data-entity="cutting-part-type"]', function() {
                    var id = $(this).closest('[data-entity="cutting-plan"]').data('id');
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id);
                })
                .on("click", '[data-entity="cutting-cancel"]', function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                    $plan.attr('data-enabled', 'N');
                    $plan.find('[data-entity="cutting-summary-cost"]').text('0 ₽');
                    $plan.find('[data-entity="cutting-summary-used"]').text('0 шт');
                    openCuttingPlan(id, false);
                    saveCuttingPlan(id, { immediate: true, enabled: 'N' });
                    updateBasketTotalWithCutting();
                });

            // после отрисовки/перерисовки корзины — держим блок резки сразу под товаром
            if (BX.Sale && BX.Sale.BasketComponent) {
                var component = BX.Sale.BasketComponent;
                var originalCreate = component.createBasketItem;
                var originalRedraw = component.redrawBasketItemNode;

                component.createBasketItem = function() {
                    originalCreate.apply(this, arguments);
                    attachCuttingRows();
                };

                component.redrawBasketItemNode = function(itemId) {
                    var oldCut = document.getElementById('basket-item-' + itemId + '-cutting');
                    if (oldCut) {
                        oldCut.parentNode.removeChild(oldCut);
                    }
                    originalRedraw.apply(this, arguments);
                    attachCuttingRows();
                };
            }

            setTimeout(function() {
                attachCuttingRows();
                $('#basket-root [data-entity="cutting-plan-row"].is-open').each(function() {
                    openCuttingPlan($(this).data('id'), true);
                });
                updateBasketTotalWithCutting();
            }, 0);
        });
	</script>
	<?
}
elseif ($arResult['EMPTY_BASKET'])
{
	include(Main\Application::getDocumentRoot().$templateFolder.'/empty.php');
}
else
{
	ShowError($arResult['ERROR_MESSAGE']);
}