<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult['FORM_TYPE'] !== 'login') {
    return;
}

$phoneAuthOn = false;
if (\Bitrix\Main\Loader::includeModule('prime.phoneauth')) {
    $phoneAuthOn = \Prime\PhoneAuth\Config::isEnabled();
}
?>

<?php if ($arResult['SHOW_ERRORS'] === 'Y' && $arResult['ERROR']) {
    ShowMessage($arResult['ERROR_MESSAGE']);
} ?>

<div class="auth">
    <div class="title">
        <?php if (!empty($arParams['CHECKOUT_AUTH'])): ?>
            Для оформления заказа войдите или зарегистрируйтесь
        <?php else: ?>
            Авторизация
        <?php endif; ?>
    </div>

    <?php if ($phoneAuthOn): ?>
    <div class="prime-phoneauth-tabs" role="tablist">
        <button type="button" class="is-active" data-tab="password" role="tab">По e-mail</button>
        <button type="button" data-tab="phone" role="tab">По телефону</button>
    </div>
    <?php endif; ?>

    <div class="prime-phoneauth-panel is-active" data-panel="password">
        <form name="system_auth_form<?=$arResult['RND']?>" method="post" action="<?=$arResult['AUTH_URL']?>">
            <?php if ($arResult['BACKURL'] <> ''): ?>
                <input type="hidden" name="backurl" value="<?=$arResult['BACKURL']?>" />
            <?php endif; ?>
            <?php foreach ($arResult['POST'] as $key => $value): ?>
                <input type="hidden" name="<?=htmlspecialcharsbx($key)?>" value="<?=htmlspecialcharsbx($value)?>" />
            <?php endforeach; ?>
            <input type="hidden" name="AUTH_FORM" value="Y" />
            <input type="hidden" name="TYPE" value="AUTH" />
            <div class="line">
                <span class="label">E-mail:</span>
                <span class="value"><input type="email" name="USER_LOGIN" placeholder="username@mail.ru" value="<?=$arResult['USER_LOGIN']?>" autocomplete="username" /></span>
            </div>
            <div class="line">
                <span class="label">Пароль:</span>
                <span class="value"><input type="password" name="USER_PASSWORD" autocomplete="current-password" /></span>
                <span class="sublabel"><a href="<?=$arResult['AUTH_FORGOT_PASSWORD_URL']?>">Забыли пароль?</a></span>
            </div>
            <input type="submit" class="main-btn" value="Войти">
        </form>
    </div>

    <?php if ($phoneAuthOn): ?>
    <div class="prime-phoneauth-panel" data-panel="phone">
        <div class="prime-phoneauth-error" style="display:none"></div>
        <form class="prime-phoneauth-phone-form" action="#" method="post">
            <div class="line">
                <span class="label">Телефон:</span>
                <span class="value"><input type="tel" name="PHONE" placeholder="+7 (___) ___-__-__" autocomplete="tel" inputmode="tel"></span>
            </div>
            <input type="submit" class="main-btn" value="Продолжить">
        </form>
        <div class="prime-phoneauth-wait" style="display:none">
            <p data-role="message"></p>
            <p>Звоните с номера <strong data-role="from-phone"></strong></p>
            <p>Звоните на телефон: <a class="prime-phoneauth-number" data-role="call-number"></a></p>
            <ol class="prime-phoneauth-steps">
                <li>Наберите номер с того телефона, который указали</li>
                <li>Звонок сбросится сам — вы войдёте в аккаунт</li>
            </ol>
            <button type="button" class="prime-phoneauth-test" data-role="test">Я позвонил (тест)</button>
            <button type="button" class="prime-phoneauth-back" data-role="back">Другой способ входа</button>
        </div>
    </div>
    <?php endif; ?>

    <p class="auth-switch auth-switch--login">
        Нет аккаунта?
        <button type="button" class="auth-switch__btn js-auth-show-register">Зарегистрироваться</button>
    </p>
</div>
