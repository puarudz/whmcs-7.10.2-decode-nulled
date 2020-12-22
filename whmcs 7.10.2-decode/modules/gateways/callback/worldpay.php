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
$GATEWAY = getGatewayVariables("worldpay");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
if ($GATEWAY["prpassword"] && $GATEWAY["prpassword"] != $_REQUEST["callbackPW"]) {
    logTransaction($GATEWAY["paymentmethod"], $_REQUEST, "Payment Response Password Mismatch");
    exit;
}
echo "<WPDISPLAY ITEM=\"banner\">";
if ($_POST["transStatus"] == "Y") {
    $invoiceid = checkCbInvoiceID($_POST["cartId"], $GATEWAY["paymentmethod"]);
    $invoice = WHMCS\Billing\Invoice::find($invoiceid);
    $amount = $_POST["amount"];
    checkCbTransID($_POST["transId"]);
    $userCurrency = getCurrency($invoice->clientId);
    if ($userCurrency["code"] != $_POST["currency"]) {
        $paymentCurrencyID = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", $_POST["currency"])->value("id");
        if (is_null($paymentCurrencyID)) {
            logTransaction($GATEWAY["paymentmethod"], $_POST, "Unsuccessful - Invalid Currency");
            exit;
        }
        $amount = convertCurrency($amount, $paymentCurrencyID, $userCurrency["id"]);
    }
    addInvoicePayment($invoiceid, $_POST["transId"], $amount, "", "worldpay");
    logTransaction($GATEWAY["paymentmethod"], $_POST, "Successful");
    echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
    exit;
}
logTransaction($GATEWAY["paymentmethod"], $_POST, "Unsuccessful");
echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";

?>