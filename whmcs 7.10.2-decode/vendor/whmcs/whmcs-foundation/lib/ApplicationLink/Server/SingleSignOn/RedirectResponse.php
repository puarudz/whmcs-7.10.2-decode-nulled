<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ApplicationLink\Server\SingleSignOn;

class RedirectResponse extends \Symfony\Component\HttpFoundation\RedirectResponse
{
    protected $pathScopeMap = array("clientarea:homepage" => "/clientarea.php", "clientarea:profile" => "/clientarea.php?action=details", "clientarea:billing_info" => "/clientarea.php?action=creditcard", "clientarea:emails" => "/clientarea.php?action=emails", "clientarea:announcements" => "/index.php?rp=/announcements", "clientarea:downloads" => "/index.php?rp=/download", "clientarea:knowledgebase" => "/knowledgebase.php", "clientarea:network_status" => "/serverstatus.php", "clientarea:services" => "/clientarea.php?action=services", "clientarea:product_details" => "/clientarea.php?action=productdetails&id=:serviceId", "clientarea:domains" => "/clientarea.php?action=domains", "clientarea:domain_details" => "/clientarea.php?action=domaindetails&id=:domainId", "clientarea:invoices" => "/clientarea.php?action=invoices", "clientarea:tickets" => "/supporttickets.php", "clientarea:submit_ticket" => "/submitticket.php", "clientarea:shopping_cart" => "/cart.php", "clientarea:shopping_cart_addons" => "/cart.php?gid=addons", "clientarea:upgrade" => "/upgrade.php?type=package&id=:serviceId", "clientarea:shopping_cart_domain_register" => "/cart.php?a=add&domain=register", "clientarea:shopping_cart_domain_transfer" => "/cart.php?a=add&domain=transfer", "sso:custom_redirect" => "/:ssoRedirectPath");
    protected $scopesWithDynamicPaths = array("sso:custom_redirect" => array("ssoRedirectPath"), "clientarea:product_details" => array("serviceId"), "clientarea:domain_details" => array("domainId"), "clientarea:upgrade" => array("serviceId"));
    const DEFAULT_URL = "/clientarea.php";
    const DEFAULT_SCOPE = "clientarea:homepage";
    public function __construct($url = "", $status = 302, $headers = array())
    {
        if (empty($url)) {
            $url = static::DEFAULT_URL;
        }
        parent::__construct($url, $status, $headers);
    }
    public function setTargetUrlFromToken(\WHMCS\ApplicationLink\AccessToken $token)
    {
        $path = $this->getScopePath($token);
        $pathParts = explode("?", $path, 2);
        $systemUrl = \App::getSystemURL(false);
        if (!empty($pathParts[1])) {
            $url = \App::getRedirectUrl($pathParts[0], $pathParts[1], $systemUrl);
        } else {
            $url = \App::getRedirectUrl($path, "", $systemUrl);
        }
        parent::setTargetUrl($url);
        return $this;
    }
    public function getScopesWithDynamicPaths()
    {
        return $this->scopesWithDynamicPaths;
    }
    public function getScopePath(\WHMCS\ApplicationLink\AccessToken $token, $data = array())
    {
        $preMadeRedirect = $token->redirectUri;
        if ($preMadeRedirect) {
            return $preMadeRedirect;
        }
        $scopeForRedirect = $this->getScope($token);
        if (!$data && isset($this->scopesWithDynamicPaths[$scopeForRedirect])) {
            $neededVariables = $this->scopesWithDynamicPaths[$scopeForRedirect];
            foreach ($neededVariables as $holder) {
                if (in_array($holder, array("serviceId", "domainId"))) {
                    $data[$holder] = $token->client->serviceId;
                }
            }
        }
        return $this->fillPlaceHolders($this->getPathFromScope($scopeForRedirect), $data);
    }
    public function getScope(\WHMCS\ApplicationLink\AccessToken $token)
    {
        $scopeForRedirect = "";
        foreach ($token->scopes()->get() as $scope) {
            if ($scope->scope != "clientarea:sso") {
                $scopeForRedirect = $scope->scope;
                break;
            }
        }
        if (empty($scopeForRedirect)) {
            $scopeForRedirect = static::DEFAULT_SCOPE;
        }
        return $scopeForRedirect;
    }
    protected function getPathFromScope($scope)
    {
        $path = $this->pathScopeMap[static::DEFAULT_SCOPE];
        if (!empty($this->pathScopeMap[$scope])) {
            $path = $this->pathScopeMap[$scope];
        }
        return $path;
    }
    protected function fillPlaceHolders($path, $data = array())
    {
        $placeholders = $this->scopesWithDynamicPaths;
        foreach ($placeholders as $scope => $variables) {
            foreach ($variables as $variable) {
                $value = isset($data[$variable]) ? $data[$variable] : "";
                $value = sprintf("%s", $value);
                $path = str_replace(":" . $variable, $value, $path);
            }
        }
        return $path;
    }
}

?>