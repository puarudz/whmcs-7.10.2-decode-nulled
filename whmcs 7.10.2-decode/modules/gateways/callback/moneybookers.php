<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require "../../../init.php";
$whmcs->load_function("gateway");
$whmcs->load_function("invoice");
$GATEWAY = getGatewayVariables("moneybookers");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
header("HTTP/1.1 200 OK");
header("Status: 200 OK");
$invoiceid = (int) $whmcs->get_req_var("invoice_id");
$transactionId = $whmcs->get_req_var("transaction_id");
$transid = $_POST["mb_transaction_id"];
$merchant_id = $_POST["merchant_id"];
$mb_amount = $_POST["mb_amount"];
$amount = $_POST["amount"];
$mb_currency = $_POST["mb_currency"];
$currency = $_POST["currency"];
$md5sig = $_POST["md5sig"];
$status = $_POST["status"];
checkCbTransID($_POST["mb_transaction_id"]);
if ($GATEWAY["secretword"]) {
    $md5Secret = strtoupper(md5($GATEWAY["secretword"]));
    if (strtoupper(md5($merchant_id . $transactionId . $md5Secret . $mb_amount . $mb_currency . $status)) != $md5sig) {
        logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "MD5 Signature Failure");
        exit;
    }
}
$currencyid = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", $currency)->value("id");
if (!$currencyid) {
    logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Unrecognised Currency");
    exit;
}
if ($GATEWAY["convertto"]) {
    $data = WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->first(array("userid", "total"));
    $userid = $data->userid;
    $total = $data->total;
    $currency = getCurrency($userid);
    $amount = convertCurrency($amount, $currencyid, $currency["id"]);
    if ($total < $amount + 1 && $amount - 1 < $total) {
        $amount = $total;
    }
}
$transactionStatus = "Unsuccessful";
if ($_POST["status"] == "2") {
    $invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY["paymentmethod"]);
    addInvoicePayment($invoiceid, $transid, $amount, "", "moneybookers");
    $transactionStatus = "Successful";
}
logTransaction($GATEWAY["paymentmethod"], $_REQUEST, $transactionStatus);

?>