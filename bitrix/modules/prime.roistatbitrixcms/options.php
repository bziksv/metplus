<?
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\File;
use Bitrix\Main\Type;

    $module_id = 'prime.roistatbitrixcms';

	Loader::includeModule('catalog');
	Loc::loadMessages(__FILE__);




    $arEvent = [];
    $dbType = CEventMessage::GetList($by="ID", $order="DESC", ["ACTIVE" => "Y"]);
    while($arType = $dbType->GetNext())
        $arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];


	$aTabs = array(
		array("DIV" => "edit0", "TAB" => GetMessage("PRIME_KEY_INTEGRATION_TITLE"), "ICON" => "currency_settings", "TITLE" => GetMessage("PRIME_KEY_INTEGRATION_TITLE")),
	);
	$tabControl = new CAdminForm("currencyTabControl", $aTabs);


	if ($_SERVER['REQUEST_METHOD'] == 'POST' && check_bitrix_sessid())
	{
	    if(strlen($_REQUEST['RoiProxyLeads']) > 0){
            COption::SetOptionString($module_id,"RoiProxyLeads",$_REQUEST['RoiProxyLeads']);
        }

	    if(isset($_REQUEST['RoiEvent']) && is_array($_REQUEST['RoiEvent']) && count($_REQUEST['RoiEvent']) > 0){
            COption::SetOptionString($module_id,"RoiEvent",serialize($_REQUEST['RoiEvent']));
        }
	}

	if(COption::GetOptionString($module_id, "RoiEvent"))
	    $RoiEvent = unserialize(COption::GetOptionString($module_id, "RoiEvent"));

    $tabControl->Begin( array(
        "FORM_ACTION" => $APPLICATION->GetCurUri()
    ));

    $tabControl->BeginNextFormTab();

    $tabControl->BeginCustomField( "RoiProxyLeads", GetMessage("PRIME_KEY_INTEGRATION"), false );
    echo bitrix_sessid_post();
	?>
        <tr id="tr_TYPE_OF_INFOBLOCK">
            <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
            <td width="60%">
                <input type="text" name="RoiProxyLeads" value="<?=COption::GetOptionString($module_id, "RoiProxyLeads")?>" size="100">
            </td>
        </tr>
	<?
    $tabControl->EndCustomField( "RoiProxyLeads" );


    $tabControl->BeginCustomField( "RoiEvent", GetMessage("PRIME_EVENT_MAIL"), false );
    ?>
    <tr id="tr_TYPE_OF_INFOBLOCK">
        <td width="40%"><? echo $tabControl->GetCustomLabelHTML(); ?></td>
        <td width="60%">
            <select name="RoiEvent[]" size="20" multiple>
                <? foreach ($arEvent as $id => $e):?>
                    <option value="<?=$id?>" <? if(isset($RoiEvent) && in_array($id, $RoiEvent)):?> selected <? endif;?>><?=$e?></option>
                <? endforeach; ?>
            </select>
        </td>
    </tr>
    <?
    $tabControl->EndCustomField( "RoiEvent" );

    $arButtonsParams = array(
        "disabled" => $readOnly,
        "back_url" => $backUrl,
    );

    $tabControl->Buttons( $arButtonsParams );
    $tabControl->Show();



