<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$name = $whmcs->getFromRequest("name");
$description = $whmcs->getFromRequest("description");
$logoUri = $whmcs->getFromRequest("logoUri");
$redirectUri = $whmcs->getFromRequest("redirectUri");
$scope = $whmcs->getFromRequest("scope");
$grantType = $whmcs->getFromRequest("grantType");
$serviceId = (int) $whmcs->getFromRequest("serviceId");
$serviceObj = WHMCS\Service\Service::find($serviceId);
$clientObj = $serviceObj->client;
$validGrantTypes = array("authorization_code", "single_sign_on");
if (!trim($grantType)) {
    $apiresults = array("result" => "error", "message" => "A valid grant type is required.");
} else {
    if (!in_array($grantType, $validGrantTypes)) {
        $apiresults = array("result" => "error", "message" => "The requested grant type \"" . $grantType . "\" is invalid.");
    } else {
        if ($grantType == "authorization_code" && !trim($name)) {
            $apiresults = array("result" => "error", "message" => "A name for the Client Credential is required.");
        } else {
            if ($grantType == "single_sign_on") {
                if (!$serviceId) {
                    $apiresults = array("result" => "error", "message" => "A service ID is required for the single sign-on grant type.");
                    return NULL;
                }
                if (!$serviceObj) {
                    $apiresults = array("result" => "error", "message" => "A valid Service ID is required.");
                    return NULL;
                }
                if (!$clientObj) {
                    $apiresults = array("result" => "error", "message" => "Service ID not associated with valid Client.");
                    return NULL;
                }
            }
            if (!trim($scope)) {
                $apiresults = array("result" => "error", "message" => "At least one valid scope is required.");
            } else {
                $validScopes = WHMCS\ApplicationLink\Scope::pluck("scope")->all();
                $scopes = explode(" ", $scope);
                foreach ($scopes as $scopeToValidate) {
                    if (!in_array($scopeToValidate, $validScopes)) {
                        $apiresults = array("result" => "error", "message" => "The requested scope \"" . $scopeToValidate . "\" is invalid.");
                        return NULL;
                    }
                }
                $server = DI::make("oauth2_server");
                $storage = $server->getStorage("client_credentials");
                $clientIdentifier = WHMCS\ApplicationLink\Client::generateClientId();
                $secret = WHMCS\ApplicationLink\Client::generateSecret();
                $rsaId = 0;
                if ($grantType == "authorization_code") {
                    $rsa = WHMCS\Security\Encryption\RsaKeyPair::factoryKeyPair();
                    $rsa->description = "Provisioned for client credential " . $clientIdentifier;
                    $rsa->save();
                    $rsaId = $rsa->id;
                }
                $userUuid = $serviceId ? $clientObj->uuid : "";
                $storage->setClientDetails($clientIdentifier, $secret, $redirectUri, $grantType, $scope, $userUuid, $serviceId, $rsaId, $name, $description, $logoUri);
                $client = WHMCS\ApplicationLink\Client::whereIdentifier($clientIdentifier)->first();
                $apiresults = array("result" => "success", "credentialId" => $client->id, "clientIdentifier" => $client->identifier, "clientSecret" => $client->decryptedSecret);
            }
        }
    }
}

?>