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

abstract class AbstractProxyMiddleware implements \WHMCS\Route\Contracts\Middleware\ProxyInterface, \WHMCS\Route\Contracts\MapInterface
{
    use Strategy\AssumingMiddlewareTrait;
    use \WHMCS\Route\HandleMapTrait;
    public abstract function factoryProxyDriver($handle, \WHMCS\Http\Message\ServerRequest $request);
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $handle = $request->getAttribute("matchedRouteHandle");
        if (!$handle) {
            return $delegate->process($request);
        }
        $mappedHandle = $this->getMappedRoute($handle);
        if (is_null($mappedHandle)) {
            return $delegate->process($request);
        }
        $driver = $this->factoryProxyDriver($mappedHandle, $request);
        if (!$driver instanceof \Interop\Http\ServerMiddleware\MiddlewareInterface) {
            throw new \RuntimeException("Invalid \"%s\" route attribute defined for %s", $this->getMappedAttributeName(), $request->getUri()->getPath());
        }
        return $driver->process($request, $delegate);
    }
}

?>