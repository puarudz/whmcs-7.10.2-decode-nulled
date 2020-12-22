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
if (!function_exists("getGatewaysArray")) {
    require ROOTDIR . "/includes/gatewayfunctions.php";
}
$paymentmethods = getGatewaysArray();
$apiresults = array("result" => "success", "totalresults" => count($paymentmethods));
foreach ($paymentmethods as $module => $name) {
    $apiresults["paymentmethods"]["paymentmethod"][] = array("module" => $module, "displayname" => $name);
}
$responsetype = "xml";

?>