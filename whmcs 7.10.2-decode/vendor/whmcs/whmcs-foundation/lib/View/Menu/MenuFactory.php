<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Menu;

class MenuFactory extends \Knp\Menu\MenuFactory
{
    protected $loader = NULL;
    protected $rootItemName = NULL;
    public function __construct()
    {
        parent::__construct();
        $this->loader = new \Knp\Menu\Loader\ArrayLoader($this);
    }
    public function createItem($name, array $options = array())
    {
        $extension = new Factory\WhmcsExtension();
        $options = $extension->buildOptions($options);
        $item = parent::createItem($name, $options);
        $item = unserialize(sprintf("O:%d:\"%s\"%s", strlen("WHMCS\\View\\Menu\\Item"), "WHMCS\\View\\Menu\\Item", strstr(strstr(serialize($item), "\""), ":")));
        $extension->buildItem($item, $options);
        return $item;
    }
    protected function buildMenuStructure(array $structure = array())
    {
        return array("name" => $this->rootItemName, "children" => $structure);
    }
    public function emptySidebar()
    {
        return $this->loader->load($this->buildMenuStructure());
    }
    public function getLoader()
    {
        return $this->loader;
    }
    public function isOnRoutePath($routePathName, $wildcardMatch = false)
    {
        $route = routePath($routePathName);
        $requestUri = isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "";
        if ($wildcardMatch) {
            return substr($requestUri, 0, strlen($route)) == $route;
        }
        return $requestUri == $route;
    }
}

?>