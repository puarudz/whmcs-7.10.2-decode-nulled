<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Support;

class SupportRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\AdminProviderTrait;
    public function getRoutes()
    {
        return array("/admin/support" => array(array("method" => array("POST"), "name" => "admin-support-ticket-open-additional-data", "path" => "/ticket/open/client/{clientId:\\d+}/additional/data", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("Open New Ticket"))->requireCsrfToken();
        }, "handle" => array("WHMCS\\Admin\\Support\\SupportController", "getAdditionalData")), array("method" => array("POST"), "name" => "admin-support-ticket-related-list", "path" => "/ticket/{ticketId:\\d+}/client/{clientId:\\d+}/services", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("View Support Ticket"))->requireCsrfToken();
        }, "handle" => array("WHMCS\\Admin\\Support\\SupportController", "getClientServices")), array("method" => array("POST"), "name" => "admin-support-ticket-set-related-service", "path" => "/ticket/{ticketId:\\d+}/client/{clientId:\\d+}/services/save", "authentication" => "admin", "authorization" => function () {
            return (new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authorization())->setRequireAllPermission(array("View Support Ticket"))->requireCsrfToken();
        }, "handle" => array("WHMCS\\Admin\\Support\\SupportController", "setRelatedService"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "admin-support-";
    }
}

?>