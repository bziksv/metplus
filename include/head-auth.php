<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;

if (!is_object($USER) || $USER->IsAuthorized()) {
    return;
}
?>
<div class="head-auth tablet-small_hidden">
    <button type="button" class="head-auth__btn js-open-auth" aria-haspopup="dialog">Войти</button>
</div>
