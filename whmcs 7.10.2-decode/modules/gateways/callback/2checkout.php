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
App::load_function("clientarea");
App::load_function("gateway");
App::load_function("invoice");
$forceInline = false;
if (App::isInRequest("x_invoice_num")) {
    $invoiceId = App::getFromRequest("x_invoice_num");
} else {
    if (App::isInRequest("product_description")) {
        $invoiceId = App::getFromRequest("product_description");
        $forceInline = true;
    } else {
        $invoiceId = App::getFromRequest("merchant_order_id");
    }
}
try {
    $gatewayParams = getGatewayVariables("tco", $invoiceId);
    if (!$gatewayParams["type"]) {
        WHMCS\Terminus::getInstance()->doDie("Module Not Activated");
    }
    $class = "\\WHMCS\\Module\\Gateway\\TCO\\Standard";
    if ($forceInline || $gatewayParams["integrationMethod"] == "inline") {
        $class = "\\WHMCS\\Module\\Gateway\\TCO\\Inline";
    }
    $callback = new $class();
    $callback->clientCallback($gatewayParams);
} catch (Exception $e) {
    WHMCS\Terminus::getInstance()->doDie($e->getMessage());
}

?>