<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Utilities\Tools;

class ToolsRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $routes = array("/admin/utilities/tools" => array(array("method" => array("POST"), "name" => $this->getDeferredRoutePathNameAttribute() . "serversync-analyse", "path" => "/serversync/{serverid}", "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\ServerSync\\Controller", "analyse"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("WHM Import Script"));
        }), array("method" => array("POST"), "name" => $this->getDeferredRoutePathNameAttribute() . "serversync-review", "path" => "/serversync/{serverid}/process", "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\ServerSync\\Controller", "process"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("WHM Import Script"));
        }), array("method" => array("POST"), "name" => $this->getDeferredRoutePathNameAttribute() . "email-marketer-rule", "path" => "/email-marketer/manage[/{id:\\d+}]", "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\EmailMarketer\\Controller", "manage"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("Email Marketer"));
        }), array("method" => array("POST"), "name" => $this->getDeferredRoutePathNameAttribute() . "email-marketer-rule-save", "path" => "/email-marketer/save", "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\EmailMarketer\\Controller", "save"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAllPermission(array("Email Marketer"));
        }), array("method" => array("GET"), "name" => "admin-utilities-tools-tld-import-step-one", "path" => "/tldsync/import", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("Configure Domain Pricing"));
        }, "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\TldSync\\TldSyncController", "importStart")), array("method" => array("POST"), "name" => "admin-utilities-tools-tld-import-step-two", "path" => "/tldsync/import[/{registrar:\\w+}]", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAllPermission(array("Configure Domain Pricing"));
        }, "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\TldSync\\TldSyncController", "importLoad")), array("method" => array("POST"), "name" => "admin-utilities-tools-tld-import-do", "path" => "/tldsync/do-import", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAllPermission(array("Configure Domain Pricing"));
        }, "handle" => array("WHMCS\\Admin\\Utilities\\Tools\\TldSync\\TldSyncController", "importTlds"))));
        return $routes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-utilities-tools-";
    }
}

?>