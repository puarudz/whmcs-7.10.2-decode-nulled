<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment;

class PaymentRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    protected function getRoutes()
    {
        return array("/payment" => array(array("name" => $this->getDeferredRoutePathNameAttribute() . "remote-confirm", "method" => array("POST"), "path" => "/remote/confirm", "authorization" => function () {
            return (new \WHMCS\Security\Middleware\Authorization())->requireCsrfToken();
        }, "handle" => array("WHMCS\\Payment\\PaymentController", "confirm")), array("name" => $this->getDeferredRoutePathNameAttribute() . "remote-confirm", "method" => array("POST"), "path" => "/remote/confirm/update", "authorization" => function () {
            return (new \WHMCS\Security\Middleware\Authorization())->requireCsrfToken();
        }, "handle" => array("WHMCS\\Payment\\PaymentController", "update")), array("name" => $this->getDeferredRoutePathNameAttribute() . "get-existing-token", "method" => array("POST"), "path" => "/{module}/token/get", "authorization" => function () {
            return (new \WHMCS\Security\Middleware\Authorization())->requireCsrfToken();
        }, "handle" => array("WHMCS\\Payment\\PaymentController", "getRemoteToken"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "payment-";
    }
}

?>