<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;

if (is_object($USER) && $USER->IsAuthorized()) {
    return;
}
?>
<div aria-hidden="true" class="modal fade js-modal" id="authRequired" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-auth" role="document">
        <div class="modal-content auth-modal">
            <button aria-label="Close" class="close uhified_close-btn auth-modal__close" data-dismiss="modal" type="button"></button>
            <?php require $_SERVER['DOCUMENT_ROOT'] . '/include/auth-block.php'; ?>
        </div>
    </div>
</div>
