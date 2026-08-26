<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * @var array $arParams
 * @var array $arResult
 */
?>

<form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data" class="form-callback">
    <?=bitrix_sessid_post()?>
    <div class="form-callback_title">
        Заказать звонок
        <small>Введите Ваш номер телефона <span class="min">и мы вам перезвоним.</span></small>
        <?if(!empty($arResult["ERROR_MESSAGE"]))
        {
            foreach($arResult["ERROR_MESSAGE"] as $v)
                ShowError($v);
        }
        ?>
    </div>
    <? foreach($arResult['USER_FIELD'] as $field):?>
    <div class="form-group">
        <labek class="form-label"><?=$field['NAME']?><?if($field['IS_REQUIRED'] == "Y"):?>*<?endif?>:</labek>
        <input type="<?=($field['CODE'] == 'PHONE') ? 'tel' : 'text'?>"
               class="form-input"
               name="<?=$field['CODE']?>"
               value="<?=$arResult[$field['CODE']]?>"
               placeholder="<?=$field['NAME']?>"
               <?if($field['IS_REQUIRED'] == "Y"):?>required<?endif?>
        >
    </div>
    <? endforeach; ?>

    <?php
    $submitLabel = 'Отправить';
    $submitClass = 'main-btn form-callback_submit';
    require $_SERVER['DOCUMENT_ROOT'] . '/include/legal/form-submit-block.php';
    ?>
</form>
