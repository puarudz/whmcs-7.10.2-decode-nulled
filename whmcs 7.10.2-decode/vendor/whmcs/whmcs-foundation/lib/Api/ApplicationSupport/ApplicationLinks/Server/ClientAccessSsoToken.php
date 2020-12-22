<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Api\ApplicationSupport\ApplicationLinks\Server;

class ClientAccessSsoToken extends \WHMCS\ApplicationLink\Server\Server implements \WHMCS\ApplicationLink\Server\ApplicationLinkServerInterface
{
    use \WHMCS\Api\ApplicationSupport\ApplicationLinks\ClientUserTrait;
    public function getClientOtpGrant()
    {
        $clientOtpGrant = new \WHMCS\Api\ApplicationSupport\ApplicationLinks\GrantType\ClientOtp($this->getStorage("client_credentials"));
        $clientOtpGrant->setClientUser($this->getClientUser());
        return $clientOtpGrant;
    }
    public function handleTokenRequest(\OAuth2\RequestInterface $request, \OAuth2\ResponseInterface $response = NULL)
    {
        list($request, $response) = $this->preProcessHandleTokenRequest($request, $response);
        if (!$response->isOk()) {
            return $response;
        }
        parent::handleTokenRequest($request, $response);
        if (!$response->isOk()) {
            return $response;
        }
        $response = $this->postProcessHandleTokenRequest($response, $request);
        return $response;
    }
    private function findOrNewOauthClient($id)
    {
        $oauthClient = \WHMCS\ApplicationLink\Client::where("identifier", $id)->first();
        if (!$oauthClient) {
            $validScopes = \WHMCS\ApplicationLink\Scope::where("scope", "LIKE", "clientarea:%")->orWhere("scope", "LIKE", "sso:%")->pluck("scope")->all();
            $permittedClientAreaScopeNames = implode(" ", $validScopes);
            $client_secret = \WHMCS\ApplicationLink\Client::generateSecret();
            $rsa = \WHMCS\Security\Encryption\RsaKeyPair::factoryKeyPair();
            $rsa->description = "Provisioned for API client credential " . $id;
            $rsa->save();
            $rsaId = $rsa->id;
            $storage = $this->getStorage("client_credentials");
            $storage->setClientDetails($id, $client_secret, "", "single_sign_on", $permittedClientAreaScopeNames, "", 0, $rsaId, $name = "API Client OTP Access Token");
            $oauthClient = \WHMCS\ApplicationLink\Client::where("identifier", $id)->first();
        }
        return $oauthClient;
    }
    protected function getAuthenticatedClientId()
    {
        $uuid = "system";
        $currentAdminId = \WHMCS\Session::get("adminid");
        if ($currentAdminId) {
            $admin = \WHMCS\User\Admin::find($currentAdminId);
            if ($admin) {
                $uuid = $admin->uuid;
            }
        }
        return "api." . $uuid;
    }
    protected function preProcessHandleTokenRequest(\OAuth2\HttpFoundationBridge\Request $request, \OAuth2\HttpFoundationBridge\Response $response = NULL)
    {
        $client_id = $this->getAuthenticatedClientId();
        $oauthClient = $this->findOrNewOauthClient($client_id);
        $client_secret = $oauthClient->decryptedSecret;
        $clientOtpGrant = $this->getClientOtpGrant();
        $this->addGrantType($clientOtpGrant, "client_credentials");
        $request->setMethod(\OAuth2\HttpFoundationBridge\Request::METHOD_POST);
        $ssoScope = "clientarea:sso";
        $scope = $request->get("destination", $ssoScope);
        if (strpos($scope, $ssoScope) === false) {
            $scope .= " " . $ssoScope;
        }
        $request->request->remove("destination");
        $request->request->add(array("grant_type" => "single_sign_on", "client_id" => $client_id, "client_secret" => $client_secret, "scope" => $scope));
        return array($request, $response);
    }
    protected function postProcessHandleTokenRequest(\OAuth2\ResponseInterface $response, \OAuth2\HttpFoundationBridge\Request $request)
    {
        $data = json_decode($response->getContent(), true);
        $responseData = array("access_token" => "", "redirect_url" => "");
        if ($data && !empty($data["access_token"])) {
            $responseData["access_token"] = $data["access_token"];
            $responseData["redirect_url"] = \App::getSystemURL() . "oauth/singlesignon.php?" . "access_token=" . $data["access_token"];
            $response->setStatusCode(\OAuth2\HttpFoundationBridge\Response::HTTP_OK);
            $this->updateTokenForScope($request, $responseData["access_token"]);
        }
        $response->setData($responseData);
        return $response;
    }
    protected function updateTokenForScope(\OAuth2\HttpFoundationBridge\Request $request, $id)
    {
        $token = $this->findAccessToken($id);
        if ($token) {
            $pathBuilder = new \WHMCS\ApplicationLink\Server\SingleSignOn\RedirectResponse();
            $dynamicScopes = $pathBuilder->getScopesWithDynamicPaths();
            $scope = $pathBuilder->getScope($token);
            $dynamicScopePathVariablesToSet = isset($dynamicScopes[$scope]) ? $dynamicScopes[$scope] : null;
            if ($dynamicScopePathVariablesToSet) {
                $data = array();
                foreach ($dynamicScopePathVariablesToSet as $param) {
                    $value = $request->get(snake_case($param));
                    if ($value) {
                        $data[$param] = $value;
                    }
                }
                if ($data) {
                    $path = $pathBuilder->getScopePath($token, $data);
                    $token->redirectUri = $path;
                    $token->save();
                }
            }
        }
    }
}

?>