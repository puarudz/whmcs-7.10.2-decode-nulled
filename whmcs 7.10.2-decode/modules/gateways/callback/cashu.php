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
$GATEWAY = getGatewayVariables("cashu");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
$amount = $_REQUEST["amount"];
$currency = $_REQUEST["currency"];
$trn_id = $_REQUEST["trn_id"];
$session_id = (int) $_REQUEST["session_id"];
$verificationString = $_REQUEST["verificationString"];
$invoiceid = checkCbInvoiceID($session_id, $GATEWAY["paymentmethod"]);
$verstr = array(strtolower($GATEWAY["merchantid"]), strtolower($trn_id), $GATEWAY["encryptionkeyword"]);
$verstr = implode(":", $verstr);
$verstr = sha1($verstr);
if ($verstr == $verificationString) {
    if (isset($GATEWAY["convertto"]) && 0 < strlen($GATEWAY["convertto"])) {
        $data = WHMCS\Database\Capsule::table("tblinvoices")->where("id", $invoiceid)->first(array("userid", "total"));
        $total = $data->total;
        $currencyArr = getCurrency($data->userid);
        $amount = convertCurrency($amount, $GATEWAY["convertto"], $currencyArr["id"]);
        $roundAmt = round($amount, 1);
        $roundTotal = round($total, 1);
        if ($roundAmt == $roundTotal) {
            $amount = $total;
        }
    }
    addInvoicePayment($invoiceid, $trn_id, $amount, "0", "cashu");
    $transactionStatus = "Successful";
    $success = true;
} else {
    $transactionStatus = "Invalid Hash";
    $success = false;
}
logTransaction($GATEWAY["paymentmethod"], $_REQUEST, $transactionStatus);
callback3DSecureRedirect($invoiceid, $success);

?>