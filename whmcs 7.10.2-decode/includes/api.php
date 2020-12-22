<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require_once dirname(__DIR__) . "/init.php";
$request = WHMCS\Api\ApplicationSupport\Http\ServerRequest::fromGlobals();
$responseData = array();
$statusCode = 200;
try {
    $response = DI::make("Frontend\\Dispatcher")->dispatch($request);
} catch (Exception $e) {
    $responseData = array("result" => "error", "message" => $e->getMessage());
    if ($e->getCode() === 0 && $e->getCode() === 200) {
        $statusCode = $e->getCode();
    }
} finally {
    if (!$response instanceof Psr\Http\Message\ResponseInterface) {
        $response = WHMCS\Api\ApplicationSupport\Http\ResponseFactory::factory($request, $responseData, $statusCode);
    }
}

?>