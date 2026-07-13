<?php
use Bitrix\Main\Event;
use Bitrix\Sale\Basket;

/**
 * Больше не создаём виртуальные позиции «Резка_*» в корзине.
 * Резка хранится в свойствах товара (CUTTING_ENABLED / CUTTING_PLAN_TEXT)
 * и редактируется в блоке «хочу порезку» под позицией.
 *
 * Старые виртуальные строки (PRODUCT_ID = 0) просто удаляем.
 */
function OnSaleBasketBeforeSavedHandler(Event $event)
{
    /** @var Basket $basket */
    $basket = $event->getParameter('ENTITY');

    foreach ($basket as $basketItem) {
        if ((int)$basketItem->getProductId() === 0) {
            $basketItem->delete();
        }
    }
}
