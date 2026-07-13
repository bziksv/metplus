<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Loader;
use Bitrix\Sale;

if (!Loader::includeModule('sale')) {
    return;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    return;
}

$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
$basketItem = $basket->getItemById($id);
if (!$basketItem) {
    return;
}

$enabled = 'N';
$plan = '';
foreach ($basketItem->getPropertyCollection() as $p) {
    if ($p->getField('CODE') === 'CUTTING_ENABLED') {
        $enabled = (string)$p->getField('VALUE') === 'Y' ? 'Y' : 'N';
    }
    if ($p->getField('CODE') === 'CUTTING_PLAN_TEXT') {
        $plan = (string)$p->getField('VALUE');
    }
}
?>

<div class="message" style="text-align: left;">
    <h2 style="text-align:center;">Резка</h2>

    <label style="display:flex; gap:8px; align-items:center; margin: 10px 0 12px;">
        <input type="checkbox" id="cutting-enabled" <?=$enabled === 'Y' ? 'checked' : ''?>>
        <span>Хочу порезку</span>
    </label>

    <div style="margin-bottom: 10px; color:#5a6b7d; font-size: 13px; line-height: 1.35;">
        Самый простой вариант: опишите партиями, какие штуки как резать. Пример:<br>
        <code>1 шт — 2.3м + 3.1м</code><br>
        <code>2 шт — по 1.5м (4 куска)</code><br>
        <code>остальные — без резки</code>
    </div>

    <textarea id="cutting-plan" style="width:100%; min-height:120px; padding: 8px 10px; border:1px solid #d5dde6; border-radius:4px;" placeholder="План резки..."><?=htmlspecialcharsbx($plan)?></textarea>

    <div style="display:flex; gap:10px; justify-content:center; margin-top: 12px;">
        <a href="javascript:void(0)" class="cutting-plan-save main-btn">Сохранить</a>
    </div>
</div>

