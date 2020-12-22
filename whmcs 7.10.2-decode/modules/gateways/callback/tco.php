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
$gatewayModuleName = "tco";
App::load_function("gateway");
App::load_function("invoice");
try {
    $requestHelper = new WHMCS\Module\Gateway\TCO\CallbackRequestHelper(WHMCS\Http\Message\ServerRequest::fromGlobals());
    $gatewayParams = $requestHelper->getGatewayParams();
    $callable = $requestHelper->getCallable();
    $result = call_user_func($callable, $gatewayParams);
} catch (Exception $e) {
    WHMCS\Terminus::getInstance()->doDie($e->getMessage());
}

?>