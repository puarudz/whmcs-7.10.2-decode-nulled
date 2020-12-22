<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Server;

class ServerRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $routes = array("/admin/setup/servers" => array("attributes" => array("authentication" => "admin"), array("method" => array("POST"), "name" => $this->getDeferredRoutePathNameAttribute() . "meta-refresh", "path" => "/meta/refresh", "handle" => array("WHMCS\\Admin\\Server\\ServerController", "refreshRemoteData"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("Configure Servers"));
        })));
        return $routes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-setup-servers-";
    }
}

?>