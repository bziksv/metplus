<?php

IncludeModuleLangFile(__FILE__);

class prime_roistatbitrixcms extends CModule {

    var $MODULE_ID = "prime.roistatbitrixcms";

    //при обявлении класса код выполняеться сразу
    function __construct(){

    $arModuleVersion = array();
    include(__DIR__.'/version.php');

    $this->MODULE_ID = 'prime.roistatbitrixcms';
    $this->MODULE_VERSION = $arModuleVersion['VERSION'];
    $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

    $this->MODULE_NAME = GetMessage("PRIME_MODULE_NAME");
    $this->MODULE_DESCRIPTION = GetMessage("PRIME_MODULE_DESCRIPTION");
    $this->PARTNER_NAME = GetMessage("PRIME_PARTNER_NAME");
    $this->PARTNER_URI = "https://prime-ltd.su/";

    }

    function DoInstall(){
        global $APPLICATION;
        RegisterModule($this->MODULE_ID);

        RegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, 'RoiStat','send');

        $APPLICATION->IncludeAdminFile($this->MODULE_NAME,$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/prime.roistatbitrixcms/install/step.php");
    }

    function DoUninstall(){
        global $APPLICATION;
        UnRegisterModule($this->MODULE_ID);

        UnRegisterModuleDependences('main', 'OnBeforeEventSend', $this->MODULE_ID, 'RoiStat','send');

        $APPLICATION->IncludeAdminFile($this->MODULE_NAME,$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/prime.roistatbitrixcms/install/unstep.php");
    }

}