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

class HandleProcessor implements \WHMCS\Route\Contracts\Middleware\StrategyInterface
{
    use \WHMCS\Route\Middleware\Strategy\DelegatingMiddlewareTrait;
    public function getApiFilePath(\WHMCS\Api\ApplicationSupport\Http\ServerRequest $request)
    {
        $action = $request->getAction();
        if (!isValidforPath($action)) {
            throw new \Exception("Invalid API Command Value");
        }
        $apiFilePath = ROOTDIR . "/includes/api/" . $action . ".php";
        if (!file_exists($apiFilePath)) {
            throw new \Exception("Command Not Found");
        }
        return $apiFilePath;
    }
    public function _process(\WHMCS\Http\Message\ServerRequest $request, \Interop\Http\ServerMiddleware\DelegateInterface $delegate)
    {
        $apiResults = $this->processV1Request($request);
        return \WHMCS\Api\ApplicationSupport\Http\ResponseFactory::factory($request, $apiResults);
    }
    public function getV1Api()
    {
        return new \WHMCS\Api();
    }
    public function processV1Request(\WHMCS\Api\ApplicationSupport\Http\ServerRequest $request)
    {
        $api = null;
        try {
            $params = array_merge($request->getQueryParams(), $request->getParsedBody());
            $api = $this->getV1Api();
            if ($user = $request->getAttribute("authenticatedUser")) {
                $api->setAdminUser($user->id);
            }
            $api->setAction($request->getAction())->setParams($params)->setRegisterLocalVars(true)->setRequest($request)->call();
            $apiResults = $api->getResults();
        } catch (\WHMCS\Exception\Api\FailedResponse $e) {
            if (is_object($api)) {
                $apiResults = $api->getResults();
            }
        } catch (\Exception $e) {
            $apiResults = array("result" => "error", "message" => $e->getMessage());
        }
        if (empty($apiResults)) {
            $apiResults = array("result" => "error", "message" => "Invalid API return");
        }
        return $apiResults;
    }
}

?>