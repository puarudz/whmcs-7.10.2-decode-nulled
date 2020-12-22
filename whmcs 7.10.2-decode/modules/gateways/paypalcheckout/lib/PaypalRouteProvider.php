<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\Paypalcheckout;

class PaypalRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    public function getRoutes()
    {
        return array("/paypal/checkout" => array(array("name" => $this->getDeferredRoutePathNameAttribute() . "create-order", "method" => array("POST"), "path" => "/order/create", "handle" => array("WHMCS\\Module\\Gateway\\Paypalcheckout\\PaypalController", "createOrder")), array("name" => $this->getDeferredRoutePathNameAttribute() . "validate-order", "method" => array("POST"), "path" => "/order/validate", "handle" => array("WHMCS\\Module\\Gateway\\Paypalcheckout\\PaypalController", "validateOrder")), array("name" => $this->getDeferredRoutePathNameAttribute() . "verify-payment", "method" => array("POST"), "path" => "/payment/verify", "handle" => array("WHMCS\\Module\\Gateway\\Paypalcheckout\\PaypalController", "verifyPayment")), array("name" => $this->getDeferredRoutePathNameAttribute() . "verify-subscription-setup", "method" => array("GET"), "path" => "/subscription/verify/{invoice_id:\\d+}", "handle" => array("WHMCS\\Module\\Gateway\\Paypalcheckout\\PaypalController", "verifySubscriptionSetup"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "paypal-checkout-";
    }
}

?>