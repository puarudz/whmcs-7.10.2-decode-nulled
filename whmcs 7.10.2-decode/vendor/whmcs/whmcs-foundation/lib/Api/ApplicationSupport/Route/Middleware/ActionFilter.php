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

class ActionFilter implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use \WHMCS\Route\Middleware\Strategy\AssumingMiddlewareTrait;
    protected $apiFunctionsRestrictedToLocalApi = array("setconfigurationvalue");
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $action = $request->getAction();
        $action = preg_replace("/[^0-9a-z]/i", "", strtolower($action));
        $action = $this->resolveLegacyAction($action);
        $request = $request->withAttribute("action", $action);
        if ($this->isRestrictedToLocalApi($request)) {
            throw new \Exception("API Command Restricted to Internal API");
        }
        return $delegate->process($request);
    }
    public function isRestrictedToLocalApi(\WHMCS\Api\ApplicationSupport\Http\ServerRequest $request)
    {
        return in_array($request->getAction(), $this->apiFunctionsRestrictedToLocalApi);
    }
    protected function resolveLegacyAction($action = "")
    {
        switch ($action) {
            case "adduser":
                $action = "addclient";
                break;
            case "getclientsdata":
            case "getclientsdatabyemail":
                $action = "getclientsdetails";
                break;
        }
        return $action;
    }
}

?>