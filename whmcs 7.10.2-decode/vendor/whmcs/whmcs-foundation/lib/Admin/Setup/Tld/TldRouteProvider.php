<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Setup\Tld;

class TldRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        return array("/admin/tld" => array(array("method" => array("POST"), "name" => "admin-tld-mass-configuration", "path" => "/mass-configuration", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAllPermission(array("Configure Domain Pricing"));
        }, "handle" => array("WHMCS\\Admin\\Setup\\Tld\\TldController", "massConfiguration"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-tld-";
    }
}

?>