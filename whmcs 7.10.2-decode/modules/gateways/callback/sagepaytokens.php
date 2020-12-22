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
$GATEWAY = getGatewayVariables("sagepaytokens");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
if ($protxsimmode) {
    $url = "https://test.sagepay.com/simulator/VSPDirectCallback.asp";
} else {
    if ($GATEWAY["testmode"]) {
        $url = "https://test.sagepay.com/gateway/service/direct3dcallback.vsp";
    } else {
        $url = "https://live.sagepay.com/gateway/service/direct3dcallback.vsp";
    }
}
$response = sagepaytokens_call($url, $_POST);
$baseStatus = $response["Status"];
$invoiceid = $_REQUEST["invoiceid"];
$storedInvoiceId = WHMCS\Module\Storage\EncryptedTransientStorage::forModule("sagepaytokens")->getValue("sagepaytokensinvoiceid", NULL);
if (!$invoiceid && !is_null($storedInvoiceId)) {
    $invoiceid = $storedInvoiceId;
}
$invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY["paymentmethod"]);
$callbacksuccess = false;
$email = "Credit Card Payment Failed";
switch ($response["Status"]) {
    case "OK":
        checkCbTransID($response["VPSTxId"]);
        addInvoicePayment($invoiceid, $response["VPSTxId"], "", "", "sagepaytokens", "on");
        $transactionStatus = "Successful";
        $email = "Credit Card Payment Confirmation";
        $callbacksuccess = true;
        break;
    case "NOTAUTHED":
        $transactionStatus = "Not Authed";
        break;
    case "REJECTED":
        $transactionStatus = "Rejected";
        break;
    case "FAIL":
        $transactionStatus = "Failed";
        break;
    default:
        $transactionStatus = "Error";
        break;
}
logTransaction($GATEWAY["paymentmethod"], $response, $transactionStatus);
sendMessage($email, $invoiceid);
if (!$callbacksuccess) {
    invoiceDeletePayMethod($invoiceid);
}
callback3DSecureRedirect($invoiceid, $callbacksuccess);

?>