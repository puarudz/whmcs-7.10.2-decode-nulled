<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Smarty;

class SmartyServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider
{
    public function register()
    {
        $this->app->bind("WHMCS\\Smarty\\Security\\Policy", function (\WHMCS\Container $app, $parameters = array()) {
            $smarty = array_shift($parameters);
            $policyName = !empty($parameters[0]) ? $parameters[0] : "system";
            $userPolicySettings = $this->getUserPolicySettings($policyName);
            $policyClassName = $this->getPolicyClassName($policyName);
            $policySettings = new $policyClassName($userPolicySettings);
            $policy = new Security\Policy($smarty, $policySettings);
            return $policy;
        });
    }
    protected function getUserPolicySettings($policyName)
    {
        $config = \Config::self();
        if (isset($config["smarty_security_policy"][$policyName])) {
            $userPolicySettings = $config["smarty_security_policy"][$policyName];
        } else {
            $userPolicySettings = array();
        }
        return $userPolicySettings;
    }
    protected function getPolicyClassName($policyName)
    {
        $policyName = ucfirst($policyName);
        $PolicyClassNamespace = "WHMCS\\Smarty\\Security\\Settings";
        $policyClassName = sprintf("%s\\%sPolicy", $PolicyClassNamespace, $policyName);
        if (!class_exists($policyClassName)) {
            $policyClassName = $policyClassName = sprintf("%s\\%sPolicy", $PolicyClassNamespace, "System");
        }
        return $policyClassName;
    }
}

?>