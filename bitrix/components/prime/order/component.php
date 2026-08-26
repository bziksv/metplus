<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Currency\CurrencyManager,
	Bitrix\Main,
	Bitrix\Iblock,
    Bitrix\Sale;

global $USER;

Bitrix\Main\Loader::includeModule("sale");
Bitrix\Main\Loader::includeModule("catalog");

$arParams['PERSON_TYPE'] = ($arParams['PERSON_TYPE']) ? $arParams['PERSON_TYPE'] : "1";

$arResult = [];
$arResult["ERROR_MESSAGE"] = [];

if($arParams['PERSON_TYPE']){
    $db_props = CSaleOrderProps::GetList(
        array("SORT" => "ASC"),
        array(
            "PERSON_TYPE_ID" => $arParams['PERSON_TYPE']
        ),
        false,
        false,
        array()
    );
    while ($props = $db_props->Fetch())
    {
        $arResult['FIELD'][] = $props;
    }
}

if(check_bitrix_sessid() && $_SERVER["REQUEST_METHOD"] == "POST")
{
    global $USER;
    if (!is_object($USER) || !$USER->IsAuthorized()) {
        $arResult["ERROR_MESSAGE"][] = "Для оформления заказа необходимо авторизоваться";
    }

    $request = Context::getCurrent()->getRequest();

    foreach ($arResult['FIELD'] as $field){
        $value = trim((string)$request->get($field['CODE']));
        if($field['REQUIED'] == 'Y'){
            if($value === '')
                $arResult["ERROR_MESSAGE"][] = "Пустое поле: $field[NAME]";
        }

        if($value !== '' && isOrderPhoneField($field) && !isValidRuPhone($value))
            $arResult["ERROR_MESSAGE"][] = "Укажите корректный номер телефона";
    }

    if(empty($request['PERSONAL_DATA_CONSENT']))
        $arResult["ERROR_MESSAGE"][] = "Необходимо дать согласие на обработку персональных данных";

    $arResult['PERSONAL_DATA_CONSENT'] = !empty($request['PERSONAL_DATA_CONSENT']);
    $arResult['VALUES'] = [];
    foreach ($arResult['FIELD'] as $field) {
        if ($request->offsetExists($field['CODE'])) {
            $arResult['VALUES'][$field['CODE']] = htmlspecialcharsbx($request->get($field['CODE']));
        }
    }
    if ($request->offsetExists('COMMENT')) {
        $arResult['VALUES']['COMMENT'] = htmlspecialcharsbx($request->get('COMMENT'));
    }

    if(empty($arResult["ERROR_MESSAGE"]))
    {

        $siteId = Context::getCurrent()->getSite();

        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), $siteId);
        if(count($basket) > 0){

            $currencyCode = CurrencyManager::getBaseCurrency();
            $order = Sale\Order::create($siteId, $USER->isAuthorized() ? $USER->GetID() : 1);
            $order->setPersonTypeId($arParams['PERSON_TYPE']);
            $order->setField('CURRENCY', $currencyCode);

            if($request['COMMENT'])
                $order->setField('USER_DESCRIPTION', $request['COMMENT']);

            $propertyCollection = $order->getPropertyCollection();
            foreach($propertyCollection as $prop){
                $prop->setValue($request[$prop->getField('CODE')]);
            }

            $order->setBasket($basket);

            $order->doFinalAction(true);
            $result = $order->save();
            $arResult['ORDER_ID'] = $order->getId();
        }
    }
}

if(strlen($arResult['ORDER_ID']) > 0)
    $this->setTemplateName('successful');

$this->includeComponentTemplate();


