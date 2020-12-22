<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Fraud;

class AbstractRequest
{
    protected $licenseKey = NULL;
    protected function log($action, $request, $response, $processedResponse)
    {
        $namespace = explode("\\", "WHMCS\\Module\\Fraud");
        $moduleName = end($namespace);
        return logModuleCall(strtolower($moduleName), $action, $request, $response, $processedResponse);
    }
    protected function getClient()
    {
        return new \GuzzleHttp\Client();
    }
}

?>