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

            function parseCutLengthTokens(raw) {
                return String(raw || '')
                    .replace(/,/g, ' ')
                    .replace(/\+/g, ' ')
                    .split(/\s+/)
                    .map(function(v) { return v.trim(); })
                    .filter(Boolean);
            }

            function validateCutLengths(raw) {
                var tokens = parseCutLengthTokens(raw);
                var lengths = [];
                var hasFraction = false;
                var hasInvalid = false;

                tokens.forEach(function(token) {
                    if (/^\d+$/.test(token)) {
                        var n = parseInt(token, 10);
                        if (n > 0) {
                            lengths.push(n);
                        } else {
                            hasInvalid = true;
                        }
                        return;
                    }
                    if (/^\d+[.,]\d+$/.test(token)) {
                        hasFraction = true;
                        return;
                    }
                    hasInvalid = true;
                });

                return {
                    lengths: lengths,
                    hasFraction: hasFraction,
                    hasInvalid: hasInvalid
                };
            }

            function parseCutLengths(raw) {
                return validateCutLengths(raw).lengths;
            }

            // Мягкая очистка во время ввода: не пересобираем «1 + 2»,
            // иначе курсор/черновик (пробел, «+» перед цифрой) сбрасываются.
            function sanitizeCutLengthsInput(raw) {
                return String(raw || '')
                    .replace(/,/g, ' ')
                    .replace(/[^\d\s+]/g, '');
            }

            function setInputValuePreserveCaret($input, nextValue) {
                var el = $input.get(0);
                if (!el) {
                    $input.val(nextValue);
                    return;
                }

                var prev = String($input.val() || '');
                if (prev === nextValue) {
                    return;
                }

                var start = typeof el.selectionStart === 'number' ? el.selectionStart : prev.length;
                var end = typeof el.selectionEnd === 'number' ? el.selectionEnd : start;
                var removedBefore = 0;
                var i = 0;
                var j = 0;

                while (i < start && j < nextValue.length) {
                    if (prev.charAt(i) === nextValue.charAt(j)) {
                        i++;
                        j++;
                        continue;
                    }
                    if (/[^\d\s+]/.test(prev.charAt(i)) || prev.charAt(i) === ',') {
                        removedBefore++;
                        i++;
                        continue;
                    }
                    break;
                }
                if (i < start) {
                    removedBefore += start - i;
                }

                $input.val(nextValue);
                var caret = Math.max(0, Math.min(nextValue.length, start - removedBefore));
                try {
                    el.setSelectionRange(caret, Math.max(0, Math.min(nextValue.length, end - removedBefore)));
                } catch (e) {}
            }

            function normalizeCutLengthsText(raw) {
                var tokens = parseCutLengthTokens(raw);
                if (!tokens.length) {
                    return '';
                }

                return tokens.map(function(token) {
                    var n = parseFloat(String(token).replace(',', '.'));
                    if (isNaN(n) || n <= 0) {
                        return null;
                    }
                    return String(Math.round(n));
                }).filter(function(v) { return v !== null; }).join(' + ');
            }

            function formatCutLength(value) {
                return String(Math.round(value));
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
                var stock = parseFloat($item.attr('data-cutting-stock') || '0');
                if (!isNaN(stock) && stock > 0) {
                    return stock;
                }

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
                        lines.push(qty + ' шт | ' + typeCode + ' | ' + lengths.map(formatCutLength).join(' + ') + ' | ' + typeName);
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
                        $part.find('[data-entity="cutting-part-cuts"]').val(normalizeCutLengthsText(parts[2]));
                        return;
                    }

                    // old format fallback: 1 шт — 2 + 3 м  OR  1 - 1+1+10
                    var m = line.match(/^(\d+)\s*шт\s*[—\-:]?\s*(.+)$/i);
                    if (m) {
                        $part.find('[data-entity="cutting-part-qty"]').val(m[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(normalizeCutLengthsText(m[2].replace(/\s*м\s*$/i, '').trim()));
                        return;
                    }

                    m = line.match(/^(\d+)\s*[-–—]\s*(.+)$/);
                    if (m) {
                        $part.find('[data-entity="cutting-part-qty"]').val(m[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(normalizeCutLengthsText(m[2].trim()));
                        return;
                    }

                    $part.find('[data-entity="cutting-part-qty"]').val('1');
                    $part.find('[data-entity="cutting-part-cuts"]').val(normalizeCutLengthsText(line));
                });
            }

            function formatMoney(value) {
                var n = Math.round(Number(value) || 0);
                return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' ₽';
            }

            function getCutCount(lengths) {
                // 1+5 → 2 куска, 1 рез; 1+1+10 → 3 куска, 2 реза
                if (!Array.isArray(lengths) || lengths.length <= 1) {
                    return 0;
                }
                return lengths.length - 1;
            }

            function parseMoneyFromText(text) {
                var s = String(text || '').replace(/\s/g, '');
                // берём только числа и точку/запятую
                var m = s.match(/([\d.,]+)/);
                if (!m) return 0;
                var num = parseFloat(m[1].replace(',', '.'));
                return isNaN(num) ? 0 : num;
            }

            function getAutoHalfCutInfo($item) {
                var isSheet = String($item.attr('data-is-sheet') || '0') === '1';
                if (isSheet) {
                    return { cuts: 0, cost: 0, fraction: 0 };
                }

                var availableInfo = getAvailablePieces($item);
                if (availableInfo.fraction <= 0.0001) {
                    return { cuts: 0, cost: 0, fraction: 0 };
                }

                var price = parseFloat($item.attr('data-default-cut-price') || '0') || 0;
                return { cuts: 1, cost: price, fraction: availableInfo.fraction };
            }

            function updateBasketTotalWithCutting() {
                var cuttingTotal = 0;
                var countedHalfCutIds = {};

                $('[data-entity="cutting-plan"][data-enabled="Y"]').each(function() {
                    var id = String($(this).attr('data-id') || $(this).data('id') || '');
                    var $cost = $(this).find('[data-entity="cutting-summary-cost"]');
                    cuttingTotal += parseMoneyFromText($cost.text());
                    if (id) {
                        countedHalfCutIds[id] = true;
                    }
                });

                // неполная штука = 1 рез даже без открытого плана резки
                $('[data-entity="basket-item"][data-half-piece-cut="1"]').each(function() {
                    var id = String($(this).attr('data-id') || $(this).data('id') || '');
                    if (id && countedHalfCutIds[id]) {
                        return;
                    }
                    var auto = getAutoHalfCutInfo($(this));
                    cuttingTotal += auto.cost;
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

                var basicSheet = String($item.attr('data-basic-sheet') || '0') === '1';
                var availableInfo = getAvailablePieces($item);
                var availableFull = availableInfo.full;
                var stock = getStockLength($item);
                var used = 0;
                var totalCost = 0;
                var totalCuts = 0;
                var totalComplexCuts = 0;
                var hasComplexCutSummary = false;
                var errors = [];
                var hasInvalidPart = false;
                var needRecalc = false;
                var autoHalfCut = getAutoHalfCutInfo($item);

                // Визуализация: 11.92 => 11 целых + неполная 0.92 шт
                if (availableInfo.fraction > 0.0001) {
                    var totalText = availableFull + ' шт + неполная ' + formatMeters(availableInfo.fraction) + ' шт';
                    if (autoHalfCut.cuts > 0) {
                        totalText += ' → 1 рез';
                    }
                    $plan.find('[data-entity="cutting-summary-total"]').text(totalText);
                } else {
                    $plan.find('[data-entity="cutting-summary-total"]').text(availableFull + ' шт');
                }
                $plan.find('[data-entity="cutting-summary-stock"]').text(stock > 0 ? (formatMeters(stock) + ' м') : '—');
                $item.attr('data-half-piece-cut', autoHalfCut.cuts > 0 ? '1' : '0');

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
                    var cutsState = validateCutLengths($cuts.val());
                    var lengths = cutsState.lengths;
                    var sum = lengths.reduce(function(acc, v) { return acc + v; }, 0);
                    var pieceError = false;
                    var lengthError = false;
                    var typeError = false;
                    var fractionError = false;

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
                        errors.push('Партия ' + (index + 1) + ': сумма кусков ' + formatMeters(sum) + ' м больше длины ' + formatMeters(stock) + ' м');
                    }

                    if (cutsState.hasFraction) {
                        fractionError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': длины кусков — только целые метры');
                    }

                    if (qty > 0 && !lengths.length && String($cuts.val() || '').trim() !== '') {
                        pieceError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': укажите длины кусков целыми числами');
                    }

                    $part.toggleClass('is-invalid', lengthError || pieceError || typeError || fractionError);
                    $qty.toggleClass('is-invalid', false);
                    $type.toggleClass('is-invalid', typeError);
                    $cuts.toggleClass('is-invalid', lengthError || pieceError || fractionError);

                    if (!lengths.length) {
                        $preview.html('<span class="cutting-part__preview-empty">Укажите длины кусков — появится расчёт</span>');
                        return;
                    }

                    var cutsPerPiece = getCutCount(lengths);
                    var isComplexCut = basicSheet && cutsPerPiece >= 2;
                    var cutsTotal = isComplexCut ? 0 : cutsPerPiece * Math.max(qty, 0);
                    var partCost = isComplexCut ? 0 : cutsTotal * pricePerCut;
                    if (isComplexCut) {
                        hasComplexCutSummary = true;
                        totalComplexCuts += cutsPerPiece * Math.max(qty, 0);
                    }
                    totalCuts += cutsTotal;
                    totalCost += partCost;

                    var remainder = stock > 0 ? Math.max(0, stock - sum) : null;
                    var chips = lengths.map(function(len) {
                        return '<span class="cutting-chip">' + formatCutLength(len) + ' м</span>';
                    }).join('<span class="cutting-chip-sep">+</span>');

                    var meta = '<span class="cutting-chip cutting-chip--type">' + typeName + '</span> · ' +
                        'с одной штуки: ' + chips +
                        ' = <strong>' + formatCutLength(sum) + ' м</strong>';
                    if (remainder !== null) {
                        meta += ', остаток: <strong>' + formatCutLength(remainder) + ' м</strong>';
                    }
                    meta += '<br>Резов: <strong>' + cutsPerPiece + '</strong>';
                    if (isComplexCut) {
                        meta += ' — <strong class="cutting-part__cost">+10% к цене за м²</strong>';
                    } else if (pricePerCut > 0) {
                        meta += ' × ' + formatMoney(pricePerCut);
                        if (qty > 1) {
                            meta += ' × ' + qty + ' шт';
                        }
                        meta += ' = <strong class="cutting-part__cost">' + formatMoney(partCost) + '</strong>';
                    } else {
                        meta += ' (цена типа резки не задана)';
                    }
                    if (qty > 1) {
                        meta += '<br>Всего по партии: <strong>' + formatCutLength(sum * qty) + ' м</strong> (' + qty + ' × ' + formatCutLength(sum) + ' м)';
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

                totalCuts += autoHalfCut.cuts;
                totalCost += autoHalfCut.cost;

                var $cost = $plan.find('[data-entity="cutting-summary-cost"]');
                if (hasComplexCutSummary) {
                    $cost.text(totalComplexCuts + ' рез. — +10% к цене за м²');
                } else if (totalCuts > 0) {
                    var costLabel = formatMoney(totalCost) + ' (' + totalCuts + ' рез.)';
                    if (autoHalfCut.cuts > 0) {
                        costLabel += autoHalfCut.cost > 0
                            ? ' · в т.ч. 1 рез за неполную ' + formatMeters(autoHalfCut.fraction) + ' шт'
                            : ' · 1 рез за неполную ' + formatMeters(autoHalfCut.fraction) + ' шт';
                    }
                    $cost.text(costLabel);
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
            var pendingPriceRefresh = {};

            function requestBasketPriceRefresh(id) {
                if ($('[data-entity="cutting-part-cuts"]:focus').length) {
                    pendingPriceRefresh[id] = true;
                    return;
                }

                if (BX.Sale && BX.Sale.BasketComponent) {
                    BX.Sale.BasketComponent.sendRequest('recalculateAjax', {
                        fullRecalculation: 'Y'
                    });
                }
            }

            function flushPendingPriceRefresh(id) {
                if (!pendingPriceRefresh[id]) {
                    return;
                }

                delete pendingPriceRefresh[id];
                if (BX.Sale && BX.Sale.BasketComponent) {
                    BX.Sale.BasketComponent.sendRequest('recalculateAjax', {
                        fullRecalculation: 'Y'
                    });
                }
            }

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
                        || msg.indexOf('только целые') !== -1
                        || msg.indexOf('выберите тип резки') !== -1;
                });
            }

            function basicSheetQuantityNeedsPlus10Price($item) {
                if (!$item.length || String($item.attr('data-basic-sheet') || '0') !== '1') {
                    return false;
                }

                var length = parseFloat($item.attr('data-length-per-piece') || '0');
                var id = $item.data('id');
                var $qty = $('#basket-item-quantity-' + id);
                var meters = parseFloat($qty.attr('data-value') || $qty.val() || '0');

                if (length <= 0 || !(meters > 0)) {
                    return false;
                }

                var pieces = meters / length;
                return Math.abs(pieces - Math.round(pieces)) > 0.0001;
            }

            function updateBasketItemPriceNote(id, hasSurcharge10) {
                var $item = $('#basket-item-' + id);
                if (!$item.length) {
                    return;
                }

                var plus10 = !!hasSurcharge10 || basicSheetQuantityNeedsPlus10Price($item);
                var $notes = $item.find('.basket-item-custom-notes--price');
                var label = plus10 ? 'Цена за метр +10%' : 'Цена за метр';

                if ($notes.length) {
                    $notes.text(label);
                } else {
                    $item.find('.cart-table_col-price .basket-item-cell-inner').append(
                        '<div class="basket-item-custom-notes basket-item-custom-notes--price">' + label + '</div>'
                    );
                }
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
                    var savedPlan = String($plan.find('[data-entity="cutting-parts"]').attr('data-plan') || '');
                    var currentEnabled = $plan.attr('data-enabled') === 'Y' ? 'Y' : 'N';
                    if (!options.force && planText === savedPlan && enabled === currentEnabled) {
                        setCuttingStatus(id, '');
                        return;
                    }

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
                            if (String($('#basket-item-' + id).attr('data-basic-sheet') || '0') === '1') {
                                updateBasketItemPriceNote(id, !!(data && data.cuttingSurcharge10));
                            }
                            if (data.needPriceRefresh) {
                                requestBasketPriceRefresh(id);
                            }
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

            function openCuttingPlan(id, open, options) {
                options = options || {};
                var skipSave = !!options.skipSave;
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
                    if (!skipSave) {
                        saveCuttingPlan(id, { immediate: true, enabled: 'Y' });
                    }
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
                .on("input", '[data-entity="cutting-part-qty"], [data-entity="cutting-part-cuts"], [data-entity="cutting-part-type"]', function() {
                    if ($(this).is('[data-entity="cutting-part-cuts"]')) {
                        var $cuts = $(this);
                        var sanitized = sanitizeCutLengthsInput($cuts.val());
                        if ($cuts.val() !== sanitized) {
                            setInputValuePreserveCaret($cuts, sanitized);
                        }
                    }
                    var id = $(this).closest('[data-entity="cutting-plan"]').data('id');
                    refreshCuttingPlan(id);
                })
                .on("blur", '[data-entity="cutting-part-cuts"]', function() {
                    var $cuts = $(this);
                    var normalized = normalizeCutLengthsText($cuts.val());
                    if ($cuts.val() !== normalized) {
                        $cuts.val(normalized);
                    }
                    var id = $(this).closest('[data-entity="cutting-plan"]').data('id');
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id, { immediate: true });
                    flushPendingPriceRefresh(id);
                })
                .on("change", '[data-entity="cutting-part-qty"], [data-entity="cutting-part-type"]', function() {
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
                    updateBasketTotalWithCutting();
                };

                component.redrawBasketItemNode = function(itemId) {
                    var oldCut = document.getElementById('basket-item-' + itemId + '-cutting');
                    if (oldCut) {
                        oldCut.parentNode.removeChild(oldCut);
                    }
                    originalRedraw.apply(this, arguments);
                    attachCuttingRows();
                    var $open = $('#basket-item-' + itemId + '-cutting.is-open');
                    if ($open.length) {
                        openCuttingPlan(itemId, true, { skipSave: true });
                    }
                    updateBasketTotalWithCutting();
                };
            }

            setTimeout(function() {
                attachCuttingRows();
                $('#basket-root [data-entity="cutting-plan-row"].is-open').each(function() {
                    openCuttingPlan($(this).data('id'), true, { skipSave: true });
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