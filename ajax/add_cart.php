<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Catalog\Product\Basket;
use Bitrix\Main\Context;
use Bitrix\Sale;

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/json; charset=utf-8');

if (!Loader::includeModule('catalog') || !Loader::includeModule('sale')) {
    echo json_encode(['success' => false, 'error' => 'Модули не подключены']);
    die();
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$quantity = isset($_GET['quantity']) ? floatval(str_replace(',', '.', $_GET['quantity'])) : 1.0;

if ($productId <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверные параметры товара']);
    die();
}

ensureCatalogProductOrderable($productId);

if (isCustomPrice(36, $productId)) {
    PriceUpdater::syncProductPrices($productId);
}

$fields = [
    'PRODUCT_ID' => $productId,
    'QUANTITY'   => $quantity,
    'PROPS' => [],
];

$cuttingService = getProductCuttingServices($productId);
if (count($cuttingService) > 0) {
    $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

    $isEmptyService = true;

    foreach ($basket as $basketItem) {
        if ($basketItem->getProductId() == $productId) {
            $basketPropertyCollection = $basketItem->getPropertyCollection();
            foreach ($basketPropertyCollection as $propertyItem) {
                if ($propertyItem->getField('CODE') == 'CUTTING_SERVICE') {
                    $fields['PROPS'][] = [
                        'NAME' => $propertyItem->getField('NAME'),
                        'CODE' => 'CUTTING_SERVICE',
                        'VALUE' => $propertyItem->getField('VALUE')
                    ];
                    $isEmptyService = false;
                    break;
                }
            }
            break;
        }
    }

    if ($isEmptyService) {
        $fields['PROPS'][] = [
            'NAME' => $cuttingService[0]['NAME'],
            'CODE' => 'CUTTING_SERVICE',
            'VALUE' => $cuttingService[0]['VALUE']
        ];
    }

    // Резку включаем только по явному желанию покупателя (не автоматически)
    $fields['PROPS'][] = [
        'NAME' => 'Хочу порезку',
        'CODE' => 'CUTTING_ENABLED',
        'VALUE' => 'N'
    ];
}

$result = Basket::addProduct($fields);

if ($result->isSuccess()) {
    echo json_encode([
        'success' => true,
        'message' => 'Товар успешно добавлен в количестве ' . $quantity
    ]);
} else {
    $errors = $result->getErrorMessages();
    echo json_encode([
        'success' => false,
        'error' => implode(', ', $errors)
    ]);
}

die();