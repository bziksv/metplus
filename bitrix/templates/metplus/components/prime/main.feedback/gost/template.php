<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 */
?>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data" class="form-static_small">
    <?=bitrix_sessid_post()?>
    <div class="form-static_small_title">Остались вопросы?</div>
    <span class="text">Оставьте заявку на обратный звонок и мы вам перезвоним!</span>
    <?if(!empty($arResult["ERROR_MESSAGE"]))
    {
        foreach($arResult["ERROR_MESSAGE"] as $v)
            ShowError($v);
    }
    ?>
    <div class="row">
        <? foreach($arResult['USER_FIELD'] as $field):?>
        <div class="form-column">
            <div class="form-group">
                <input type="<?=($field['CODE'] == 'PHONE') ? 'tel' : 'text'?>"
                       class="form-input"
                       name="<?=$field['CODE']?>"
                       value="<?=$arResult[$field['CODE']]?>"
                       placeholder="<?=$field['NAME']?>"
                       <?if($field['IS_REQUIRED'] == "Y"):?>required<?endif?>
                >
            </div>
        </div>
        <? endforeach; ?>
    </div>
    <div class="form-static_small-actions">
        <?php
        $submitLabel = 'Перезвонить';
        $submitClass = 'form-static_small-submit main-btn';
        require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/form-submit-block.php';
        ?>
    </div>
</form>
