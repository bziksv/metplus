<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

$APPLICATION->SetPageProperty('title', 'Правила применения рекомендательных технологий — Металлинвест');
$APPLICATION->SetPageProperty('description', 'Правила применения рекомендательных технологий ООО «Корпорация Металлинвест» на сайте metplus-vrn.ru');
$APPLICATION->SetTitle('Правила применения рекомендательных технологий');

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
        <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/recommendation.php'; ?>
    </div>
</main>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
