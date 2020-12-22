<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Service;

class ServiceRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $routes = array("/admin/services" => array("attributes" => array("authentication" => "admin"), array("method" => array("GET", "POST"), "name" => "admin-services-index", "path" => "", "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "index"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Services"));
        }), array("method" => array("GET", "POST"), "name" => "admin-services-shared", "path" => "/shared", "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "shared"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Services"));
        }), array("method" => array("GET", "POST"), "name" => "admin-services-reseller", "path" => "/reseller", "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "reseller"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Services"));
        }), array("method" => array("GET", "POST"), "name" => "admin-services-server", "path" => "/server", "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "server"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Services"));
        }), array("method" => array("GET", "POST"), "name" => "admin-services-other", "path" => "/other", "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "other"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Services"));
        }), array("method" => array("GET"), "name" => "admin-services-detail", "path" => "/detail/{serviceid:\\d+}", "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "serviceDetail"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Services", "View Clients Products/Services"));
        }), array("method" => array("POST"), "name" => "admin-services-subscription-info", "path" => "/{id:\\d+}/subscription/info", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAnyPermission(array("View Clients Products/Services"));
        }, "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "subscriptionInfo")), array("method" => array("POST"), "name" => "admin-services-cancel-subscription", "path" => "/{id:\\d+}/subscription/cancel", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAnyPermission(array("Edit Clients Products/Services"));
        }, "handle" => array("WHMCS\\Admin\\Service\\ServiceController", "subscriptionCancel"))));
        return $routes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-services-";
    }
}

?>