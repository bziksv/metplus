<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!empty($arParams['~AUTH_RESULT'])) {
    ShowMessage($arParams['~AUTH_RESULT']);
}
?>
<div class="auth auth--forgot">
    <div class="title">Восстановление пароля</div>
    <p class="auth-forgot-lead">Укажите e-mail или логин — мы отправим ссылку для смены пароля.</p>

    <form name="bform" method="post" action="<?= $arResult['AUTH_URL'] ?>">
        <?php if ($arResult['BACKURL'] <> ''): ?>
            <input type="hidden" name="backurl" value="<?= $arResult['BACKURL'] ?>">
        <?php endif; ?>
        <input type="hidden" name="AUTH_FORM" value="Y">
        <input type="hidden" name="TYPE" value="SEND_PWD">

        <div class="line">
            <span class="label">E-mail или логин*:</span>
            <span class="value">
                <input type="text" name="USER_LOGIN" value="<?= $arResult['USER_LOGIN'] ?>" autocomplete="username">
                <input type="hidden" name="USER_EMAIL">
            </span>
        </div>

        <?php if ($arResult['PHONE_REGISTRATION']): ?>
        <div class="line">
            <span class="label">Телефон:</span>
            <span class="value">
                <input type="tel" name="USER_PHONE_NUMBER" value="<?= $arResult['USER_PHONE_NUMBER'] ?>" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__">
            </span>
            <span class="sublabel">Или укажите номер — пришлём SMS с кодом.</span>
        </div>
        <?php endif; ?>

        <?php if ($arResult['USE_CAPTCHA']): ?>
        <div class="line">
            <span class="label">Символы с картинки*:</span>
            <span class="value">
                <input type="hidden" name="captcha_sid" value="<?= $arResult['CAPTCHA_CODE'] ?>">
                <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult['CAPTCHA_CODE'] ?>" width="180" height="40" alt="CAPTCHA">
                <input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off">
            </span>
        </div>
        <?php endif; ?>

        <input type="submit" class="main-btn" name="send_account_info" value="Отправить">
    </form>

    <p class="auth-switch">
        <a href="<?= $arResult['AUTH_AUTH_URL'] ?>">Вернуться ко входу</a>
    </p>
</div>
<script>
document.bform.onsubmit = function () {
    document.bform.USER_EMAIL.value = document.bform.USER_LOGIN.value;
};
</script>
