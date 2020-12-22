<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "bootstrap.php";
$server = DI::make("oauth2_server");
if (is_null($scope)) {
    $scope = $server->getScopeUtil()->getScopeFromRequest($request);
}
if ($server->verifyResourceRequest($request, $response)) {
    $token = $server->getAccessToken($request);
    $response->setData(array("success" => true, "message" => sprintf("Token '%s' is valid for scope(s) '%s'", $token->accessToken, $token->scope)));
}
Log::debug("oauth/resource", array("request" => array("headers" => $request->server->getHeaders(), "request" => $request->request->all(), "query" => $request->query->all()), "response" => array("body" => $response->getContent())));
$response->send();

?>