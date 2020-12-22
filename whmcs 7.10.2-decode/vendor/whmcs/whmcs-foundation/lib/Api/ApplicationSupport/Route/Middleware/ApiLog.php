<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Api\ApplicationSupport\Route\Middleware;

class ApiLog implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use \WHMCS\Route\Middleware\Strategy\AssumingMiddlewareTrait;
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $response = $delegate->process($request);
        $loggableRequest = \DI::make("runtimeStorage")->apiRequest;
        if (!$loggableRequest) {
            $loggableRequest = $request;
        }
        $logger = \DI::make("ApiLog");
        $logger->info($loggableRequest->getAction(), array("request" => $loggableRequest, "response" => $response));
        return $response;
    }
}

?>