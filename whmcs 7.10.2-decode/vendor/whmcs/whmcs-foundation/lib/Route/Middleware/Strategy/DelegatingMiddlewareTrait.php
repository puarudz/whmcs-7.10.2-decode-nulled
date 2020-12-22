<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Route\Middleware\Strategy;

abstract class DelegatingMiddlewareTrait
{
    public abstract function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate);
    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $result = $this->_process($request, $delegate);
        if ($result instanceof \Psr\Http\Message\ResponseInterface || $result instanceof \WHMCS\Exception\HttpCodeException) {
            $response = $result;
        } else {
            $response = $delegate->process($result);
        }
        return $response;
    }
}

?>