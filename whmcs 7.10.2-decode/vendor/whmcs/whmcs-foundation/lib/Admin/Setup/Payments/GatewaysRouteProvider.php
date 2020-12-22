<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Setup\Payments;

class GatewaysRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $routes = array("/admin/setup/payments/gateways" => array("attributes" => array("authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("Configure Payment Gateways"));
        }), array("method" => array("GET", "POST"), "name" => "admin-setup-payments-gateways-onboarding-return", "path" => "/onboarding/return", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\GatewaysController", "handleOnboardingReturn")), array("method" => array("POST"), "name" => $this->getDeferredRoutePathNameAttribute() . "action", "path" => "/{gateway:\\w+}/action/{method:\\w+}", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\GatewaysController", "callAdditionalFunction"))));
        return $routes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-setup-payments-gateways-";
    }
}

?>