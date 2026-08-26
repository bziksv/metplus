<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$activeMode = (!empty($_REQUEST['register']) && $_REQUEST['register'] === 'yes') ? 'register' : 'login';
$authParams = [
    'REGISTER_URL' => '/auth/?register=yes',
    'FORGOT_PASSWORD_URL' => '/auth/',
    'PROFILE_URL' => '/personal/',
    'SHOW_ERRORS' => 'Y',
];
$registerParams = [
    'SHOW_FIELDS' => ['NAME', 'EMAIL', 'PERSONAL_PHONE', 'PASSWORD', 'CONFIRM_PASSWORD'],
    'REQUIRED_FIELDS' => ['EMAIL', 'PERSONAL_PHONE', 'PASSWORD', 'CONFIRM_PASSWORD'],
    'AUTH' => 'Y',
    'USE_BACKURL' => 'Y',
    'SUCCESS_PAGE' => '',
];
?>
<div class="personal_enter">
    <div class="auth-shell">
        <div class="auth-shell__notice" aria-live="polite"></div>

        <div class="auth-mode-panel<?= $activeMode !== 'register' ? ' is-active' : '' ?>" data-mode-panel="login">
            <?php $APPLICATION->IncludeComponent(
                'bitrix:system.auth.form',
                'phoneauth',
                $authParams,
                false
            ); ?>
        </div>

        <div class="auth-mode-panel<?= $activeMode === 'register' ? ' is-active' : '' ?>" data-mode-panel="register">
            <div class="reg">
                <?php $APPLICATION->IncludeComponent(
                    'bitrix:main.register',
                    'phoneauth',
                    $registerParams,
                    false
                ); ?>
            </div>
        </div>
    </div>
</div>
