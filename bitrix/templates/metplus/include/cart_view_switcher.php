<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$cartViewVariants = [
    'new' => [
        'label' => 'Новая',
        'title' => 'Страница /cart/ с резкой целых и неполной штуки',
    ],
    'original' => [
        'label' => 'Оригинал',
        'title' => 'Классическая выезжающая корзина',
    ],
];

$requestedView = isset($_GET['cart_view']) ? (string)$_GET['cart_view'] : '';
$activeView = array_key_exists($requestedView, $cartViewVariants) ? $requestedView : 'new';
?>
<div class="cart-view-switcher" id="cart-view-switcher">
    <button
        type="button"
        class="cart-view-switcher__toggle"
        id="cart-view-switcher-toggle"
        title="Варианты оформления корзины"
    >
        Корзина: Новая
    </button>
    <div class="cart-view-switcher__panel" id="cart-view-switcher-panel" hidden>
        <div class="cart-view-switcher__title">Оформление корзины</div>
        <div class="cart-view-switcher__buttons">
            <?php foreach ($cartViewVariants as $viewId => $viewMeta): ?>
                <button
                    type="button"
                    class="cart-view-switcher__btn<?=$viewId === $activeView ? ' is-active' : ''?>"
                    data-cart-view="<?=$viewId?>"
                    title="<?=$viewMeta['title']?>"
                >
                    <?=$viewMeta['label']?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>
