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

$cartDisplayMode = (isset($arParams['CART_DISPLAY_MODE']) && $arParams['CART_DISPLAY_MODE'] === 'page')
	? 'page'
	: 'overlay';
if ($cartDisplayMode !== 'page') {
	$curPage = (string)$APPLICATION->GetCurPage(false);
	if (strpos($curPage, '/cart') === 0) {
		$cartDisplayMode = 'page';
	}
}
$isCartPage = $cartDisplayMode === 'page';

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

	<div class="container basket-root--<?=$cartDisplayMode?>" id="basket-root" data-cart-mode="<?=$cartDisplayMode?>">

        <?php if (!$isCartPage): ?>
        <div class="cart-close"></div>
        <div class="cart-content_header">
            <div class="cart-content_title">Состав корзины</div>
            <ul class="cart-steps">
                <li class="cart-step_item active active-mod">Шаг 1</li>
                <li class="cart-step_item">Шаг 2</li>
                <li class="cart-step_item">Шаг 3</li>
            </ul>
        </div>
        <?php endif; ?>

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
		try {
			BX.Sale.BasketComponent.init({
				result: <?=CUtil::PhpToJSObject($arResult, false, false, true)?>,
				params: <?=CUtil::PhpToJSObject($arParams)?>,
				template: '<?=CUtil::JSEscape($signedTemplate)?>',
				signedParamsString: '<?=CUtil::JSEscape($signedParams)?>',
				siteId: '<?=CUtil::JSEscape($component->getSiteId())?>',
				siteTemplateId: '<?=CUtil::JSEscape($component->getSiteTemplateId())?>',
				templateFolder: '<?=CUtil::JSEscape($templateFolder)?>'
			});
		} catch (e) {
			if (window.console && console.error) {
				console.error('BasketComponent.init', e);
			}
		}

        (function bootMetplusBasketCutting(attempt) {
            if (typeof jQuery === 'undefined') {
                if (attempt < 100) {
                    setTimeout(function() { bootMetplusBasketCutting(attempt + 1); }, 50);
                }
                return;
            }

            jQuery(function($) {
            // Делегирование с document — переживает перерисовку корзины и AJAX-overlay
            var cuttingNs = '.metplusBasketCutting';

            $(document).off(cuttingNs);

            $(document).on("click" + cuttingNs, "#basket-root .cutting-service-options", function(e) {
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

            function getPartTarget($part) {
                return String($part.attr('data-target') || 'full') === 'incomplete' ? 'incomplete' : 'full';
            }

            function getRemnantMeters($item, availableInfo) {
                var length = parseFloat($item.attr('data-length-per-piece') || '0') || 0;
                if (length <= 0 || !availableInfo || availableInfo.fraction <= 0.0001) {
                    return 0;
                }
                // Остаток в метрах из точного qty, не fraction*L (избегаем 0.667*12=8.004)
                var id = $item.attr('data-id') || $item.data('id');
                var qtyEl = document.getElementById('basket-item-quantity-' + id);
                var meters = 0;
                if (qtyEl) {
                    meters = parseFloat(String(qtyEl.getAttribute('data-value') || qtyEl.value || '').replace(/\s/g, '').replace(',', '.'));
                }
                if (!(meters > 0)) {
                    meters = availableInfo.exact * length;
                }
                var remnant = Math.max(0, meters - availableInfo.full * length);
                remnant = Math.round(remnant * 1000) / 1000;
                if (Math.abs(remnant - Math.round(remnant)) < 0.001) {
                    remnant = Math.round(remnant);
                }
                return remnant;
            }

            function applyPartTargetUi($part, target) {
                target = target === 'incomplete' ? 'incomplete' : 'full';
                $part.attr('data-target', target);
                var $label = $part.find('[data-entity="cutting-part-target-label"]');
                var $qtyField = $part.find('[data-entity="cutting-part-qty-field"]');
                var $qtyFixed = $part.find('[data-entity="cutting-part-qty-fixed"]');
                var $qty = $part.find('[data-entity="cutting-part-qty"]');
                if (target === 'incomplete') {
                    $label.text('Неполная');
                    $qtyField.prop('hidden', true);
                    $qtyFixed.prop('hidden', false);
                    $qty.val('1');
                } else {
                    $label.text('Целая');
                    $qtyField.prop('hidden', false);
                    $qtyFixed.prop('hidden', true);
                }
            }

            /**
             * Шаг 1 → 2: убрать «лишнюю» дефолтную партию, если план ещё пустой.
             * Если партии уже заполнены (куски/несколько строк) — НЕ трогаем,
             * иначе «Назад» в «Что режем» + «Далее» сносит весь план до 1 строки.
             */
            function syncPartsToSelectedTarget(id) {
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$plan.length) {
                    return;
                }
                var $parts = $plan.find('[data-entity="cutting-parts"]');
                var $all = $parts.find('[data-entity="cutting-part"]');
                if (!$all.length) {
                    return;
                }

                var hasConfigured = false;
                $all.each(function() {
                    var cuts = String($(this).find('[data-entity="cutting-part-cuts"]').val() || '').trim();
                    if (cuts !== '') {
                        hasConfigured = true;
                        return false;
                    }
                });
                var savedPlan = String($parts.attr('data-plan') || '').trim();
                if (hasConfigured || savedPlan || $all.length > 1) {
                    return;
                }

                var $incompleteBtn = $plan.find('[data-entity="cutting-target-incomplete"]');
                var wantIncomplete = $incompleteBtn.hasClass('is-active') && !$incompleteBtn.prop('hidden');
                var wantTarget = wantIncomplete ? 'incomplete' : 'full';

                var $keep = $all.filter('[data-target="' + wantTarget + '"]').first();
                if (!$keep.length) {
                    $keep = $all.first();
                    applyPartTargetUi($keep, wantTarget);
                    $keep.find('[data-entity="cutting-part-cuts"]').val('');
                    $keep.find('[data-entity="cutting-part-preview"]').empty();
                    $keep.removeClass('is-invalid');
                    if (wantTarget === 'full') {
                        $keep.find('[data-entity="cutting-part-qty"]').val('1');
                    }
                }

                $all.each(function() {
                    if (this !== $keep[0]) {
                        $(this).remove();
                    }
                });

                applyPartTargetUi($keep, wantTarget);
                if (wantTarget === 'incomplete') {
                    $keep.find('[data-entity="cutting-part-qty"]').val('1');
                }
            }

            function buildPlanText($plan) {
                var lines = [];
                $plan.find('[data-entity="cutting-part"]').each(function() {
                    var $part = $(this);
                    var target = getPartTarget($part);
                    var qty = parseInt($part.find('[data-entity="cutting-part-qty"]').val(), 10) || 0;
                    var $type = $part.find('[data-entity="cutting-part-type"]');
                    var typeCode = String($type.val() || '');
                    var typeName = String($type.find('option:selected').data('name') || $type.find('option:selected').text() || typeCode);
                    var lengths = parseCutLengths($part.find('[data-entity="cutting-part-cuts"]').val());
                    if (!lengths.length || !typeCode) {
                        return;
                    }
                    if (target === 'incomplete') {
                        lines.push('неполная | ' + typeCode + ' | ' + lengths.map(formatCutLength).join(' + ') + ' | ' + typeName);
                    } else if (qty > 0) {
                        lines.push(qty + ' шт | ' + typeCode + ' | ' + lengths.map(formatCutLength).join(' + ') + ' | ' + typeName);
                    }
                });
                return lines.join('\n');
            }

            /** Все партии из DOM, включая черновики без кусков («+ Ещё целые»). */
            function serializeCuttingPartsState($plan) {
                var out = [];
                $plan.find('[data-entity="cutting-part"]').each(function() {
                    var $part = $(this);
                    out.push({
                        target: getPartTarget($part),
                        qty: String($part.find('[data-entity="cutting-part-qty"]').val() || '1'),
                        type: String($part.find('[data-entity="cutting-part-type"]').val() || ''),
                        cuts: String($part.find('[data-entity="cutting-part-cuts"]').val() || '')
                    });
                });
                return out;
            }

            function applyCuttingPartsState($plan, partsState) {
                if (!partsState || !partsState.length) {
                    return false;
                }
                var $parts = $plan.find('[data-entity="cutting-parts"]');
                var $first = $parts.find('[data-entity="cutting-part"]').first();
                if (!$first.length) {
                    return false;
                }
                $parts.find('[data-entity="cutting-part"]').slice(1).remove();
                partsState.forEach(function(state, index) {
                    var $part = index === 0 ? $first : $first.clone(false, false);
                    if (index > 0) {
                        $parts.append($part);
                    }
                    $part.removeClass('is-invalid is-incomplete-target');
                    applyPartTargetUi($part, state.target === 'incomplete' ? 'incomplete' : 'full');
                    $part.find('[data-entity="cutting-part-qty"]').val(state.qty || '1');
                    if (state.type) {
                        $part.find('[data-entity="cutting-part-type"]').val(state.type);
                    }
                    $part.find('[data-entity="cutting-part-cuts"]').val(state.cuts || '');
                    $part.find('[data-entity="cutting-part-preview"]').empty();
                });
                return true;
            }

            var cuttingPartsStateCache = {};

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

                    // incomplete: неполная | CODE | 2 + 3 | Name
                    var parts = line.split('|').map(function(v) { return v.trim(); });
                    if (parts.length >= 3 && /^неполн/i.test(parts[0])) {
                        applyPartTargetUi($part, 'incomplete');
                        $part.find('[data-entity="cutting-part-qty"]').val('1');
                        $part.find('[data-entity="cutting-part-type"]').val(parts[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(normalizeCutLengthsText(parts[2]));
                        return;
                    }

                    // new format: 1 шт | CODE | 2.3 + 3.1 | Name
                    if (parts.length >= 3 && /^\d+\s*шт$/i.test(parts[0])) {
                        applyPartTargetUi($part, 'full');
                        $part.find('[data-entity="cutting-part-qty"]').val(parseInt(parts[0], 10) || 1);
                        $part.find('[data-entity="cutting-part-type"]').val(parts[1]);
                        $part.find('[data-entity="cutting-part-cuts"]').val(normalizeCutLengthsText(parts[2]));
                        return;
                    }

                    // old format fallback: 1 шт — 2 + 3 м  OR  1 - 1+1+10
                    applyPartTargetUi($part, 'full');
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

            function planHasIncompleteParty($plan) {
                var found = false;
                $plan.find('[data-entity="cutting-part"]').each(function() {
                    if (getPartTarget($(this)) !== 'incomplete') {
                        return;
                    }
                    var lengths = parseCutLengths($(this).find('[data-entity="cutting-part-cuts"]').val());
                    var typeCode = String($(this).find('[data-entity="cutting-part-type"]').val() || '');
                    if (lengths.length && typeCode) {
                        found = true;
                        return false;
                    }
                });
                return found;
            }

            function getAutoHalfCutInfo($item, $plan) {
                var availableInfo = getAvailablePieces($item);
                if (availableInfo.fraction <= 0.0001) {
                    return { cuts: 0, cost: 0, fraction: 0 };
                }

                // Если неполная уже в плане — авто-рез не добавляем
                if ($plan && $plan.length && planHasIncompleteParty($plan)) {
                    return { cuts: 0, cost: 0, fraction: availableInfo.fraction, planned: true };
                }

                var price = parseFloat($item.attr('data-default-cut-price') || '0') || 0;
                return { cuts: 1, cost: price, fraction: availableInfo.fraction };
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
                var m = s.match(/([\d.,]+)/);
                if (!m) return 0;
                var num = parseFloat(m[1].replace(',', '.'));
                return isNaN(num) ? 0 : num;
            }

            function updateBasketTotalWithCutting() {
                var cuttingTotal = 0;
                var countedHalfCutIds = {};

                $('[data-entity="cutting-plan"][data-enabled="Y"]').each(function() {
                    var id = String($(this).attr('data-id') || $(this).data('id') || '');
                    var $item = id ? $('#basket-item-' + id) : $();
                    // у базового листа резы уже в сумме позиции — не дублировать в футере
                    if ($item.length && String($item.attr('data-basic-sheet') || '0') === '1') {
                        if (id) {
                            countedHalfCutIds[id] = true;
                        }
                        return;
                    }
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
                    if (String($(this).attr('data-basic-sheet') || '0') === '1') {
                        return;
                    }
                    var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                    var auto = getAutoHalfCutInfo($(this), $plan);
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

            function syncBasketItemSumFromCutting(id, grandTotal) {
                if (!(grandTotal > 0) || !id) {
                    return;
                }
                var $item = $('#basket-item-' + id);
                if (!$item.length || String($item.attr('data-basic-sheet') || '0') !== '1') {
                    return;
                }
                var el = document.getElementById('basket-item-sum-price-' + id);
                if (el) {
                    el.textContent = formatMoney(grandTotal);
                }
            }

            function roundMoney(value) {
                return Math.round((value + Number.EPSILON) * 100) / 100;
            }

            function formatPiecesShort(value) {
                if (!(value > 0)) {
                    return '0';
                }
                if (Math.abs(value - Math.round(value)) < 0.001) {
                    return String(Math.round(value));
                }
                return formatMeters(value);
            }

            function updateCuttingCostBreakdown($item, $plan, opts) {
                opts = opts || {};
                var $box = $plan.find('[data-entity="cutting-cost-breakdown"]');
                var $lines = $box.find('[data-entity="cutting-cost-breakdown-lines"]');
                var $total = $box.find('[data-entity="cutting-cost-breakdown-total"]');
                if (!$box.length) {
                    return;
                }

                var basicSheet = String($item.attr('data-basic-sheet') || '0') === '1';
                var isSheet = String($item.attr('data-is-sheet') || '0') === '1';
                var freeCutting = String($item.attr('data-free-cutting') || '0') === '1';
                var length = parseFloat($item.attr('data-length-per-piece') || '0') || 0;
                var baseUnit = parseFloat($item.attr('data-base-meter-price') || '0') || 0;
                var percent = 10;
                var pipeIncompletePercent = (!isSheet && !freeCutting) ? 20 : 0;
                var surchargeUnit = roundMoney(baseUnit * (1 + percent / 100));

                var availableInfo = opts.availableInfo || getAvailablePieces($item);
                var used = opts.used || 0;
                var complexPieces = opts.complexPieces || 0;
                var simplePieces = opts.simplePieces || 0;
                var simpleCuts = opts.simpleCuts || 0;
                var complexCuts = opts.complexCuts || 0;
                var cutFeeTotal = opts.cutFeeTotal || 0;
                var totalCutsCount = opts.totalCuts || 0;
                var incompleteCuts = opts.incompleteCuts || 0;
                var planCuts = Math.max(0, totalCutsCount - incompleteCuts);
                var halfPiecesFlag = !!opts.halfPiecesFlag;
                var incompletePlanned = !!opts.incompletePlanned;

                function costRow(title, formula, sum, note) {
                    return '<div class="cutting-cost-row">'
                        + '<div class="cutting-cost-row__head">'
                        + '<span class="cutting-cost-row__title">' + title + '</span>'
                        + '<strong class="cutting-cost-row__sum">' + formatMoney(sum) + '</strong>'
                        + '</div>'
                        + (formula ? ('<div class="cutting-cost-row__detail">' + formula + '</div>') : '')
                        + (note ? ('<div class="cutting-cost-row__note">' + note + '</div>') : '')
                        + '</div>';
                }

                function finishBreakdown(html, metalAtBase, surchargeOnly, blended, grandTotal) {
                    if (!html) {
                        $box.prop('hidden', true);
                        return;
                    }
                    $lines.html(html);
                    $box.find('[data-entity="cutting-total-metal"]').html(
                        '<span>Металл</span><strong>' + formatMoney(metalAtBase) + '</strong>'
                    );
                    $box.find('[data-entity="cutting-total-surcharge"]').html(
                        '<span>Наценки</span><strong>' + formatMoney(surchargeOnly) + '</strong>'
                    ).show();
                    $box.find('[data-entity="cutting-total-cuts"]').html(
                        '<span>Резы</span><strong>' + formatMoney(cutFeeTotal) + '</strong>'
                    );
                    $box.find('[data-entity="cutting-total-grand"]').html(
                        '<span>Итого</span>'
                        + '<span class="cutting-cost-total-row__grand">'
                        + '<strong>' + formatMoney(grandTotal) + '</strong>'
                        + (blended > 0 ? ('<em>средняя ' + formatMoney(blended) + '/м</em>') : '')
                        + '</span>'
                    );
                    $box.prop('hidden', false);
                    var planId = $plan.attr('data-id') || $plan.data('id') || $item.attr('data-id');
                    syncBasketItemSumFromCutting(planId, grandTotal);
                }

                if (length <= 0 || baseUnit <= 0) {
                    var htmlMin = '';
                    if (cutFeeTotal > 0 || totalCutsCount > 0) {
                        htmlMin += costRow(
                            'Оплата резов', '', cutFeeTotal,
                            totalCutsCount > 0 ? (totalCutsCount + ' рез.') : '', false
                        );
                    }
                    finishBreakdown(htmlMin, 0, 0, 0, cutFeeTotal);
                    return;
                }

                // Не лист — единый UI без «сложной» наценки листа
                if (!basicSheet) {
                    var maxWholeOther = availableInfo.full;
                    var incompletePiecesOther = availableInfo.fraction || 0;
                    var safeUsed = Math.min(Math.max(used, 0), maxWholeOther);
                    var uncutOther = Math.max(0, maxWholeOther - safeUsed);
                    var cutMetersOther = safeUsed * length;
                    var uncutMetersOther = uncutOther * length;
                    var incompleteMetersOther = incompletePiecesOther * length;
                    var incompleteUnitOther = pipeIncompletePercent
                        ? roundMoney(baseUnit * (1 + pipeIncompletePercent / 100))
                        : baseUnit;
                    var cutSumOther = roundMoney(cutMetersOther * baseUnit);
                    var uncutSumOther = roundMoney(uncutMetersOther * baseUnit);
                    var incompleteSumOther = roundMoney(incompleteMetersOther * incompleteUnitOther);
                    var allMetersOther = cutMetersOther + uncutMetersOther + incompleteMetersOther;
                    var metalAtBaseOther = roundMoney(allMetersOther * baseUnit);
                    var surchargeOther = roundMoney(
                        incompleteMetersOther * Math.max(0, incompleteUnitOther - baseUnit)
                    );
                    var metalSumOther = roundMoney(metalAtBaseOther + surchargeOther);
                    var blendedOther = allMetersOther > 0 ? roundMoney(metalSumOther / allMetersOther) : baseUnit;
                    var grandOther = roundMoney(metalSumOther + cutFeeTotal);
                    var htmlOther = '';
                    if (uncutOther > 0.0001) {
                        htmlOther += costRow(
                            'Целые без резки',
                            formatPiecesShort(uncutOther) + ' шт × ' + formatMeters(length) + ' м = '
                                + formatMeters(uncutMetersOther) + ' м × ' + formatMoney(baseUnit),
                            uncutSumOther, '', false
                        );
                    }
                    if (safeUsed > 0.0001) {
                        htmlOther += costRow(
                            'Резанные',
                            formatPiecesShort(safeUsed) + ' шт × ' + formatMeters(length) + ' м = '
                                + formatMeters(cutMetersOther) + ' м × ' + formatMoney(baseUnit),
                            cutSumOther,
                            planCuts > 0 ? ('резов: ' + planCuts) : '',
                            false
                        );
                    }
                    if (incompletePiecesOther > 0.0001) {
                        htmlOther += costRow(
                            'Неполная штука',
                            formatPiecesShort(incompletePiecesOther) + ' шт = '
                                + formatMeters(incompleteMetersOther) + ' м × ' + formatMoney(incompleteUnitOther)
                                + (pipeIncompletePercent ? (' (+' + pipeIncompletePercent + '%)') : ''),
                            incompleteSumOther,
                            incompletePlanned ? 'по схеме резки' : '',
                            pipeIncompletePercent > 0
                        );
                    }
                    if (cutFeeTotal > 0) {
                        htmlOther += costRow(
                            'Оплата резов', '', cutFeeTotal,
                            totalCutsCount > 0 ? (totalCutsCount + ' рез.') : '', false
                        );
                    }
                    finishBreakdown(htmlOther, metalAtBaseOther, surchargeOther, blendedOther, grandOther);
                    return;
                }

                var maxWhole = availableInfo.full;
                if (complexPieces + simplePieces > maxWhole) {
                    var overflow = complexPieces + simplePieces - maxWhole;
                    if (simplePieces >= overflow) {
                        simplePieces -= overflow;
                    } else {
                        overflow -= simplePieces;
                        simplePieces = 0;
                        complexPieces = Math.max(0, complexPieces - overflow);
                    }
                }

                var uncutPieces = Math.max(0, maxWhole - complexPieces - simplePieces);
                var incompletePieces = availableInfo.fraction || 0;

                var uncutMeters = uncutPieces * length;
                var simpleMeters = simplePieces * length;
                var complexMeters = complexPieces * length;
                var incompleteMeters = incompletePieces * length;

                var complexUnitPrice = halfPiecesFlag ? baseUnit : surchargeUnit;
                var incompleteUnitPrice = halfPiecesFlag ? baseUnit : surchargeUnit;
                var uncutSum = roundMoney(uncutMeters * baseUnit);
                var simpleSum = roundMoney(simpleMeters * baseUnit);
                var complexSum = roundMoney(complexMeters * complexUnitPrice);
                var incompleteSum = roundMoney(incompleteMeters * incompleteUnitPrice);
                var metalBaseSum = roundMoney(
                    (uncutMeters + simpleMeters + (halfPiecesFlag ? complexMeters : 0) + (halfPiecesFlag ? incompleteMeters : 0)) * baseUnit
                    + (!halfPiecesFlag ? (complexMeters + incompleteMeters) * baseUnit : 0)
                );
                // Металл по базовой цене на все метры; наценки отдельно
                var allMeters = uncutMeters + simpleMeters + complexMeters + incompleteMeters;
                var metalAtBase = roundMoney(allMeters * baseUnit);
                var surchargeOnly = roundMoney(
                    (halfPiecesFlag ? 0 : (complexMeters + incompleteMeters) * (surchargeUnit - baseUnit))
                );
                var metalSum = roundMoney(metalAtBase + surchargeOnly);
                var blended = allMeters > 0 ? roundMoney(metalSum / allMeters) : baseUnit;
                var grandTotal = roundMoney(metalSum + cutFeeTotal);

                var html = '';

                if (uncutPieces > 0.0001) {
                    html += costRow(
                        'Целые без резки',
                        formatPiecesShort(uncutPieces) + ' шт × ' + formatMeters(length) + ' м = '
                            + formatMeters(uncutMeters) + ' м × ' + formatMoney(baseUnit),
                        uncutSum,
                        '',
                        false
                    );
                }
                if (simplePieces > 0.0001) {
                    html += costRow(
                        'Резанные (1 рез)',
                        formatPiecesShort(simplePieces) + ' шт × ' + formatMeters(length) + ' м = '
                            + formatMeters(simpleMeters) + ' м × ' + formatMoney(baseUnit),
                        simpleSum,
                        simpleCuts > 0 ? ('резов: ' + simpleCuts) : '',
                        false
                    );
                }
                if (complexPieces > 0.0001) {
                    if (halfPiecesFlag) {
                        html += costRow(
                            'Резанные',
                            formatPiecesShort(complexPieces) + ' шт × ' + formatMeters(length) + ' м = '
                                + formatMeters(complexMeters) + ' м × ' + formatMoney(baseUnit),
                            complexSum,
                            complexCuts > 0 ? ('резов: ' + complexCuts + ' · без наценки') : 'без наценки',
                            false
                        );
                    } else {
                        html += costRow(
                            'Резанные (сложная)',
                            formatPiecesShort(complexPieces) + ' шт × ' + formatMeters(length) + ' м = '
                                + formatMeters(complexMeters) + ' м × ' + formatMoney(surchargeUnit) + ' (+' + percent + '%)',
                            complexSum,
                            complexCuts > 0 ? ('резов: ' + complexCuts) : '',
                            true
                        );
                    }
                }
                if (incompletePieces > 0.0001) {
                    if (halfPiecesFlag) {
                        html += costRow(
                            'Неполная штука',
                            formatPiecesShort(incompletePieces) + ' шт = '
                                + formatMeters(incompleteMeters) + ' м × ' + formatMoney(baseUnit),
                            incompleteSum,
                            'без наценки',
                            false
                        );
                    } else {
                        html += costRow(
                            'Неполная штука',
                            formatPiecesShort(incompletePieces) + ' шт = '
                                + formatMeters(incompleteMeters) + ' м × ' + formatMoney(surchargeUnit) + ' (+' + percent + '%)',
                            incompleteSum,
                            incompletePlanned ? 'по схеме резки' : '',
                            true
                        );
                    }
                }

                if (cutFeeTotal > 0) {
                    var cutsNote = '';
                    if (planCuts > 0 && incompleteCuts > 0) {
                        cutsNote = totalCutsCount + ' рез.: ' + planCuts + ' по схеме + ' + incompleteCuts + ' авто за неполную';
                    } else if (totalCutsCount > 0) {
                        cutsNote = totalCutsCount + ' рез.';
                    }
                    html += costRow('Оплата резов', '', cutFeeTotal, cutsNote, false);
                }

                finishBreakdown(html, metalAtBase, surchargeOnly, blended, grandTotal);
            }

            function refreshCuttingPlan(id, isRetry) {
                var $item = $('#basket-item-' + id);
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$plan.length) {
                    return { ok: true };
                }

                var basicSheet = String($item.attr('data-basic-sheet') || '0') === '1';
                var halfPiecesFlag = String($item.attr('data-half-pieces') || '0') === '1';
                var availableInfo = getAvailablePieces($item);
                var availableFull = availableInfo.full;
                var stock = getStockLength($item);
                var remnantMeters = getRemnantMeters($item, availableInfo);
                var used = 0;
                var incompletePartCount = 0;
                var totalCost = 0;
                var totalCuts = 0;
                var totalComplexCuts = 0;
                var complexPiecesCount = 0;
                var simplePiecesCount = 0;
                var simpleCutsCount = 0;
                var hasComplexCutSummary = false;
                var errors = [];
                var hasInvalidPart = false;
                var needRecalc = false;
                var autoHalfCut = getAutoHalfCutInfo($item, $plan);
                var incompletePlanned = !!autoHalfCut.planned;

                // Карточки «Что режем»
                $plan.find('[data-entity="cutting-target-full-meta"]').text(
                    availableFull + ' шт × ' + (stock > 0 ? formatMeters(stock) + ' м' : '—')
                );
                var $incCard = $plan.find('[data-entity="cutting-target-incomplete"]');
                var $incAdd = $plan.find('[data-entity="cutting-part-add-incomplete"]');
                var hasIncompletePart = $plan.find('[data-entity="cutting-part"][data-target="incomplete"]').length > 0;
                if (remnantMeters > 0.0001) {
                    // шаг 1: карточка неполной всегда видна, если остаток есть
                    $incCard.prop('hidden', false);
                    $plan.find('[data-entity="cutting-target-incomplete-meta"]').text(
                        formatMeters(availableInfo.fraction) + ' шт = ' + formatMeters(remnantMeters) + ' м'
                    );
                    // «+ Неполная» — только если её ещё нет в плане
                    $incAdd.prop('hidden', hasIncompletePart);
                } else {
                    $incCard.prop('hidden', true).removeClass('is-active');
                    $incAdd.prop('hidden', true);
                }

                if (availableInfo.fraction > 0.0001) {
                    var totalText = availableFull + ' шт + неполная ' + formatMeters(availableInfo.fraction) + ' шт';
                    if (incompletePlanned) {
                        totalText += ' → режется по плану';
                    } else if (autoHalfCut.cuts > 0) {
                        totalText += ' → 1 рез';
                    }
                    $plan.find('[data-entity="cutting-summary-total"]').text(totalText);
                } else {
                    $plan.find('[data-entity="cutting-summary-total"]').text(availableFull + ' шт');
                }
                $plan.find('[data-entity="cutting-summary-stock"]').text(stock > 0 ? (formatMeters(stock) + ' м') : '—');
                $item.attr('data-half-piece-cut', (autoHalfCut.cuts > 0 || incompletePlanned) ? '1' : '0');

                $plan.find('[data-entity="cutting-part"]').each(function(index) {
                    var $part = $(this);
                    var target = getPartTarget($part);
                    applyPartTargetUi($part, target);
                    var $qty = $part.find('[data-entity="cutting-part-qty"]');
                    var $type = $part.find('[data-entity="cutting-part-type"]');
                    var $cuts = $part.find('[data-entity="cutting-part-cuts"]');
                    var $preview = $part.find('[data-entity="cutting-part-preview"]');
                    var isIncomplete = target === 'incomplete';
                    var qty = isIncomplete ? 1 : (parseInt($qty.val(), 10) || 0);
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
                    var partStock = isIncomplete ? remnantMeters : stock;

                    if (!isIncomplete && qty < 0) {
                        qty = 0;
                        $qty.val('0');
                    }
                    if (isIncomplete) {
                        $qty.val('1');
                        incompletePartCount += 1;
                        $part.find('[data-entity="cutting-part-qty-fixed-value"]').text(
                            formatMeters(availableInfo.fraction) + ' шт = ' + formatMeters(remnantMeters) + ' м'
                        );
                    } else if (qty > 0 && lengths.length) {
                        // в лимит целых идут только партии с кусками;
                        // пустой черновик («+ Ещё целые») не должен ужимать соседние строки
                        used += qty;
                    }

                    if (qty > 0 && !typeCode && String($cuts.val() || '').trim() !== '') {
                        typeError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': выберите тип резки');
                    }

                    if (isIncomplete && remnantMeters <= 0.0001 && lengths.length) {
                        lengthError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': неполной штуки нет в корзине');
                    }

                    if (lengths.length && partStock > 0 && Math.abs(sum - partStock) > 0.0001) {
                        if (sum - partStock > 0.0001) {
                            lengthError = true;
                            hasInvalidPart = true;
                            errors.push('Партия ' + (index + 1) + ': сумма кусков ' + formatMeters(sum) + ' м больше '
                                + (isIncomplete ? 'остатка ' : 'длины ') + formatMeters(partStock) + ' м');
                        } else if (isIncomplete) {
                            lengthError = true;
                            hasInvalidPart = true;
                            errors.push('Партия ' + (index + 1) + ': сумма кусков должна быть ровно '
                                + formatMeters(partStock) + ' м (неполная)');
                        }
                    } else if (lengths.length && partStock > 0 && !isIncomplete && sum - partStock > 0.0001) {
                        lengthError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': сумма кусков ' + formatMeters(sum) + ' м больше длины ' + formatMeters(partStock) + ' м');
                    }

                    if (cutsState.hasFraction) {
                        fractionError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': длины кусков — только целые метры');
                    }

                    if ((isIncomplete || qty > 0) && !lengths.length && String($cuts.val() || '').trim() !== '') {
                        pieceError = true;
                        hasInvalidPart = true;
                        errors.push('Партия ' + (index + 1) + ': укажите длины кусков целыми числами');
                    }

                    $part.toggleClass('is-invalid', lengthError || pieceError || typeError || fractionError);
                    $part.toggleClass('is-incomplete-target', isIncomplete);
                    $qty.toggleClass('is-invalid', false);
                    $type.toggleClass('is-invalid', typeError);
                    $cuts.toggleClass('is-invalid', lengthError || pieceError || fractionError);

                    if (!lengths.length) {
                        if (!isIncomplete && qty <= 0) {
                            $preview.html('<span class="cutting-part__preview-empty">Укажите число штук и длины кусков</span>');
                        } else {
                            $preview.html('<span class="cutting-part__preview-empty">Укажите длины кусков — появится расчёт</span>');
                        }
                        return;
                    }

                    var cutsPerPiece = getCutCount(lengths);
                    var isComplexCut = !isIncomplete && basicSheet && !halfPiecesFlag && cutsPerPiece >= 2;
                    var effectiveQty = isIncomplete ? 1 : Math.max(qty, 0);
                    var cutsTotal = cutsPerPiece * effectiveQty;
                    var partCost = cutsTotal * pricePerCut;
                    if (!isIncomplete && effectiveQty > 0) {
                        if (cutsPerPiece >= 2) {
                            complexPiecesCount += effectiveQty;
                            totalComplexCuts += cutsPerPiece * effectiveQty;
                            if (isComplexCut) {
                                hasComplexCutSummary = true;
                            }
                        } else if (cutsPerPiece === 1) {
                            simplePiecesCount += effectiveQty;
                            simpleCutsCount += effectiveQty;
                        }
                    }
                    totalCuts += cutsTotal;
                    totalCost += partCost;

                    var remainder = partStock > 0 ? Math.max(0, partStock - sum) : null;
                    var chips = lengths.map(function(len) {
                        return '<span class="cutting-chip">' + formatCutLength(len) + ' м</span>';
                    }).join('<span class="cutting-chip-sep">+</span>');

                    var meta = '<span class="cutting-chip cutting-chip--type">' + typeName + '</span> · '
                        + (isIncomplete ? 'с неполной: ' : 'с одной штуки: ') + chips
                        + ' = <strong>' + formatCutLength(sum) + ' м</strong>';
                    if (remainder !== null) {
                        meta += ', остаток: <strong>' + formatCutLength(remainder) + ' м</strong>';
                    }
                    if (!isIncomplete && qty <= 0) {
                        meta += '<br><em>0 шт — партия не учитывается, укажите число штук</em>';
                    } else {
                        meta += '<br>Резов: <strong>' + cutsPerPiece + '</strong>';
                        if (pricePerCut > 0) {
                            meta += ' × ' + formatMoney(pricePerCut);
                            if (!isIncomplete && effectiveQty > 1) {
                                meta += ' × ' + effectiveQty + ' шт';
                            }
                            meta += ' = <strong class="cutting-part__cost">' + formatMoney(partCost) + '</strong>';
                        } else {
                            meta += ' (цена типа резки не задана)';
                        }
                        if (isIncomplete) {
                            if (basicSheet && !halfPiecesFlag) {
                                meta += ' · <strong class="cutting-part__cost">+10% на неполную</strong>';
                            } else if (basicSheet && halfPiecesFlag) {
                                meta += ' · <em>без наценки на неполную</em>';
                            }
                        } else if (isComplexCut) {
                            meta += ' · <strong class="cutting-part__cost">+ дополнительно +10% на резанные</strong>';
                        } else if (basicSheet && halfPiecesFlag && cutsPerPiece >= 2) {
                            meta += ' · <em>без наценки (+10% не применяется)</em>';
                        }
                        if (!isIncomplete && effectiveQty > 1) {
                            meta += '<br>Всего по партии: <strong>' + formatCutLength(sum * effectiveQty) + ' м</strong> ('
                                + effectiveQty + ' × ' + formatCutLength(sum) + ' м)';
                        }
                    }

                    $preview.html(meta);
                });

                if (incompletePartCount > 1) {
                    hasInvalidPart = true;
                    errors.unshift('Неполную штуку можно указать только одной партией.');
                }

                if (used > availableFull) {
                    hasInvalidPart = true;
                    errors.unshift(
                        'К резке выбрано ' + used + ' шт, а в корзине только ' + availableFull
                        + ' целых шт. Уменьшите партии резки или увеличьте количество в корзине.'
                    );
                    // не урезаем партии автоматически — иначе пропадает последняя строка
                    // даже когда ещё есть «Без резки» (гонка при − qty / redraw)
                }

                // убрать только полностью пустые черновики (0 шт и нет кусков)
                if (!isRetry) {
                    var $fullParts = $plan.find('[data-entity="cutting-part"][data-target="full"]');
                    $fullParts.each(function() {
                        var $part = $(this);
                        var qty = parseInt($part.find('[data-entity="cutting-part-qty"]').val(), 10) || 0;
                        var cutsRaw = String($part.find('[data-entity="cutting-part-cuts"]').val() || '').trim();
                        if (qty > 0 || cutsRaw !== '') {
                            return;
                        }
                        if ($plan.find('[data-entity="cutting-part"]').length <= 1) {
                            return;
                        }
                        $part.remove();
                    });
                }

                if (needRecalc && !isRetry) {
                    return refreshCuttingPlan(id, true);
                }

                var rest = Math.max(0, availableFull - used);
                var usedLabel = used + ' шт';
                if (incompletePlanned || incompletePartCount > 0) {
                    usedLabel = used > 0 ? (used + ' шт + неполная') : 'неполная';
                }
                $plan.find('[data-entity="cutting-summary-used"]').text(usedLabel);
                $plan.find('[data-entity="cutting-summary-rest"]').text(rest + ' шт');

                // Схема запаса
                var $bar = $plan.find('[data-entity="cutting-stock-bar"]');
                var $legend = $plan.find('[data-entity="cutting-stock-legend"]');
                if ($bar.length) {
                    var restFlex = rest;
                    var cutFlex = used;
                    var incFlex = availableInfo.fraction > 0.0001 ? Math.max(availableInfo.fraction, 0.15) : 0;
                    $bar.find('[data-entity="cutting-stock-rest"]').css('flex', String(Math.max(restFlex, 0.001)));
                    $bar.find('[data-entity="cutting-stock-cut"]').css('flex', String(Math.max(cutFlex, 0)));
                    $bar.find('[data-entity="cutting-stock-incomplete"]').css('flex', String(incFlex));
                    $bar.prop('hidden', false);
                    $legend.html(
                        '<span class="cutting-stock-bar__leg cutting-stock-bar__leg--rest">Без резки: ' + rest + ' шт</span>'
                        + '<span class="cutting-stock-bar__leg cutting-stock-bar__leg--cut">К резке: ' + used + ' шт</span>'
                        + (availableInfo.fraction > 0.0001
                            ? ('<span class="cutting-stock-bar__leg cutting-stock-bar__leg--incomplete">Неполная: '
                                + formatMeters(availableInfo.fraction) + ' шт (' + formatMeters(remnantMeters) + ' м)</span>')
                            : '')
                    ).prop('hidden', false);
                }

                totalCuts += autoHalfCut.cuts;
                totalCost += autoHalfCut.cost;

                var $cost = $plan.find('[data-entity="cutting-summary-cost"]');
                if (totalCuts > 0) {
                    var costLabel = formatMoney(totalCost) + ' (' + totalCuts + ' рез.)';
                    if (hasComplexCutSummary) {
                        costLabel += ' · +10% на резанные';
                    }
                    if ((incompletePlanned || incompletePartCount > 0) && basicSheet && !halfPiecesFlag) {
                        costLabel += ' · +10% на неполную';
                    }
                    if (autoHalfCut.cuts > 0 && autoHalfCut.cost > 0) {
                        costLabel += ' · в т.ч. 1 рез за неполную ' + formatMeters(autoHalfCut.fraction) + ' шт';
                    }
                    $cost.text(costLabel);
                } else {
                    $cost.text('0 ₽');
                }
                $plan.find('[data-entity="cutting-summary-cost-copy"]').text($cost.text());

                updateCuttingCostBreakdown($item, $plan, {
                    availableInfo: availableInfo,
                    used: used,
                    complexPieces: complexPiecesCount,
                    simplePieces: simplePiecesCount,
                    simpleCuts: simpleCutsCount,
                    complexCuts: totalComplexCuts,
                    cutFeeTotal: totalCost,
                    totalCuts: totalCuts,
                    incompleteCuts: autoHalfCut.cuts || 0,
                    incompletePlanned: incompletePlanned,
                    halfPiecesFlag: halfPiecesFlag
                });

                updateBasketTotalWithCutting();

                var $error = $plan.find('[data-entity="cutting-plan-error"]');
                if (errors.length) {
                    $error.html(errors.join('<br>')).prop('hidden', false);
                } else {
                    $error.empty().prop('hidden', true);
                }

                // снимок DOM (с черновиками) — переживает ajax-перерисовку строки
                if ($plan.hasClass('is-open') || ($plan.closest('[data-entity="cutting-plan-row"]').hasClass('is-open'))) {
                    cuttingPartsStateCache[String(id)] = serializeCuttingPartsState($plan);
                }

                return { ok: !hasInvalidPart || used <= availableFull, errors: errors };
            }

            function attachCuttingRows() {
                $('[data-entity="cutting-plan-row"]').each(function() {
                    var id = $(this).data('id');
                    var $item = $('#basket-item-' + id);
                    if (!$item.length) {
                        // товар удалён — убрать «осиротевшую» резку из DOM
                        clearCuttingUiForItem(id);
                        $(this).remove();
                        return;
                    }
                    if ($item.next()[0] !== this) {
                        $item.after(this);
                    }
                });
            }

            function clearCuttingUiForItem(id) {
                var key = String(id);
                delete cuttingPartsStateCache[key];
                delete cuttingWizardSteps[key];
                if (cuttingSaveTimers[key]) {
                    clearTimeout(cuttingSaveTimers[key]);
                    delete cuttingSaveTimers[key];
                }
                if (cuttingSaveXhrs[key] && cuttingSaveXhrs[key].abort) {
                    try { cuttingSaveXhrs[key].abort(); } catch (e) {}
                    delete cuttingSaveXhrs[key];
                }
                try {
                    var openMap = getCuttingUiOpenMap();
                    if (Object.prototype.hasOwnProperty.call(openMap, key)) {
                        delete openMap[key];
                        window.localStorage.setItem(CUTTING_UI_OPEN_KEY, JSON.stringify(openMap));
                    }
                } catch (e) {}
                try {
                    var map = getCuttingWizardStepMap();
                    if (Object.prototype.hasOwnProperty.call(map, key)) {
                        delete map[key];
                        window.localStorage.setItem(CUTTING_WIZARD_STEP_KEY, JSON.stringify(map));
                    }
                } catch (e) {}
            }

            function removeCuttingRowForItem(id) {
                var cut = document.getElementById('basket-item-' + id + '-cutting');
                if (cut && cut.parentNode) {
                    cut.parentNode.removeChild(cut);
                }
                clearCuttingUiForItem(id);
            }

            var cuttingSaveTimers = {};
            var cuttingSaveXhrs = {};
            var cuttingStatusTimers = {};
            var cuttingHintTimers = {};
            var pendingPriceRefresh = {};
            var cuttingWizardSteps = {};
            var CUTTING_UI_OPEN_KEY = 'metplus_cutting_panel_open';
            var CUTTING_WIZARD_STEP_KEY = 'metplus_cutting_wizard_step';

            function getCuttingUiOpenMap() {
                try {
                    var raw = window.localStorage.getItem(CUTTING_UI_OPEN_KEY);
                    var map = raw ? JSON.parse(raw) : {};
                    return map && typeof map === 'object' ? map : {};
                } catch (e) {
                    return {};
                }
            }

            function setCuttingUiOpen(id, open) {
                try {
                    var map = getCuttingUiOpenMap();
                    map[String(id)] = !!open;
                    window.localStorage.setItem(CUTTING_UI_OPEN_KEY, JSON.stringify(map));
                } catch (e) {}
            }

            function isCuttingUiOpen(id) {
                var map = getCuttingUiOpenMap();
                var key = String(id);
                // только явный выбор пользователя; шаг мастера сам по себе панель не открывает
                if (Object.prototype.hasOwnProperty.call(map, key)) {
                    return !!map[key];
                }
                return false;
            }

            var CUTTING_UI_SCROLL_KEY = 'metplus_cutting_page_scroll';
            var CUTTING_UI_ANCHOR_KEY = 'metplus_cutting_scroll_anchor';

            function saveCuttingPageScroll() {
                try {
                    var y = window.pageYOffset || document.documentElement.scrollTop || 0;
                    window.sessionStorage.setItem(CUTTING_UI_SCROLL_KEY, String(Math.round(y)));
                    var $open = $('#basket-root [data-entity="cutting-plan-row"].is-open').first();
                    if ($open.length) {
                        window.sessionStorage.setItem(CUTTING_UI_ANCHOR_KEY, String($open.data('id') || ''));
                    }
                } catch (e) {}
            }

            function restoreCuttingPageScroll() {
                try {
                    if ('scrollRestoration' in history) {
                        history.scrollRestoration = 'manual';
                    }
                    var anchorId = window.sessionStorage.getItem(CUTTING_UI_ANCHOR_KEY) || '';
                    var y = parseInt(window.sessionStorage.getItem(CUTTING_UI_SCROLL_KEY) || '0', 10) || 0;
                    var apply = function() {
                        if (anchorId) {
                            var el = document.getElementById('basket-item-' + anchorId)
                                || document.getElementById('basket-item-' + anchorId + '-cutting');
                            if (el && typeof el.scrollIntoView === 'function') {
                                el.scrollIntoView({ block: 'start', behavior: 'auto' });
                                return;
                            }
                        }
                        if (y > 0) {
                            window.scrollTo(0, y);
                        }
                    };
                    apply();
                    requestAnimationFrame(function() {
                        apply();
                        setTimeout(apply, 50);
                    });
                } catch (e) {}
            }

            function restoreAllCuttingUi(options) {
                options = options || {};
                attachCuttingRows();
                var opened = 0;
                $('#basket-root [data-entity="cutting-plan-row"]').each(function() {
                    var id = $(this).data('id');
                    var $row = $(this);
                    var $plan = $row.find('[data-entity="cutting-plan"]');
                    var enabled = $plan.attr('data-enabled') === 'Y';
                    var hasPlan = String($plan.find('[data-entity="cutting-parts"]').attr('data-plan') || '').trim() !== '';
                    if (!isCuttingUiOpen(id)) {
                        syncCuttingToggleLabel(id);
                        return;
                    }
                    if (!enabled && !hasPlan && !getCuttingWizardStepStored(id)) {
                        syncCuttingToggleLabel(id);
                        return;
                    }
                    var alreadyOpen = $row.hasClass('is-open') && !$row.prop('hidden');
                    var step = getCuttingWizardStepStored(id);
                    if (!step) {
                        step = hasPlan ? 3 : 1;
                    }
                    openCuttingPlan(id, true, { skipSave: true, step: step });
                    if (!alreadyOpen || options.forceStep) {
                        restoreCuttingWizardStep(id, step);
                    }
                    opened += 1;
                });
                updateBasketTotalWithCutting();
                if (options.restoreScroll !== false) {
                    restoreCuttingPageScroll();
                }
                return opened;
            }

            function getCuttingWizardStepMap() {
                try {
                    var raw = window.localStorage.getItem(CUTTING_WIZARD_STEP_KEY);
                    var map = raw ? JSON.parse(raw) : {};
                    return map && typeof map === 'object' ? map : {};
                } catch (e) {
                    return {};
                }
            }

            function getCuttingWizardStepStored(id) {
                var key = String(id);
                if (Object.prototype.hasOwnProperty.call(cuttingWizardSteps, key)) {
                    return cuttingWizardSteps[key];
                }
                if (Object.prototype.hasOwnProperty.call(cuttingWizardSteps, id)) {
                    return cuttingWizardSteps[id];
                }
                var map = getCuttingWizardStepMap();
                var step = parseInt(map[key], 10) || 0;
                return (step >= 1 && step <= 3) ? step : 0;
            }

            function setCuttingWizardStepStored(id, step) {
                step = parseInt(step, 10) || 1;
                if (step < 1) step = 1;
                if (step > 3) step = 3;
                var key = String(id);
                cuttingWizardSteps[key] = step;
                try {
                    var map = getCuttingWizardStepMap();
                    map[key] = step;
                    window.localStorage.setItem(CUTTING_WIZARD_STEP_KEY, JSON.stringify(map));
                } catch (e) {}
            }

            function flashCuttingHint(id, text) {
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                var $hint = $plan.find('[data-entity="cutting-plan-hint"]');
                if (!$hint.length) {
                    $hint = $('<div class="cutting-plan__hint" data-entity="cutting-plan-hint" hidden></div>');
                    var $error = $plan.find('[data-entity="cutting-plan-error"]').first();
                    if ($error.length) {
                        $error.before($hint);
                    } else {
                        $plan.find('[data-entity="cutting-parts"]').after($hint);
                    }
                }
                clearTimeout(cuttingHintTimers[id]);
                $hint.text(text).prop('hidden', false);
                cuttingHintTimers[id] = setTimeout(function() {
                    $hint.prop('hidden', true).text('');
                }, 4000);
            }

            function getCuttingWizardStep(id) {
                var stored = getCuttingWizardStepStored(id);
                if (stored) {
                    return stored;
                }
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                var fromDom = $plan.length ? (parseInt($plan.attr('data-wizard-step'), 10) || 0) : 0;
                if (fromDom >= 1 && fromDom <= 3) {
                    return fromDom;
                }
                return 1;
            }

            function restoreCuttingWizardStep(id, step) {
                step = parseInt(step, 10) || getCuttingWizardStepStored(id) || 1;
                if (step < 1) step = 1;
                if (step > 3) step = 3;
                setCuttingWizardStepStored(id, step);
                var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                if (!$plan.length) {
                    return;
                }
                if (typeof window.metplusSetCuttingWizardStep === 'function') {
                    window.metplusSetCuttingWizardStep($plan, step);
                } else {
                    $plan.attr('data-wizard-step', String(step));
                }
            }

            function requestBasketPriceRefresh(id, force) {
                if (!force && $('[data-entity="cutting-part-cuts"]:focus').length) {
                    pendingPriceRefresh[id] = true;
                    return;
                }

                // Через actionPool — иначе параллельный recalculateAjax затирает только что изменённое кол-во
                if (BX.Sale && BX.Sale.BasketComponent && BX.Sale.BasketComponent.actionPool) {
                    var pool = BX.Sale.BasketComponent.actionPool;
                    pool.needFullRecalculation = true;
                    pool.setRefreshStatus(false);
                    pool.switchTimer();
                    return;
                }

                if (BX.Sale && BX.Sale.BasketComponent) {
                    BX.Sale.BasketComponent.sendRequest('refreshAjax', {
                        fullRecalculation: 'Y'
                    });
                }
            }

            function flushPendingPriceRefresh(id) {
                if (!pendingPriceRefresh[id]) {
                    return;
                }

                delete pendingPriceRefresh[id];
                requestBasketPriceRefresh(id, true);
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
                    return msg.indexOf('больше длины') !== -1
                        || msg.indexOf('больше остатка') !== -1
                        || msg.indexOf('должна быть ровно') !== -1
                        || msg.indexOf('укажите длины') !== -1
                        || msg.indexOf('только целые') !== -1
                        || msg.indexOf('выберите тип резки') !== -1
                        || msg.indexOf('только одной партией') !== -1
                        || msg.indexOf('неполной штуки нет') !== -1;
                });
            }

            function basicSheetQuantityNeedsPlus10Price($item) {
                if (!$item.length || String($item.attr('data-basic-sheet') || '0') !== '1') {
                    return false;
                }

                // Флаг «Только шт и 0,5 шт» — без +10% за неполную штуку
                if (String($item.attr('data-half-pieces') || '0') === '1') {
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
                // Разбивка цены в строке убрана — детали на шаге «Итого» резки.
            }

            function syncCuttingToggleLabel(id) {
                var $item = $('#basket-item-' + id);
                var $btn = $item.find('[data-entity="cutting-plan-toggle"]');
                var $row = $('#basket-item-' + id + '-cutting');
                var $plan = $row.find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$btn.length) {
                    return;
                }
                var enabled = $plan.length
                    ? ($plan.attr('data-enabled') === 'Y')
                    : $btn.hasClass('is-active');
                var isOpen = $row.length && !$row.prop('hidden') && $row.hasClass('is-open');
                $btn.toggleClass('is-active', enabled);
                $btn.text(enabled ? 'Изменить резку' : 'Хочу порезку');
                $btn.attr('aria-expanded', isOpen ? 'true' : 'false');
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
                    var planBuilt = buildPlanText($plan);
                    var enabled;
                    if (Object.prototype.hasOwnProperty.call(options, 'enabled')) {
                        enabled = options.enabled;
                    } else if (planBuilt) {
                        // есть куски — включаем резку автоматически
                        enabled = 'Y';
                    } else {
                        enabled = $plan.attr('data-enabled') === 'Y' ? 'Y' : 'N';
                    }

                    // пока план явно кривой — не пишем в корзину, чтобы не сохранить мусор
                    if (enabled === 'Y' && hasHardCuttingErrors(state.errors)) {
                        setCuttingStatus(id, 'Исправьте ошибки', 'error');
                        return;
                    }

                    var planText = enabled === 'Y' ? planBuilt : '';
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
                            syncCuttingToggleLabel(id);
                            var $btn = $('#basket-item-' + id).find('[data-entity="cutting-plan-toggle"]');
                            if (enabled !== 'Y') {
                                $btn.attr('aria-expanded', 'false');
                            }
                            setCuttingStatus(id, 'Сохранено', 'saved');
                            if (String($('#basket-item-' + id).attr('data-basic-sheet') || '0') === '1') {
                                updateBasketItemPriceNote(id, !!(data && data.cuttingSurcharge10));
                            }
                            // после смены плана всегда пересчитываем сумму строки
                            if (data.needPriceRefresh || String($('#basket-item-' + id).attr('data-basic-sheet') || '0') === '1') {
                                requestBasketPriceRefresh(id, true);
                            }
                            updateBasketTotalWithCutting();
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
                var forceFill = !!options.forceFill;
                var skipFill = !!options.skipFill;
                var $item = $('#basket-item-' + id);
                var $btn = $item.find('[data-entity="cutting-plan-toggle"]');
                var $row = $('#basket-item-' + id + '-cutting');
                var $plan = $row.find('[data-entity="cutting-plan"][data-id="' + id + '"]');
                if (!$row.length || !$plan.length) {
                    return;
                }

                attachCuttingRows();

                var $parts = $plan.find('[data-entity="cutting-parts"]');
                var savedPlan = String($parts.attr('data-plan') || '').trim();

                if (open) {
                    var wasOpen = $row.hasClass('is-open') && !$row.prop('hidden');
                    // шаг до показа панелей — без мерцания «1. Что режем»
                    var preferStep = parseInt(options.step, 10)
                        || getCuttingWizardStepStored(id)
                        || parseInt($plan.attr('data-wizard-step'), 10)
                        || 0;
                    if (preferStep >= 1 && preferStep <= 3 && typeof window.metplusSetCuttingWizardStep === 'function') {
                        window.metplusSetCuttingWizardStep($plan, preferStep);
                    }

                    $row.prop('hidden', false).addClass('is-open');
                    $plan.addClass('is-open');
                    if (savedPlan || $plan.attr('data-enabled') === 'Y') {
                        $plan.attr('data-enabled', 'Y');
                    }
                    $btn.attr('aria-expanded', 'true');
                    setCuttingUiOpen(id, true);
                    saveCuttingPageScroll();
                    syncCuttingToggleLabel(id);

                    var partsCount = $parts.find('[data-entity="cutting-part"]').length;
                    var cached = cuttingPartsStateCache[String(id)];
                    if (!skipFill && cached && cached.length && (forceFill || !wasOpen)) {
                        applyCuttingPartsState($plan, cached);
                        delete cuttingPartsStateCache[String(id)];
                    } else if (!skipFill && (forceFill || !wasOpen || partsCount === 0)) {
                        fillPartsFromPlan($parts);
                    }
                    refreshCuttingPlan(id);
                    if (!skipSave && savedPlan) {
                        saveCuttingPlan(id, { immediate: true, enabled: 'Y' });
                    }
                } else {
                    $row.prop('hidden', true).removeClass('is-open');
                    $plan.removeClass('is-open');
                    $btn.attr('aria-expanded', 'false');
                    if (!options.skipUiPersist) {
                        setCuttingUiOpen(id, false);
                    }

                    var draftPlan = buildPlanText($plan);
                    if (!draftPlan && !savedPlan) {
                        $plan.attr('data-enabled', 'N');
                        if (!skipSave) {
                            saveCuttingPlan(id, { immediate: true, enabled: 'N', force: true });
                        }
                        updateBasketTotalWithCutting();
                    }
                    syncCuttingToggleLabel(id);
                }
            }

            // клик «Хочу порезку» — в main.js (после jQuery), здесь только остальное

            $(document).on("click" + cuttingNs, "#basket-root [data-entity='cutting-part-add']", function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var $parts = $('[data-entity="cutting-parts"][data-id="' + id + '"]');
                    var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                    var $tpl = $parts.find('[data-entity="cutting-part"]').first().clone();
                    $tpl.removeClass('is-invalid is-incomplete-target');
                    $tpl.find('input').val('').removeClass('is-invalid');
                    $tpl.find('[data-entity="cutting-part-qty"]').val('1');
                    $tpl.find('[data-entity="cutting-part-preview"]').empty();
                    applyPartTargetUi($tpl, 'full');
                    $parts.append($tpl);
                    $('[data-entity="cutting-target-full"][data-id="' + id + '"]').addClass('is-active');
                    $('[data-entity="cutting-target-incomplete"][data-id="' + id + '"]').removeClass('is-active');
                    // кэш на случай ajax-перерисовки: черновик не попадает в data-plan
                    cuttingPartsStateCache[String(id)] = serializeCuttingPartsState($plan);
                    refreshCuttingPlan(id);
                    // не saveCuttingPlan — план не меняется без кусков, save/recalc затирает строку
            });
            $(document).on("click" + cuttingNs, "#basket-root [data-entity='cutting-part-add-incomplete']", function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    var $parts = $('[data-entity="cutting-parts"][data-id="' + id + '"]');
                    var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                    if ($parts.find('[data-entity="cutting-part"][data-target="incomplete"]').length) {
                        flashCuttingHint(id, 'Неполная штука уже в плане резки — она одна, добавить ещё нельзя.');
                        refreshCuttingPlan(id);
                        return;
                    }
                    var $tpl = $parts.find('[data-entity="cutting-part"]').first().clone();
                    $tpl.removeClass('is-invalid');
                    $tpl.find('input').val('').removeClass('is-invalid');
                    $tpl.find('[data-entity="cutting-part-preview"]').empty();
                    applyPartTargetUi($tpl, 'incomplete');
                    $parts.append($tpl);
                    $('[data-entity="cutting-target-incomplete"][data-id="' + id + '"]').addClass('is-active');
                    $('[data-entity="cutting-target-full"][data-id="' + id + '"]').removeClass('is-active');
                    cuttingPartsStateCache[String(id)] = serializeCuttingPartsState($plan);
                    refreshCuttingPlan(id);
            });
            $(document).on("click" + cuttingNs, "#basket-root [data-entity='cutting-target-full']", function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    $(this).addClass('is-active');
                    $('[data-entity="cutting-target-incomplete"][data-id="' + id + '"]').removeClass('is-active');
                    syncPartsToSelectedTarget(id);
                    refreshCuttingPlan(id);
            });
            $(document).on("click" + cuttingNs, "#basket-root [data-entity='cutting-target-incomplete']", function(e) {
                    e.preventDefault();
                    var id = $(this).data('id');
                    if ($(this).prop('hidden')) {
                        return;
                    }
                    $(this).addClass('is-active');
                    $('[data-entity="cutting-target-full"][data-id="' + id + '"]').removeClass('is-active');
                    syncPartsToSelectedTarget(id);
                    refreshCuttingPlan(id);
            });
            $(document).on("click" + cuttingNs, "#basket-root [data-entity='cutting-part-remove']", function(e) {
                    e.preventDefault();
                    var $part = $(this).closest('[data-entity="cutting-part"]');
                    var $parts = $part.closest('[data-entity="cutting-parts"]');
                    var id = $parts.data('id');
                    if ($parts.find('[data-entity="cutting-part"]').length > 1) {
                        $part.remove();
                    } else {
                        applyPartTargetUi($part, 'full');
                        $part.find('[data-entity="cutting-part-qty"]').val('1');
                        $part.find('[data-entity="cutting-part-cuts"]').val('');
                        $part.find('[data-entity="cutting-part-preview"]').empty();
                        $part.removeClass('is-invalid');
                    }
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id, { immediate: true });
            });
            $(document).on("input" + cuttingNs, "#basket-root [data-entity='cutting-part-qty'], #basket-root [data-entity='cutting-part-cuts'], #basket-root [data-entity='cutting-part-type']", function() {
                    if ($(this).is('[data-entity="cutting-part-cuts"]')) {
                        var $cuts = $(this);
                        var sanitized = sanitizeCutLengthsInput($cuts.val());
                        if ($cuts.val() !== sanitized) {
                            setInputValuePreserveCaret($cuts, sanitized);
                        }
                    }
                    var id = $(this).closest('[data-entity="cutting-plan"]').data('id');
                    refreshCuttingPlan(id);
            });
            $(document).on("blur" + cuttingNs, "#basket-root [data-entity='cutting-part-cuts']", function() {
                    var $cuts = $(this);
                    var normalized = normalizeCutLengthsText($cuts.val());
                    if ($cuts.val() !== normalized) {
                        $cuts.val(normalized);
                    }
                    var id = $(this).closest('[data-entity="cutting-plan"]').data('id');
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id, { immediate: true });
                    flushPendingPriceRefresh(id);
            });
            $(document).on("change" + cuttingNs, "#basket-root [data-entity='cutting-part-qty'], #basket-root [data-entity='cutting-part-type']", function() {
                    var id = $(this).closest('[data-entity="cutting-plan"]').data('id');
                    refreshCuttingPlan(id);
                    saveCuttingPlan(id);
            });
            $(document).on("click" + cuttingNs, "#basket-root [data-entity='cutting-cancel']", function(e) {
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
                var originalDelete = component.deleteBasketItem;

                component.deleteBasketItem = function(itemId, restore, final) {
                    try {
                        // до удаления строки товара — сразу убрать резку (иначе остаётся «сирота»)
                        if (!restore) {
                            removeCuttingRowForItem(itemId);
                        }
                        if (this.items && this.items[itemId]) {
                            originalDelete.apply(this, arguments);
                        } else {
                            var node = document.getElementById('basket-item-' + itemId);
                            if (node && node.parentNode) {
                                node.parentNode.removeChild(node);
                            }
                            removeCuttingRowForItem(itemId);
                        }
                        if (restore) {
                            // restore-режим: строка товара остаётся, резку скрываем
                            var $cut = $('#basket-item-' + itemId + '-cutting');
                            if ($cut.length) {
                                $cut.removeClass('is-open').prop('hidden', true);
                                $cut.find('[data-entity="cutting-plan"]').removeClass('is-open');
                            }
                            clearCuttingUiForItem(itemId);
                        }
                    } catch (e) {
                        if (window.console && console.error) {
                            console.error('deleteBasketItem', itemId, e);
                        }
                    }
                    attachCuttingRows();
                    updateBasketTotalWithCutting();
                };

                component.createBasketItem = function() {
                    originalCreate.apply(this, arguments);
                    attachCuttingRows();
                    updateBasketTotalWithCutting();
                    // после первичной отрисовки позиции — восстановить открытую резку
                    var newId = arguments[0];
                    if (newId && isCuttingUiOpen(newId)) {
                        var $plan = $('#basket-item-' + newId + '-cutting').find('[data-entity="cutting-plan"]');
                        var step = getCuttingWizardStepStored(newId) || 1;
                        var cached = cuttingPartsStateCache[String(newId)];
                        if ($plan.length && ($plan.attr('data-enabled') === 'Y'
                            || String($plan.find('[data-entity="cutting-parts"]').attr('data-plan') || '').trim()
                            || step
                            || (cached && cached.length))) {
                            openCuttingPlan(newId, true, {
                                skipSave: true,
                                skipFill: !!(cached && cached.length),
                                step: step
                            });
                            if (cached && cached.length) {
                                applyCuttingPartsState($plan, cached);
                                delete cuttingPartsStateCache[String(newId)];
                                refreshCuttingPlan(newId);
                            }
                            restoreCuttingWizardStep(newId, step);
                        }
                    }
                };

                component.redrawBasketItemNode = function(itemId) {
                    var cartBody = document.querySelector('.cart-content_body');
                    var savedScrollTop = cartBody ? cartBody.scrollTop : 0;
                    var savedStep = getCuttingWizardStepStored(itemId) || getCuttingWizardStep(itemId);
                    var livePlanText = '';
                    var livePartsState = null;
                    var oldCut = document.getElementById('basket-item-' + itemId + '-cutting');
                    if (oldCut) {
                        var $oldPlan = $(oldCut).find('[data-entity="cutting-plan"]');
                        var domStep = parseInt($oldPlan.attr('data-wizard-step'), 10) || 0;
                        if (domStep >= 1 && domStep <= 3) {
                            savedStep = domStep;
                            setCuttingWizardStepStored(itemId, domStep);
                        }
                        livePartsState = serializeCuttingPartsState($oldPlan);
                        cuttingPartsStateCache[String(itemId)] = livePartsState;
                        livePlanText = String(buildPlanText($oldPlan) || '').trim();
                        oldCut.parentNode.removeChild(oldCut);
                    }
                    originalRedraw.apply(this, arguments);
                    attachCuttingRows();
                    var $plan = $('#basket-item-' + itemId + '-cutting').find('[data-entity="cutting-plan"]');
                    var $parts = $plan.find('[data-entity="cutting-parts"]');
                    var enabled = $plan.attr('data-enabled') === 'Y';
                    if (livePlanText) {
                        $parts.attr('data-plan', livePlanText);
                        enabled = true;
                        $plan.attr('data-enabled', 'Y');
                    }
                    if ((enabled || (livePartsState && livePartsState.length)) && isCuttingUiOpen(itemId)) {
                        openCuttingPlan(itemId, true, {
                            skipSave: true,
                            skipFill: !!(livePartsState && livePartsState.length),
                            step: savedStep
                        });
                        if (livePartsState && livePartsState.length) {
                            applyCuttingPartsState($plan, livePartsState);
                            delete cuttingPartsStateCache[String(itemId)];
                            refreshCuttingPlan(itemId);
                        }
                        restoreCuttingWizardStep(itemId, savedStep);
                    } else {
                        syncCuttingToggleLabel(itemId);
                    }
                    updateBasketTotalWithCutting();
                    if (cartBody) {
                        cartBody.scrollTop = savedScrollTop;
                        requestAnimationFrame(function() {
                            cartBody.scrollTop = savedScrollTop;
                        });
                    }
                };
            }

            try {
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
            } catch (e) {}

            setTimeout(function() {
                restoreAllCuttingUi({ restoreScroll: true });
            }, 0);

            $(window).on('scroll.metplusCuttingScroll', function() {
                clearTimeout(window.__metplusCuttingScrollTimer);
                window.__metplusCuttingScrollTimer = setTimeout(saveCuttingPageScroll, 150);
            });
            $(window).on('beforeunload.metplusCuttingScroll pagehide.metplusCuttingScroll', function() {
                saveCuttingPageScroll();
            });

            window.MetplusBasketCutting = {
                refreshAll: function() {
                    attachCuttingRows();
                    updateBasketTotalWithCutting();
                },
                refreshPlan: refreshCuttingPlan,
                open: openCuttingPlan,
                syncPartsToSelectedTarget: syncPartsToSelectedTarget,
                rememberStep: setCuttingWizardStepStored,
                getStoredStep: getCuttingWizardStepStored,
                restoreUi: restoreAllCuttingUi,
                setStep: function(id, step) {
                    var $plan = $('#basket-item-' + id + '-cutting').find('[data-entity="cutting-plan"]');
                    if (!$plan.length || typeof window.metplusSetCuttingWizardStep !== 'function') {
                        return;
                    }
                    window.metplusSetCuttingWizardStep($plan, step);
                }
            };
            }); // jQuery ready
        })(0);
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