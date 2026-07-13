<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$catalogViewVariants = [
    'original' => [
        'label' => 'Оригинал',
        'title' => 'Текущее оформление каталога',
    ],
    'concept1' => [
        'label' => 'Мастерская',
        'title' => 'Плотная таблица, горизонтальное меню, акцент на данные',
    ],
    'concept2' => [
        'label' => 'Витрина',
        'title' => 'Карточки товаров, воздушная сетка, витринный стиль',
    ],
];

$requestedView = isset($_GET['catalog_view']) ? (string)$_GET['catalog_view'] : '';
$activeView = array_key_exists($requestedView, $catalogViewVariants) ? $requestedView : 'original';
?>
<div class="catalog-view-switcher" id="catalog-view-switcher">
    <button
        type="button"
        class="catalog-view-switcher__toggle"
        id="catalog-view-switcher-toggle"
        title="Варианты оформления каталога"
    >
        Каталог
    </button>
    <div class="catalog-view-switcher__panel" id="catalog-view-switcher-panel" hidden>
        <div class="catalog-view-switcher__title">Оформление каталога</div>
        <div class="catalog-view-switcher__buttons">
            <?php foreach ($catalogViewVariants as $viewId => $viewMeta): ?>
                <button
                    type="button"
                    class="catalog-view-switcher__btn<?=$viewId === $activeView ? ' is-active' : ''?>"
                    data-catalog-view="<?=$viewId?>"
                    title="<?=$viewMeta['title']?>"
                >
                    <?=$viewMeta['label']?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>
