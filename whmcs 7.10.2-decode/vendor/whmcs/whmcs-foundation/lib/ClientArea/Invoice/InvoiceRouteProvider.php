<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ClientArea\Invoice;

class InvoiceRouteProvider implements \WHMCS\Route\Contracts\DeferredProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    public function getRoutes()
    {
        return array("/invoice" => array(array("name" => $this->getDeferredRoutePathNameAttribute() . "pay", "method" => array("GET", "POST"), "path" => "/{id:\\d+}/pay", "handle" => array("WHMCS\\ClientArea\\Invoice\\InvoiceController", "pay")), array("name" => $this->getDeferredRoutePathNameAttribute() . "pay-process", "method" => array("POST"), "path" => "/{id:\\d+}/process", "authorization" => function () {
            return (new \WHMCS\Security\Middleware\Authorization())->requireCsrfToken();
        }, "handle" => array("WHMCS\\ClientArea\\Invoice\\InvoiceController", "process"))));
    }
    public function getDeferredRoutePathNameAttribute()
    {
        return "invoice-";
    }
}

?>