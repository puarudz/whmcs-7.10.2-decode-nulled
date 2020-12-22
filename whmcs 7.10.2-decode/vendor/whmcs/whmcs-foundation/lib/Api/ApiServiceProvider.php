<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Api;

class ApiServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider implements \WHMCS\Route\Contracts\ProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    public function register()
    {
    }
    public function getRoutes()
    {
        return array("/api/v1" => array("attributes" => array("authentication" => "api", "authorization" => "api"), array("method" => array("GET", "POST"), "name" => "api-v1-action", "path" => "/{action}", "handle" => array("WHMCS\\Api\\ApplicationSupport\\Route\\Middleware\\HandleProcessor", "process"))), "/includes" => array("attributes" => array("authentication" => "api", "authorization" => "api"), array("method" => array("GET", "POST"), "name" => "api-legacy", "path" => "/api.php", "handle" => array("WHMCS\\Api\\ApplicationSupport\\Route\\Middleware\\HandleProcessor", "process"))));
    }
    public function registerRoutes(\FastRoute\RouteCollector $routeCollector)
    {
        $this->addRouteGroups($routeCollector, $this->getRoutes());
    }
}

?>