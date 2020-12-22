<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version700alpha5 extends IncrementalVersion
{
    protected $updateActions = array("updateEnomDomainLookupProviderSetting", "removeToggleInfoPopup", "disableSetupWizardForUpgrades");
    public function updateEnomDomainLookupProviderSetting()
    {
        if (strtolower(\WHMCS\Config\Setting::getValue("domainLookupProvider")) == "enom") {
            \WHMCS\Config\Setting::setValue("domainLookupProvider", "Registrar");
            \WHMCS\Config\Setting::setValue("domainLookupRegistrar", "enom");
        }
    }
    public function removeToggleInfoPopup()
    {
        $setting = \WHMCS\Config\Setting::find("ToggleInfoPopup");
        if ($setting) {
            $setting->delete();
        }
    }
    public function disableSetupWizardForUpgrades()
    {
        $companyName = \WHMCS\Config\Setting::getValue("CompanyName");
        $email = \WHMCS\Config\Setting::getValue("Email");
        $domain = \WHMCS\Config\Setting::getValue("Domain");
        $systemFrom = \WHMCS\Config\Setting::getValue("SystemEmailsFromEmail");
        if ($companyName != "Company Name" || $email != "changeme@changeme.com" || $domain != "http://www.yourdomain.com/" || $systemFrom != "noreply@yourdomain.com") {
            \WHMCS\Config\Setting::setValue("DisableSetupWizard", 1);
        }
    }
}

?>