<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Route;

trait AdminProviderTrait
{
    use ProviderTrait;
    protected function enforceAdminAuthentication(array $routeCollection)
    {
        $noAuthRoutes = array("dev-test", "admin-login");
        foreach ($routeCollection as $routeKey => &$route) {
            if ($routeKey === "attributes" && in_array($route["authentication"], array("admin", "adminConfirmation"))) {
                break;
            }
            if (!isset($route["authentication"]) && !in_array($route["name"], $noAuthRoutes)) {
                $route["authentication"] = "admin";
            }
        }
        return $routeCollection;
    }
    public function mutateAdminRoutesForCustomDirectory(array $adminRoutes = array())
    {
        $adminBasePath = \WHMCS\Admin\AdminServiceProvider::getAdminRouteBase();
        $mutatedRoutes = array();
        foreach ($adminRoutes as $key => $value) {
            if (is_array($value)) {
                $value = $this->enforceAdminAuthentication($value);
            }
            if (strpos($key, "/admin") === 0) {
                $mutatedKey = $adminBasePath . substr($key, 6);
                $mutatedRoutes[$mutatedKey] = $value;
            } else {
                $mutatedRoutes[$key] = $value;
            }
        }
        return $mutatedRoutes;
    }
    public function registerRoutes(\FastRoute\RouteCollector $routeCollector)
    {
        $this->addRouteGroups($routeCollector, $this->mutateAdminRoutesForCustomDirectory($this->getRoutes()));
    }
}

?>