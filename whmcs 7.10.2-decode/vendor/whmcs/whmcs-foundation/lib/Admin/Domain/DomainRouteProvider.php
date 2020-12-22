<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Domain;

class DomainRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $routes = array("/admin/domains" => array("attributes" => array("authentication" => "admin"), array("method" => array("GET", "POST"), "name" => "admin-domains-index", "path" => "", "handle" => array("WHMCS\\Admin\\Domain\\DomainController", "index"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Domains"));
        }), array("name" => "admin-domains-ssl-check", "method" => array("POST"), "path" => "/ssl-check", "handle" => array("WHMCS\\Admin\\Domain\\DomainController", "sslCheck"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken();
        }), array("method" => array("GET"), "name" => "admin-domains-detail", "path" => "/detail/{domainid:\\d+}", "handle" => array("WHMCS\\Admin\\Domain\\DomainController", "domainDetail"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Domains", "View Clients Domains"));
        }), array("method" => array("POST"), "name" => "admin-domains-subscription-info", "path" => "/{id:\\d+}/subscription/info", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAnyPermission(array("View Clients Domains"));
        }, "handle" => array("WHMCS\\Admin\\Domain\\DomainController", "subscriptionInfo")), array("method" => array("POST"), "name" => "admin-domains-cancel-subscription", "path" => "/{id:\\d+}/subscription/cancel", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAnyPermission(array("Edit Clients Domains"));
        }, "handle" => array("WHMCS\\Admin\\Domain\\DomainController", "subscriptionCancel"))));
        return $routes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-domains-";
    }
}

?>