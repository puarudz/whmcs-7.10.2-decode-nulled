<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require_once "init.php";
require_once ROOTDIR . "/includes" . DIRECTORY_SEPARATOR . "clientareafunctions.php";
$controller = new WHMCS\ClientArea\PasswordResetController();
$request = WHMCS\Http\Message\ServerRequest::fromGlobals();
$response = NULL;
if ($_SERVER["REQUEST_METHOD"] === "POST" && $request->has("email")) {
    $response = $controller->validateEmail($request);
}
if (!$response) {
    $response = $controller->emailPrompt($request);
}
(new Zend\Diactoros\Response\SapiEmitter())->emit($response);

?>