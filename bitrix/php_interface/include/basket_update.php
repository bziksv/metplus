<?php
use Bitrix\Main\Event;
use Bitrix\Sale\Basket;

/**
 * Больше не создаём виртуальные позиции «Резка_*» в корзине.
 * Резка хранится в свойствах товара (CUTTING_ENABLED / CUTTING_PLAN_TEXT)
 * и редактируется в блоке «хочу порезку» под позицией.
 *
 * Старые виртуальные строки (PRODUCT_ID = 0) просто удаляем.
 * «ТОЛЬКО ШТ» — QUANTITY кратно длине штуки.
 */
function snapBasketItemOnlyPiecesQuantity($basketItem)
{
    $productId = (int)$basketItem->getProductId();
    if ($productId <= 0) {
        return false;
    }

    if (!isOnlyPiecesProduct(getPropVal(36, $productId, 'TOLKO_SHT'))) {
        return false;
    }

    $lengthPerPiece = (float)getPropVal(36, $productId, 'DLINA_RASCHET');
    if ($lengthPerPiece <= 0) {
        return false;
    }

    $qty = (float)$basketItem->getQuantity();
    $snapped = snapOnlyPiecesMetersQuantity($qty, $lengthPerPiece);
    if (abs($snapped - $qty) <= 0.0001) {
        return false;
    }

    $basketItem->setField('QUANTITY', $snapped);
    return true;
}

/**
 * Нормализация «ТОЛЬКО ШТ» до рендера корзины (иначе сумма ещё по старым метрам).
 */
function normalizeCurrentBasketOnlyPiecesQuantities()
{
    if (!\Bitrix\Main\Loader::includeModule('sale')) {
        return;
    }

    $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
        \Bitrix\Sale\Fuser::getId(),
        SITE_ID
    );

    $changed = false;
    foreach ($basket as $basketItem) {
        if (snapBasketItemOnlyPiecesQuantity($basketItem)) {
            $changed = true;
        }
    }

    if ($changed) {
        $basket->save();
    }
}

function OnSaleBasketBeforeSavedHandler(Event $event)
{
    /** @var Basket $basket */
    $basket = $event->getParameter('ENTITY');

    foreach ($basket as $basketItem) {
        if ((int)$basketItem->getProductId() === 0) {
            $basketItem->delete();
            continue;
        }

        snapBasketItemOnlyPiecesQuantity($basketItem);
    }
}
