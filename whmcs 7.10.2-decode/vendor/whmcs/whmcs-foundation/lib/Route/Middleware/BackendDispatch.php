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

class BackendDispatch implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use Strategy\AssumingMiddlewareTrait;
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        return $this->getDispatch($request)->dispatch($request);
    }
    public function getDispatch(\WHMCS\Http\Message\ServerRequest $request)
    {
        if ($request->isAdminRequest()) {
            return \DI::make("Backend\\Dispatcher\\Admin");
        }
        if ($request->isApiRequest()) {
            return \DI::make("Backend\\Dispatcher\\Api");
        }
        return \DI::make("Backend\\Dispatcher\\Client");
    }
}

?>