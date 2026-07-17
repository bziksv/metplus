<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

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
?>
<div class="container basket-root--<?=$cartDisplayMode?>" data-cart-mode="<?=$cartDisplayMode?>">
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

    <div class="cart-content_body">
        <div class="empty-cart" style="text-align: center">
            <img src="<?=$this->GetFolder()?>/images/empty_cart.svg" style="margin-bottom: 30px">
            <div class="bx-sbb-empty-cart-text"><?=Loc::getMessage("SBB_EMPTY_BASKET_TITLE")?></div>
            <?
            if (!empty($arParams['EMPTY_BASKET_HINT_PATH']))
            {
                ?>
                <div class="bx-sbb-empty-cart-desc">
                    <?=Loc::getMessage(
                        'SBB_EMPTY_BASKET_HINT',
                        [
                            '#A1#' => '<a href="'.$arParams['EMPTY_BASKET_HINT_PATH'].'">',
                            '#A2#' => '</a>',
                        ]
                    )?>
                </div>
                <?
            }
            ?>
            <div style="margin-top: 24px;">
                <a href="/catalog/" class="main-btn">В каталог</a>
            </div>
        </div>
    </div>

    <div class="cart-content_footer">
        <div class="left-column">
            <?php if ($isCartPage): ?>
            <a href="/catalog/" class="gray-btn back-site_btn">В каталог</a>
            <?php else: ?>
            <a href="" class="gray-btn back-site_btn js_back-site">Вернуться к сайту</a>
            <?php endif; ?>
        </div>
        <?php if (!$isCartPage): ?>
        <div class="right-column">
            <a href="/cart/" class="main-btn cart-full-btn">Перейти в корзину</a>
        </div>
        <?php endif; ?>
    </div>

</div>
