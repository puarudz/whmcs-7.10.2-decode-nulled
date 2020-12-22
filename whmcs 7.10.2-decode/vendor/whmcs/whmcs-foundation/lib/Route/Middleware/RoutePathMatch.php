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

class RoutePathMatch implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use Strategy\AssumingMiddlewareTrait;
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $dispatch = \DI::make("Route\\Dispatch");
        $route = $dispatch->dispatch($request->getMethod(), $request->getUri()->getPath());
        if ($route[0] == $dispatch::FOUND) {
            if (!empty($route[2])) {
                foreach ($route[2] as $attribute => $value) {
                    $request = $request->withAttribute($attribute, $value);
                }
            }
            $request = $request->withAttribute("matchedRouteHandle", $route[1]);
        }
        return $delegate->process($request);
    }
}

?>