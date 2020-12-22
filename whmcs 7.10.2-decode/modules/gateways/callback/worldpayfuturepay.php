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
$whmcs->load_function("clientarea");
$GATEWAY = getGatewayVariables("worldpayfuturepay");
if (!$GATEWAY["type"]) {
    exit("Module Not Activated");
}
$invoiceid = (int) App::getFromRequest("cartId");
$futurepayid = mysql_real_escape_string(App::getFromRequest("futurePayId"));
$transid = mysql_real_escape_string(App::getFromRequest("transId"));
$amount = (double) App::getFromRequest("authCost");
$invoiceid = checkCbInvoiceID($invoiceid, $GATEWAY["paymentmethod"]);
checkCbTransID($transid);
try {
    $invoice = new WHMCS\Invoice($invoiceid);
    $params = $invoice->getGatewayInvoiceParams();
} catch (Exception $e) {
    logTransaction($GATEWAY["paymentmethod"], array_merge($_POST, array("error" => $e->getMessage())), "Error");
}
$amount = App::getFromRequest("amount");
$currencyCode = App::getFromRequest("authCurrency");
$callbackCurrency = WHMCS\Database\Capsule::table("tblcurrencies")->where("code", $currencyCode)->first();
if (!$callbackCurrency) {
    logTransaction($GATEWAY["paymentmethod"], $_POST, "Unrecognised Currency");
    WHMCS\Terminus::getInstance()->doExit();
}
$callbackCurrencyId = $callbackCurrency->id;
$callbackCurrencyConversionRate = $callbackCurrency->rate;
$currency = getCurrency($params["userid"]);
if ($callbackCurrencyId != $currency["id"]) {
    $amount = convertCurrency($amount, $callbackCurrencyId, $currency["id"]);
    $invoiceAmount = $invoice->getData("total");
    if ($invoiceAmount < $amount + 1 && $amount - 1 < $invoiceAmount) {
        $amount = $invoiceAmount;
    }
}
initialiseClientArea($_LANG["ordercheckout"], "", $_LANG["ordercheckout"]);
$templateName = $whmcs->getClientAreaTemplate()->getName();
$templateVars = $smarty->getTemplateVars();
$templateVars["primarySidebar"] = Menu::primarySidebar("support");
$templateVars["secondarySidebar"] = Menu::secondarySidebar("support");
echo processSingleTemplate("/templates/" . $templateName . "/header.tpl", $templateVars);
echo "<WPDISPLAY ITEM=\"banner\">";
$transactionStatus = App::getFromRequest("transStatus");
if (!$futurepayid && $amount !== (double) "0.00") {
    $transactionStatus = "N";
}
if ($transactionStatus == "Y") {
    logTransaction($GATEWAY["paymentmethod"], $_POST, "Successful");
    try {
        if ($futurepayid && $amount !== (double) "0.00") {
            $invoiceModel = WHMCS\Billing\Invoice::findOrFail($invoiceid);
            $payMethod = $invoiceModel->saveRemoteBankAccount("Worldpay FuturePay", $futurepayid);
            $invoiceModel->addPayment($amount, $transid, 0, "worldpayfuturepay");
        }
    } catch (Exception $e) {
    }
    echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentsuccess=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
} else {
    logTransaction($GATEWAY["paymentmethod"], $_POST, "Unsuccessful");
    echo "<p align=\"center\"><a href=\"" . $CONFIG["SystemURL"] . "/viewinvoice.php?id=" . $invoiceid . "&paymentfailed=true\">Click here to return to " . $CONFIG["CompanyName"] . "</a></p>";
}
echo processSingleTemplate("/templates/" . $templateName . "/footer.tpl", $templateVars);

?>