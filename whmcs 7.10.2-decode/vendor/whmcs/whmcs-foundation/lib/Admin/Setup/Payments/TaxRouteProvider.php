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

class TaxRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        $helpRoutes = array("/admin/setup/payments/tax" => array("attributes" => array("authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("Tax Configuration"));
        }), array("method" => array("GET", "POST"), "name" => "admin-setup-payments-tax-index", "path" => "", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\TaxController", "index"), "authentication" => "adminConfirmation"), array("method" => array("POST"), "name" => "admin-setup-payments-tax-settings", "path" => "/settings", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\TaxController", "saveSettings"), "authorization" => function (\WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization $authz) {
            return $authz->requireCsrfToken();
        }), array("method" => array("POST"), "name" => "admin-setup-payments-tax-create", "path" => "/create", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\TaxController", "create"), "authorization" => function (\WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization $authz) {
            return $authz->requireCsrfToken();
        }), array("method" => array("POST"), "name" => "admin-setup-payments-tax-delete", "path" => "/delete", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\TaxController", "delete"), "authorization" => function (\WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization $authz) {
            return $authz->requireCsrfToken();
        }, "authentication" => "adminConfirmation"), array("method" => array("POST"), "name" => "admin-setup-payments-tax-eu-rates", "path" => "/eu-rates", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\TaxController", "setupEuRates"), "authentication" => "adminConfirmation"), array("method" => array("POST"), "name" => "admin-setup-payments-tax-migrate", "path" => "/migrate", "handle" => array("WHMCS\\Admin\\Setup\\Payments\\TaxController", "migrateCustomField"), "authentication" => "adminConfirmation")));
        return $helpRoutes;
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-setup-payments-tax-";
    }
}

?>