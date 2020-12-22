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

abstract class HandleMapTrait
{
    protected $routes = array();
    public abstract function getMappedAttributeName();
    public function mapRoute($route)
    {
        $attributeName = $this->getMappedAttributeName();
        if (empty($route["handle"]) || empty($route[$attributeName])) {
            return $this;
        }
        $this->routes[serialize($route["handle"])] = $route[$attributeName];
        return $this;
    }
    public function getMappedRoute($key)
    {
        if (is_array($key) || is_object($key) && !$key instanceof \Closure) {
            $key = serialize($key);
        }
        if (isset($this->routes[$key])) {
            return $this->routes[$key];
        }
        return null;
    }
}

?>