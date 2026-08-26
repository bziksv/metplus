<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Политика обработки персональных данных — Металлинвест');
$APPLICATION->SetPageProperty('description', 'Политика обработки персональных данных ООО «Корпорация Металлинвест» на сайте metplus-vrn.ru');
$APPLICATION->SetTitle('Политика обработки персональных данных');

$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/css/legal.css');
?>
<main class="main-content">
    <div class="inner-page_title-section">
        <div class="container">
            <?php
            $APPLICATION->IncludeComponent(
                'bitrix:breadcrumb',
                'breadcrumb',
                ['SITE_ID' => SITE_ID],
                false
            );
            ?>
            <h1><?php $APPLICATION->ShowTitle(false); ?></h1>
        </div>
    </div>

    <div class="container" style="padding-bottom: 3em;">
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/personal-data.php'; ?>
    </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
