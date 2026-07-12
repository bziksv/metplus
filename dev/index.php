<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use Bitrix\Sale;

if(!CModule::IncludeModule("iblock"))
    return;

$id = intval(4615823);

$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

$name = 'Резка абразив';
$value = 105;

foreach ($basket as $basketItem) {
    $productId = $basketItem->getProductId();

    if ($productId == $id) {
        $basketPropertyCollection = $basketItem->getPropertyCollection();
        foreach ($basketPropertyCollection as $propertyItem) {
            if ($propertyItem->getField('CODE') == 'CUTTING_SERVICE') {
                $propertyItem->delete();
                break;
            }
        }
        $basketPropertyCollection->setProperty(array(
            array(
                'NAME' => $name,
                'CODE' => 'CUTTING_SERVICE',
                'VALUE' => $value,
            ),
        ));
        $basketPropertyCollection->save();
        break;
    }
}
?>


<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>