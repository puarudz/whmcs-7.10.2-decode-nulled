<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Controller;

class ErrorController
{
    use \WHMCS\Application\Support\Controller\DelegationTrait;
    public function loginRequired(\WHMCS\Http\Message\ServerRequest $request)
    {
        $msg = "Admin Login Required";
        if ($request->expectsJsonResponse()) {
            $response = new \WHMCS\Http\Message\JsonResponse(array("status" => "error", "errorMessage" => $msg), 403);
        } else {
            $response = $this->redirectTo("admin-login", $request);
        }
        return $response;
    }
}

?>