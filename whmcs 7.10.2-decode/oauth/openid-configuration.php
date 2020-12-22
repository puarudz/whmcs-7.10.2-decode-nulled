<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "init.php";
$response = new Symfony\Component\HttpFoundation\JsonResponse();
$content = "";
$cacheKey = "OIDC-Discovery-Document";
$cache = new WHMCS\TransientData();
if ($cachedDiscovery = $cache->retrieve($cacheKey)) {
    $content = $cachedDiscovery;
} else {
    $server = DI::make("oauth2_server");
    $content = jsonPrettyPrint($server->getDiscoveryDocument());
    $cache->store($cacheKey, $content, 60);
}
$response->setContent($content);
$response->send();

?>