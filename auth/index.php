<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $USER;

if (is_object($USER) && $USER->IsAuthorized()) {
    if (is_string($_REQUEST['backurl'] ?? null) && strpos($_REQUEST['backurl'], '/') === 0) {
        LocalRedirect($_REQUEST['backurl']);
    }
    LocalRedirect('/');
}

$isForgot = !empty($_REQUEST['forgot_password']) && $_REQUEST['forgot_password'] === 'yes';
$isChange = !empty($_REQUEST['change_password']) && $_REQUEST['change_password'] === 'yes';
$isRegister = !$isForgot && !$isChange && !empty($_REQUEST['register']) && $_REQUEST['register'] === 'yes';

if ($isForgot) {
    $APPLICATION->SetTitle('Восстановление пароля');
} elseif ($isChange) {
    $APPLICATION->SetTitle('Новый пароль');
} elseif ($isRegister) {
    $APPLICATION->SetTitle('Регистрация');
} else {
    $APPLICATION->SetTitle('Авторизация');
}
?>
<main class="main-content">
    <div class="container" style="padding: 2em 15px; max-width: 520px;">
        <?php if ($isForgot): ?>
            <div class="personal_enter">
                <?php $APPLICATION->IncludeComponent(
                    'bitrix:system.auth.forgotpasswd',
                    'phoneauth',
                    [],
                    false
                ); ?>
            </div>
        <?php elseif ($isChange): ?>
            <div class="personal_enter">
                <?php $APPLICATION->IncludeComponent(
                    'bitrix:system.auth.changepasswd',
                    'phoneauth',
                    [],
                    false
                ); ?>
            </div>
        <?php else: ?>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/auth-block.php'; ?>
        <?php endif; ?>
    </div>
</main>
<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';
