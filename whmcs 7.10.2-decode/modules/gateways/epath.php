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
$GATEWAYMODULE["epathname"] = "epath";
$GATEWAYMODULE["epathvisiblename"] = "e-Path";
$GATEWAYMODULE["epathtype"] = "Invoices";
function epath_activate()
{
    defineGatewayField("epath", "text", "submiturl", "http://e-path.com.au/demo1/demo1/demo1.php", "Submit URL", "50", "Your unique secure e-Path payment page");
    defineGatewayField("epath", "text", "returl", "http://www.yourdomain.com/success.html", "Return URL", "50", "The URL you want users returning to once complete");
}
function epath_link($params)
{
    $invoiceid = $params["invoiceid"];
    $invoicetotal = $params["amount"];
    $billingcycle = WHMCS\Database\Capsule::table("tblhosting")->join("tblinvoiceitems", "tblhosting.id", "=", "tblinvoiceitems.relid")->where("tblinvoiceitems.type", "Hosting")->where("tblinvoiceitems.invoiceid", (int) $invoiceid)->value("tblhosting.billingcycle");
    if (is_null($billingcycle)) {
        $billingcycle = "Only Only";
    }
    $description = preg_replace("/[^A-Za-z0-9 -]/", "", $params["description"]);
    $code = "<form action=\"" . $params["submiturl"] . "\" method=\"post\" name=\"\">\n<input type=\"hidden\" name=\"ord\" value=\"" . $params["invoiceid"] . "\">\n<input type=\"hidden\" name=\"des\" value=\"" . $description . "\">\n<input type=\"hidden\" name=\"amt\" value=\"" . $params["amount"] . "\">\n<input type=\"hidden\" name=\"frq\" value=\"" . $billingcycle . "\">\n<input type=\"hidden\" name=\"ceml\" value=\"" . $params["clientdetails"]["email"] . "\">\n<input type=\"hidden\" name=\"ret\" value=\"" . $params["returl"] . "\">\n<input type=\"submit\" name=\"\" value=\"" . $params["langpaynow"] . "\">\n</form>";
    return $code;
}

?>