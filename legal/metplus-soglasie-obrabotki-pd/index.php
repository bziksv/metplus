<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Согласие на обработку персональных данных — Металлинвест');
$APPLICATION->SetPageProperty('description', 'Согласие на обработку персональных данных ООО «Корпорация Металлинвест» на сайте metplus-vrn.ru');
$APPLICATION->SetTitle('Согласие на обработку персональных данных');

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
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/consent.php'; ?>
    </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
