<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("Manage API Credentials", false);
$aInt->title = AdminLang::trans("setup.apicredentials");
$aInt->sidebar = "config";
$aInt->icon = "admins";
$aInt->helplink = "API_Authentication_Credentials";
$aInt->requireAuthConfirmation();
$controller = new WHMCS\Authentication\DeviceConfigurationController();
$request = WHMCS\Http\Message\ServerRequest::fromGlobals();
$action = $request->get("action");
$response = "";
if ($action == "generate") {
    check_token("WHMCS.admin.default");
    $response = $controller->generate($request);
} else {
    if ($action == "delete") {
        check_token("WHMCS.admin.default");
        $response = $controller->delete($request);
    } else {
        if ($action == "savefield") {
            check_token("WHMCS.admin.default");
            $response = $controller->updateFields($request);
        } else {
            if ($action == "getDevices") {
                $response = $controller->getDevices($request);
            } else {
                $request = $request->withAttribute("aInt", $aInt);
                $response = $controller->index($request);
            }
        }
    }
}
(new Zend\Diactoros\Response\SapiEmitter())->emit($response);
exit;

?>