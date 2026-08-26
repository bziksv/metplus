<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Web\Json;

if ($arResult['PHONE_REGISTRATION']) {
    CJSCore::Init('phone_auth');
}

if (!empty($arParams['~AUTH_RESULT'])) {
    ShowMessage($arParams['~AUTH_RESULT']);
}
?>
<div class="auth auth--changepass">
    <div class="title">Новый пароль</div>

    <?php if ($arResult['SHOW_FORM']): ?>
    <form method="post" action="<?= $arResult['AUTH_URL'] ?>" name="bform">
        <?php if ($arResult['BACKURL'] <> ''): ?>
            <input type="hidden" name="backurl" value="<?= $arResult['BACKURL'] ?>">
        <?php endif; ?>
        <input type="hidden" name="AUTH_FORM" value="Y">
        <input type="hidden" name="TYPE" value="CHANGE_PWD">

        <?php if ($arResult['PHONE_REGISTRATION']): ?>
            <div class="line">
                <span class="label">Телефон:</span>
                <span class="value">
                    <input type="text" value="<?= htmlspecialcharsbx($arResult['USER_PHONE_NUMBER']) ?>" disabled>
                    <input type="hidden" name="USER_PHONE_NUMBER" value="<?= htmlspecialcharsbx($arResult['USER_PHONE_NUMBER']) ?>">
                </span>
            </div>
            <div class="line">
                <span class="label">Код из SMS*:</span>
                <span class="value"><input type="text" name="USER_CHECKWORD" maxlength="50" value="<?= $arResult['USER_CHECKWORD'] ?>" autocomplete="off"></span>
            </div>
        <?php elseif ($arResult['USE_PASSWORD']): ?>
            <div class="line">
                <span class="label">Логин*:</span>
                <span class="value"><input type="text" name="USER_LOGIN" maxlength="50" value="<?= $arResult['LAST_LOGIN'] ?>" autocomplete="username"></span>
            </div>
            <div class="line">
                <span class="label">Текущий пароль*:</span>
                <span class="value"><input type="password" name="USER_CURRENT_PASSWORD" maxlength="255" value="<?= $arResult['USER_CURRENT_PASSWORD'] ?>" autocomplete="current-password"></span>
            </div>
        <?php else: ?>
            <div class="line">
                <span class="label">Логин*:</span>
                <span class="value"><input type="text" name="USER_LOGIN" maxlength="50" value="<?= $arResult['LAST_LOGIN'] ?>" autocomplete="username"></span>
            </div>
            <div class="line">
                <span class="label">Контрольная строка*:</span>
                <span class="value"><input type="text" name="USER_CHECKWORD" maxlength="50" value="<?= $arResult['USER_CHECKWORD'] ?>" autocomplete="off"></span>
            </div>
        <?php endif; ?>

        <div class="line">
            <span class="label">Новый пароль*:</span>
            <span class="value"><input type="password" name="USER_PASSWORD" maxlength="255" value="<?= $arResult['USER_PASSWORD'] ?>" autocomplete="new-password"></span>
        </div>
        <div class="line">
            <span class="label">Подтверждение*:</span>
            <span class="value"><input type="password" name="USER_CONFIRM_PASSWORD" maxlength="255" value="<?= $arResult['USER_CONFIRM_PASSWORD'] ?>" autocomplete="new-password"></span>
        </div>

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

        <input type="submit" class="main-btn" name="change_pwd" value="Сохранить пароль">
    </form>

    <?php if ($arResult['PHONE_REGISTRATION']): ?>
    <script>
    new BX.PhoneAuth({
        containerId: 'bx_chpass_resend',
        errorContainerId: 'bx_chpass_error',
        interval: <?= (int)$arResult['PHONE_CODE_RESEND_INTERVAL'] ?>,
        data: <?= Json::encode(['signedData' => $arResult['SIGNED_DATA']]) ?>,
        onError: function (response) {
            var errorDiv = BX('bx_chpass_error');
            var errorNode = BX.findChildByClassName(errorDiv, 'errortext');
            errorNode.innerHTML = '';
            for (var i = 0; i < response.errors.length; i++) {
                errorNode.innerHTML += BX.util.htmlspecialchars(response.errors[i].message) + '<br>';
            }
            errorDiv.style.display = '';
        }
    });
    </script>
    <div id="bx_chpass_error" style="display:none"><?php ShowError('error') ?></div>
    <div id="bx_chpass_resend"></div>
    <?php endif; ?>

    <?php endif; ?>

    <p class="auth-switch">
        <a href="<?= $arResult['AUTH_AUTH_URL'] ?>">Вернуться ко входу</a>
    </p>
</div>
