<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\StripeAch;

class StripeAchRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    protected function getRoutes()
    {
        return array("/stripe_ach" => array(array("name" => $this->getDeferredRoutePathNameAttribute() . "exchange", "method" => array("POST"), "path" => "/token/exchange", "authorization" => function () {
            return (new \WHMCS\Security\Middleware\Authorization())->requireCsrfToken();
        }, "handle" => array("WHMCS\\Module\\Gateway\\StripeAch\\StripeAchController", "exchange"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "stripe-ach-";
    }
}

?>