<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Sale;
use Bitrix\Main\Loader;

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/json; charset=utf-8');

if(!Loader::includeModule('catalog') || !Loader::includeModule('sale')) {
    echo json_encode(['success' => false, 'error' => 'Модули не подключены']);
    die();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$code = $_GET['type'] ?? '';

$cuttingService = getProductCuttingServices($productId);
if (count($cuttingService) > 0) {
    $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());

    $basketItem = $basket->getItemById($id);
    if (!$basketItem) {
        echo json_encode(['success' => false, 'error' => 'Позиция корзины не найдена']);
        die();
    }
    $basketPropertyCollection = $basketItem->getPropertyCollection();

    foreach ($cuttingService as $item) {
        if ($item['CODE'] == $code) {
            // redefine() перезаписывает коллекцию, поэтому сохраняем остальные свойства
            $existingProps = [];
            foreach ($basketPropertyCollection as $p) {
                $pCode = (string)$p->getField('CODE');
                if ($pCode === 'CUTTING_SERVICE') {
                    continue;
                }
                $existingProps[] = [
                    'NAME' => (string)$p->getField('NAME'),
                    'CODE' => $pCode,
                    'VALUE' => (string)$p->getField('VALUE'),
                ];
            }

            $basketPropertyCollection->redefine(array_merge($existingProps, [
                [
                    'NAME' => $item['NAME'],
                    'CODE' => 'CUTTING_SERVICE',
                    'VALUE' => $item['VALUE'],
                ]
            ]));
            break;
        }
    }

    $basketPropertyCollection->save();
    $basket->save();

    echo json_encode([
        'success' => true,
        'message' => 'Свойство корзины CUTTING_SERVICE обновлено'
    ]);
}

die();