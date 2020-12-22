<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Addon;

class AddonRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $routes = array("/admin/addons" => array("attributes" => array("authentication" => "admin"), array("method" => array("GET", "POST"), "name" => "admin-addons-index", "path" => "", "handle" => array("WHMCS\\Admin\\Addon\\AddonController", "index"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Addons"));
        }), array("method" => array("GET"), "name" => "admin-addons-detail", "path" => "/detail/{addonid:\\d+}", "handle" => array("WHMCS\\Admin\\Addon\\AddonController", "addonDetail"), "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("List Addons", "View Clients Products/Services"));
        }), array("method" => array("POST"), "name" => "admin-addons-subscription-info", "path" => "/{id:\\d+}/subscription/info", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAnyPermission(array("View Clients Products/Services"));
        }, "handle" => array("WHMCS\\Admin\\Addon\\AddonController", "subscriptionInfo")), array("method" => array("POST"), "name" => "admin-addons-cancel-subscription", "path" => "/{id:\\d+}/subscription/cancel", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->requireCsrfToken()->setRequireAnyPermission(array("Edit Clients Products/Services"));
        }, "handle" => array("WHMCS\\Admin\\Addon\\AddonController", "subscriptionCancel"))));
        return $routes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-addons-";
    }
}

?>