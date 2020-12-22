<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\Stripe;

class StripeRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    protected function getRoutes()
    {
        return array("/stripe" => array(array("name" => $this->getDeferredRoutePathNameAttribute() . "payment-intent", "method" => array("POST"), "path" => "/payment/intent", "handle" => array("WHMCS\\Module\\Gateway\\Stripe\\StripeController", "intent")), array("name" => $this->getDeferredRoutePathNameAttribute() . "payment-method-add", "method" => array("POST"), "path" => "/payment/add", "handle" => array("WHMCS\\Module\\Gateway\\Stripe\\StripeController", "add")), array("name" => $this->getDeferredRoutePathNameAttribute() . "setup-intent", "method" => array("POST"), "path" => "/setup/intent", "handle" => array("WHMCS\\Module\\Gateway\\Stripe\\StripeController", "setupIntent"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "stripe-";
    }
}

?>