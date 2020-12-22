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
if (!function_exists("captureCCPayment")) {
    require ROOTDIR . "/includes/ccfunctions.php";
}
if (!function_exists("getClientsDetails")) {
    require ROOTDIR . "/includes/clientfunctions.php";
}
if (!function_exists("processPaidInvoice")) {
    require ROOTDIR . "/includes/invoicefunctions.php";
}
$result = select_query("tblinvoices", "id", array("id" => $invoiceid, "status" => "Unpaid"));
$data = mysql_fetch_array($result);
$invoiceid = $data["id"];
if (!$invoiceid) {
    $apiresults = array("result" => "error", "message" => "Invoice Not Found or Not Unpaid");
} else {
    $ccResult = captureCCPayment($invoiceid, $cvv);
    if (is_string($ccResult) && $ccResult == "success" || is_string($ccResult) && $ccResult == "pending" || is_bool($ccResult) && $ccResult) {
        $apiresults = array("result" => "success");
    } else {
        $apiresults = array("result" => "error", "message" => "Payment Attempt Failed");
    }
}

?>