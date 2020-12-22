<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

function directdebit_MetaData()
{
    return array("gatewayType" => WHMCS\Module\Gateway::GATEWAY_BANK, "failedEmail" => "Direct Debit Payment Failed", "successEmail" => "Direct Debit Payment Confirmation", "pendingEmail" => "Direct Debit Payment Pending", "processingType" => WHMCS\Module\Gateway::PROCESSING_OFFLINE);
}
function directdebit_config()
{
    $configarray = array("FriendlyName" => array("Type" => "System", "Value" => "Direct Debit"));
    return $configarray;
}
function directdebit_localbankdetails()
{
}

?>