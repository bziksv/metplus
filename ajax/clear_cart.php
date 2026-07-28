<?php
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NEED_AUTH', false);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Sale;

header('Content-Type: application/json; charset=utf-8');

if (!Loader::includeModule('sale')) {
    echo json_encode(['success' => false, 'error' => 'Модуль sale не подключен']);
    die();
}

$basket = Sale\Basket::loadItemsForFUser(
    Sale\Fuser::getId(),
    Context::getCurrent()->getSite()
);

$deleted = 0;
foreach ($basket as $item) {
    $item->delete();
    $deleted++;
}

$result = $basket->save();

if (!$result->isSuccess()) {
    echo json_encode([
        'success' => false,
        'error' => implode(', ', $result->getErrorMessages()),
    ]);
    die();
}

echo json_encode([
    'success' => true,
    'deleted' => $deleted,
    'message' => $deleted > 0 ? 'Корзина очищена' : 'Корзина уже пуста',
]);
die();
