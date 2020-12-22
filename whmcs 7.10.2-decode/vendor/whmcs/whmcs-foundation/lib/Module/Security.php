<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module;

class Security extends AbstractModule
{
    protected $type = self::TYPE_SECURITY;
    public function getActiveModules()
    {
        return (new \WHMCS\TwoFactorAuthentication())->getAvailableModules();
    }
    public function getAdminActivationForms($moduleName)
    {
        return array((new \WHMCS\View\Form())->setUriPrefixAdminBaseUrl("configtwofa.php")->setMethod(\WHMCS\View\Form::METHOD_GET)->setParameters(array("module" => $moduleName))->setSubmitLabel(\AdminLang::trans("global.activate")));
    }
    public function getAdminManagementForms($moduleName)
    {
        return array((new \WHMCS\View\Form())->setUriPrefixAdminBaseUrl("configtwofa.php")->setMethod(\WHMCS\View\Form::METHOD_GET)->setParameters(array("module" => $moduleName))->setSubmitLabel(\AdminLang::trans("global.manage")));
    }
}

?>