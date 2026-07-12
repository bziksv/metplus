<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
if(($_REQUEST['AJAX_CALL'] == "Y" || $_SERVER['HTTP_X_REQUESTED_WITH']) && $_REQUEST['component']){
    $APPLICATION->ShowAjaxHead();
    $name = $_REQUEST['component'];

    if (file_exists(__DIR__.'/'.$name.'.php'))
        include_once (__DIR__.'/'.$name.'.php');
}
