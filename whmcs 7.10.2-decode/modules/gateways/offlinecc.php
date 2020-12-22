<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
function offlinecc_MetaData()
{
    return array("gatewayType" => WHMCS\Module\Gateway::GATEWAY_CREDIT_CARD, "processingType" => WHMCS\Module\Gateway::PROCESSING_OFFLINE);
}
function offlinecc_config()
{
    return array("FriendlyName" => array("Type" => "System", "Value" => "Offline Credit Card"), "RemoteStorage" => true);
}

?>