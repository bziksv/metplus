<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$submitLabel = $submitLabel ?? 'Отправить';
$submitClass = $submitClass ?? 'form-static_submit-btn main-btn';
?>
<div class="form-submit-block static-form_right-column">
    <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/form-consent-notice.php'; ?>
    <input type="hidden" name="PARAMS_HASH" value="<?=$arResult['PARAMS_HASH']?>">
    <input type="submit" name="submit" value="<?=htmlspecialcharsbx($submitLabel)?>" class="<?=htmlspecialcharsbx($submitClass)?>">
</div>
