<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;

if ($USER->IsAuthorized()) {
    echo '<p>Вы уже авторизованы.</p>';
    return;
}

if ($arResult['SHOW_SMS_FIELD'] === true) {
    include __DIR__ . '/sms.php';
    return;
}

$fieldLabels = [
    'NAME' => 'Имя',
    'EMAIL' => 'E-mail',
    'PERSONAL_PHONE' => 'Телефон',
    'PASSWORD' => 'Пароль',
    'CONFIRM_PASSWORD' => 'Подтверждение пароля',
];

if (!empty($arResult['ERRORS'])) {
    foreach ($arResult['ERRORS'] as $key => $error) {
        if (intval($key) == 0 && $key !== 0) {
            $arResult['ERRORS'][$key] = str_replace('#FIELD_NAME#', '&quot;' . ($fieldLabels[$key] ?? $key) . '&quot;', $error);
        }
    }
    ShowError(implode('<br>', $arResult['ERRORS']));
} elseif ($arResult['USE_EMAIL_CONFIRMATION'] === 'Y') {
    echo '<p>На указанный e-mail будет отправлено письмо для подтверждения регистрации.</p>';
}
?>

<div class="auth auth--register">
    <div class="title">Регистрация</div>

    <form method="post" action="<?= POST_FORM_ACTION_URI ?>" name="regform" enctype="multipart/form-data">
        <?php if ($arResult['BACKURL'] <> ''): ?>
            <input type="hidden" name="backurl" value="<?= $arResult['BACKURL'] ?>">
        <?php endif; ?>
        <?= bitrix_sessid_post() ?>

        <?php foreach ($arResult['SHOW_FIELDS'] as $field): ?>
            <?php
            if (!isset($fieldLabels[$field])) {
                continue;
            }
            $required = ($arResult['REQUIRED_FIELDS_FLAGS'][$field] ?? '') === 'Y';
            $value = htmlspecialcharsbx((string)($arResult['VALUES'][$field] ?? ''));
            ?>
            <div class="line">
                <span class="label"><?= $fieldLabels[$field] ?><?= $required ? '*' : '' ?>:</span>
                <span class="value">
                    <?php if ($field === 'PASSWORD' || $field === 'CONFIRM_PASSWORD'): ?>
                        <input type="password" name="REGISTER[<?= $field ?>]" value="<?= $value ?>" autocomplete="<?= $field === 'PASSWORD' ? 'new-password' : 'new-password' ?>">
                    <?php elseif ($field === 'EMAIL'): ?>
                        <input type="email" name="REGISTER[<?= $field ?>]" value="<?= $value ?>" autocomplete="email" placeholder="username@mail.ru">
                    <?php elseif ($field === 'PERSONAL_PHONE'): ?>
                        <input type="tel" name="REGISTER[<?= $field ?>]" value="<?= $value ?>" autocomplete="tel" inputmode="tel" placeholder="+7 (___) ___-__-__">
                    <?php else: ?>
                        <input type="text" name="REGISTER[<?= $field ?>]" value="<?= $value ?>" autocomplete="name">
                    <?php endif; ?>
                </span>
            </div>
        <?php endforeach; ?>

        <?php if ($arResult['USE_CAPTCHA'] === 'Y'): ?>
            <div class="line">
                <span class="label">Код с картинки*:</span>
                <span class="value">
                    <input type="hidden" name="captcha_sid" value="<?= $arResult['CAPTCHA_CODE'] ?>">
                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult['CAPTCHA_CODE'] ?>" width="180" height="40" alt="CAPTCHA">
                    <input type="text" name="captcha_word" maxlength="50" value="" autocomplete="off">
                </span>
            </div>
        <?php endif; ?>

        <div class="line auth-consent-line">
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/form-consent-notice.php'; ?>
        </div>

        <input type="submit" class="main-btn" name="register_submit_button" value="Зарегистрироваться">
    </form>

    <p class="auth-switch">
        Уже есть аккаунт?
        <button type="button" class="auth-switch__btn js-auth-show-login">Войти</button>
    </p>
</div>
