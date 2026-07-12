<?php
use Bitrix\Main\Event;
use Bitrix\Sale\Basket;

function OnSaleBasketBeforeSavedHandler(Event $event)
{
    /** @var Basket $basket */
    $basket = $event->getParameter('ENTITY');

    // Удаляем все виртуальные позиции
    foreach ($basket as $basketItem) {
        $productId = $basketItem->getProductId();
        if ($productId === 0) {
            $basketItem->delete();
        }
    }

    foreach ($basket as $basketItem) {
        $productId = $basketItem->getProductId();

        foreach ($basketItem->getPropertyCollection() as $property) {
            if ($property->getField('CODE') == 'CUTTING_SERVICE') {

                $res = CIBlockElement::GetByID($productId);
                if($ar_res = $res->GetNext()) {
                    $length = getLengthProduct($ar_res['IBLOCK_ID'], $productId);
                    $quantity = $basketItem->getQuantity();
                    $needCut = fmod($quantity, $length);

                    if ($needCut > 0) {
                        $item = $basket->createItem('', 0);

                        $item->setFields([
                            'PRODUCT_ID' => 0, // У виртуального товара нет ID в каталоге
                            'NAME' => $property->getField('NAME'), // Название берем из свойства
                            'PRICE' => $property->getField('VALUE'), // Цену берем из свойства
                            'CUSTOM_PRICE' => 'Y', // Блокируем пересчет цены Битриксом
                            'QUANTITY' => 1, // Количество равно количеству основного товара
                            'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                            'LID' => \Bitrix\Main\Context::getCurrent()->getSite(),
                            'PRODUCT_PROVIDER_CLASS' => '',
                            'NOTES' => ''
                        ]);
                    }
                }
                break;
            }
        }
    }
}


