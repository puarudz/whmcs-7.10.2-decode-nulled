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

class AuthenticationProxy extends AbstractProxyMiddleware
{
    public function getMappedAttributeName()
    {
        return "authentication";
    }
    public function factoryProxyDriver($handle, \WHMCS\Http\Message\ServerRequest $request = NULL)
    {
        if ($handle == "api") {
            $driver = new \WHMCS\Api\ApplicationSupport\Route\Middleware\Authentication();
        } else {
            if ($handle == "admin") {
                $driver = new \WHMCS\Admin\ApplicationSupport\Route\Middleware\Authentication();
            } else {
                if ($handle == "adminConfirmation") {
                    $driver = new \WHMCS\Admin\ApplicationSupport\Route\Middleware\AuthenticationConfirmation();
                } else {
                    if (is_callable($handle)) {
                        $driver = $handle();
                    } else {
                        throw new \RuntimeException("blank or non admin/api authentication middleware not supported");
                    }
                }
            }
        }
        return $driver;
    }
}

?>