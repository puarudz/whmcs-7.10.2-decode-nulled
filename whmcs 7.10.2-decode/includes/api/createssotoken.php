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
try {
    $httpRequest = OAuth2\HttpFoundationBridge\Request::createFromGlobals();
    $userClientId = (int) $httpRequest->get("client_id", 0);
    $userClient = WHMCS\User\Client::find($userClientId);
    if (!$userClient) {
        throw new WHMCS\Exception\Api\InvalidArgument("Invalid client_id");
    }
    $httpRequest->request->remove("client_id");
    $httpRequest->query->remove("client_id");
    $userContactId = (int) $httpRequest->get("contact_id", 0);
    if ($userContactId) {
        $contact = WHMCS\User\Client\Contact::find($userContactId);
        if (!$contact || $contact->clientId !== $userClientId) {
            throw new WHMCS\Exception\Api\InvalidArgument("Invalid contact_id");
        }
        $userClient = $contact;
    }
    $httpRequest->request->add(array("module" => "ClientAccessSsoToken", "module_type" => "api"));
    $httpRequest->headers->remove("PHP_AUTH_USER");
    $httpRequest->headers->remove("PHP_AUTH_PW");
    $clientOtpServer = DI::make("oauth2_sso", array("request" => $httpRequest));
    $clientOtpServer->setClientUser($userClient);
    $httpResponse = new OAuth2\HttpFoundationBridge\Response();
    $httpResponse->prepare($httpRequest);
    $httpResponse = $clientOtpServer->handleTokenRequest($httpRequest, $httpResponse);
    if (!$httpResponse->isOk()) {
        $msg = "";
        if ($httpResponse instanceof Symfony\Component\HttpFoundation\JsonResponse) {
            $details = json_decode($httpResponse->getContent(), true);
            if (!empty($details["error"])) {
                if ($details["error"] == "invalid_scope") {
                    $msg = "Invalid destination";
                } else {
                    $error = $details["error"];
                    if (!empty($details["error_description"])) {
                        $error .= ". " . $details["error_description"];
                    }
                    $msg = "Token could not be provisioned: " . $error;
                }
            }
        }
        if (!$msg) {
            $msg = "Token could not be provisioned";
        }
        throw new WHMCS\Exception($msg);
    }
    $data = json_decode($httpResponse->getContent(), true);
    if (!$data || !is_array($data)) {
        throw new WHMCS\Exception("Unexpected internal structure");
    }
    $apiresults = array("result" => "success", "access_token" => $data["access_token"], "redirect_url" => $data["redirect_url"]);
} catch (Exception $e) {
    $apiresults = array("result" => "error", "message" => $e->getMessage());
}

?>