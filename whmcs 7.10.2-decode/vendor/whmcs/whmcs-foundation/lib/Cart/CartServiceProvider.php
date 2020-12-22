<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cart;

class CartServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider implements \WHMCS\Route\Contracts\ProviderInterface
{
    use \WHMCS\Route\ProviderTrait;
    protected function getRoutes()
    {
        return array("/cart" => array(array("name" => "cart-domain-renewals-add", "method" => array("POST"), "path" => "/domain/renew/add", "handle" => array("WHMCS\\Cart\\Controller\\DomainController", "addRenewal")), array("name" => "cart-domain-renewals", "method" => array("GET", "POST"), "path" => "/domain/renew", "handle" => array("WHMCS\\Cart\\Controller\\DomainController", "massRenew")), array("name" => "cart-domain-renew-calculate", "method" => array("GET"), "path" => "/domain/renew/calculate", "handle" => array("WHMCS\\Cart\\Controller\\DomainController", "calcRenewalCartTotals")), array("name" => "cart-invoice-pay-process", "method" => array("GET"), "path" => "/invoice/{id:\\d+}/pay", "handle" => array("WHMCS\\ClientArea\\Invoice\\InvoiceController", "processCardFromCart"))));
    }
    public function registerRoutes(\FastRoute\RouteCollector $routeCollector)
    {
        $this->addRouteGroups($routeCollector, $this->getRoutes());
    }
    public function register()
    {
    }
}

?>