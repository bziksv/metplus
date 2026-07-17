<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Sale;

$GLOBALS['APPLICATION']->RestartBuffer();
header('Content-Type: application/json; charset=utf-8');

if (!Loader::includeModule('sale')) {
    echo json_encode(['success' => false, 'error' => 'Модуль sale не подключен']);
    die();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$enabled = ($_GET['enabled'] ?? '') === 'Y' ? 'Y' : 'N';
$planText = trim((string)($_GET['plan'] ?? ''));

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный id позиции']);
    die();
}

$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
$basketItem = $basket->getItemById($id);
if (!$basketItem) {
    echo json_encode(['success' => false, 'error' => 'Позиция корзины не найдена']);
    die();
}

$productId = (int)$basketItem->getProductId();
$cuttingSurcharge10 = 'N';
$needPriceRefresh = false;
$oldSurcharge10 = 'N';

foreach ($basketItem->getPropertyCollection() as $property) {
    if ((string)$property->getField('CODE') === 'CUTTING_SURCHARGE_10') {
        $oldSurcharge10 = (string)$property->getField('VALUE') === 'Y' ? 'Y' : 'N';
        break;
    }
}

if ($enabled === 'Y' && isBasicSheetProduct($productId)) {
    $analysis = analyzeBasicSheetCuttingPlan($planText, $productId);
    if ($analysis['hasComplexCut'] && !basicSheetSkipsIncompletePieceSurcharge($productId)) {
        $cuttingSurcharge10 = 'Y';
    }
} elseif ($enabled !== 'Y' && $oldSurcharge10 === 'Y') {
    $needPriceRefresh = true;
}

if ($cuttingSurcharge10 !== $oldSurcharge10) {
    $needPriceRefresh = true;
}

// redefine() перезаписывает коллекцию, поэтому сохраняем остальные свойства
$existingProps = [];
foreach ($basketItem->getPropertyCollection() as $p) {
    $code = (string)$p->getField('CODE');
    if (in_array($code, ['CUTTING_ENABLED', 'CUTTING_PLAN_TEXT', 'CUTTING_SURCHARGE_10'], true)) {
        continue;
    }
    $existingProps[] = [
        'NAME' => (string)$p->getField('NAME'),
        'CODE' => $code,
        'VALUE' => (string)$p->getField('VALUE'),
    ];
}

$props = array_merge($existingProps, [
    [
        'NAME' => 'Хочу порезку',
        'CODE' => 'CUTTING_ENABLED',
        'VALUE' => $enabled,
    ],
    [
        'NAME' => 'План резки',
        'CODE' => 'CUTTING_PLAN_TEXT',
        'VALUE' => $planText,
    ],
    [
        'NAME' => 'Наценка +10% за сложную резку',
        'CODE' => 'CUTTING_SURCHARGE_10',
        'VALUE' => $cuttingSurcharge10,
    ],
]);

$basketItem->getPropertyCollection()->redefine($props);
$basketItem->getPropertyCollection()->save();

if (isBasicSheetProduct($productId)) {
    refreshBasketItemCustomPrice($basketItem);
    $needPriceRefresh = true;
}

$basket->save();

echo json_encode([
    'success' => true,
    'needPriceRefresh' => $needPriceRefresh,
    'cuttingSurcharge10' => $cuttingSurcharge10 === 'Y',
]);
die();
