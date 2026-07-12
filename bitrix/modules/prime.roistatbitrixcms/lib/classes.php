<?php
IncludeModuleLangFile(__FILE__);

class RoiStat {

    public static $roistat = 'nocookie';

    public function store()
    {
        if($_COOKIE["roistat_visit"]){

            session_start();
            $_SESSION["roistat_visit"] = $_COOKIE["roistat_visit"];
        }
    }

    public static function getRoiStat()
    {
        if($_SESSION["roistat_visit"])
            self::$roistat = $_SESSION["roistat_visit"];

        if($_COOKIE["roistat_visit"])
            self::$roistat = $_COOKIE["roistat_visit"];

        return self::$roistat;
    }

    public function send($arFields, $arTemplate)
    {
        $module = "prime.roistatbitrixcms";

        if(!empty(COption::GetOptionString($module, "RoiProxyLeads")) ||
            !empty(COption::GetOptionString($module, "RoiEvent"))){

            $RoiEvent = unserialize(COption::GetOptionString($module, "RoiEvent"));

            $mess = "";
            foreach($arFields as $keyField => $arField)
                $mess .= $keyField .':'. $arField.'; ';

            $roistatData = array(
                'roistat' => self::getRoiStat(),
                'key'     => COption::GetOptionString('prime.roistatbitrixcms', "RoiProxyLeads"),
                'title' => $arTemplate['ID'].' : '.$arTemplate['EVENT_NAME'],
                'name' =>  $arFields['FIO'] . $arFields['NAME'] . $arFields['ORDER_USER'] ?: "no name",
                'comment' => strip_tags($mess),
                'phone'   => $arFields['PHONE'],
                'email'   => $arFields['EMAIL'],
                'fields'  => array(
                    "price" => $arFields['PRICE'],
                    "bcc" => $arTemplate['BCC'],
                    "request" => $_SERVER['REQUEST_URI'],
                ),
            );

            //AddMessage2Log($roistatData, "prime.roistatbitrixcms");

            try {
                if(in_array($arTemplate['ID'], $RoiEvent))
                    file_get_contents("https://cloud.roistat.com/api/proxy/1.0/leads/add?" . http_build_query($roistatData));
            } catch (Exception $e) {}
        }
    }
}
