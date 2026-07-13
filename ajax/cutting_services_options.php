<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("NEED_AUTH", false);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(!CModule::IncludeModule("iblock"))
    return;

$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$options = getProductCuttingServices($productId);
?>

<div class="message" style="text-align: center;">
    <h2>Выберите услугу</h2>

    <select id="cutting-type" style="width: 100%;padding: 5px 10px;margin-bottom:15px;">
        <?php foreach ($options as $option): ?>
            <?php
                $humanName = getCuttingServiceHumanName($option['CODE'] ?? '', $option['NAME'] ?? '');
                $price = (float)($option['VALUE'] ?? 0);
            ?>
            <option value="<?=htmlspecialcharsbx($option['CODE'])?>">
                <?=htmlspecialcharsbx($humanName)?><?=$price > 0 ? ' — ' . number_format($price, 0, '.', ' ') . ' ₽' : ''?>
            </option>
        <?php endforeach; ?>
    </select>

    <a href="javascript:void(0)" class="update-action main-btn">Изменить</a>
</div>
