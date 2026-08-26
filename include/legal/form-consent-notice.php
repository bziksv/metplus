<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$consentChecked = !empty($arResult['PERSONAL_DATA_CONSENT']);
?>
<label class="form-consent">
    <input type="checkbox"
           name="PERSONAL_DATA_CONSENT"
           value="Y"
           class="form-consent__input"
           required
           <?= $consentChecked ? ' checked' : '' ?>>
    <span class="form-consent__text form-static_policy-text">
        Даю <a href="/legal/metplus-soglasie-obrabotki-pd/" target="_blank" rel="nofollow noopener">согласие на обработку персональных данных</a>
        и подтверждаю ознакомление с <a href="/legal/metplus-politika-obrabotki-pd/" target="_blank" rel="nofollow noopener">политикой обработки персональных данных</a>.
    </span>
</label>
