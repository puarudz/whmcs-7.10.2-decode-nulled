<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Route\Middleware;

class RoutableClientModuleRequest implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use Strategy\AssumingMiddlewareTrait;
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        if (!$request->isAdminRequest() && ($moduleName = $request->get("m", ""))) {
            $moduleName = preg_replace("/[^a-zA-Z0-9._]/", "", $moduleName);
            $addonModule = new \WHMCS\Module\Addon();
            if (!$addonModule->load($moduleName) || !$addonModule->functionExists("clientarea")) {
                $controller = new \WHMCS\ClientArea\ClientAreaController();
                return $controller->homePage($request);
            }
            $uri = $request->getUri()->withPath("/clientarea/module/" . $moduleName);
            $request = $request->withUri($uri);
        }
        return $delegate->process($request);
    }
}

?>