<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Wizard;

class GettingStarted extends Wizard
{
    protected $wizardName = "GettingStarted";
    public function __construct()
    {
        $this->steps = array(array("name" => "Start", "hidden" => true), array("name" => "Settings", "stepName" => \AdminLang::trans("wizard.stepGeneral"), "stepDescription" => \AdminLang::trans("wizard.stepGeneralDesc")), array("name" => "Payments", "stepName" => \AdminLang::trans("wizard.stepPayments"), "stepDescription" => \AdminLang::trans("wizard.stepPaymentsDesc")), array("name" => "CreditCard", "stepName" => \AdminLang::trans("wizard.stepCreditCard"), "stepDescription" => \AdminLang::trans("wizard.stepCreditCardDesk"), "hidden" => true), array("name" => "Registrars", "stepName" => \AdminLang::trans("wizard.stepDomains"), "stepDescription" => \AdminLang::trans("wizard.stepDomainsDesc")), array("name" => "Enom", "stepName" => \AdminLang::trans("wizard.stepEnom"), "stepDescription" => \AdminLang::trans("wizard.stepEnomDesc"), "hidden" => true), array("name" => "Servers", "stepName" => \AdminLang::trans("wizard.stepWebHosting"), "stepDescription" => \AdminLang::trans("wizard.stepWebHostingDesc")), array("name" => "MarketConnect", "stepName" => \AdminLang::trans("wizard.stepAddonsExtras"), "stepDescription" => \AdminLang::trans("wizard.stepAddonsExtrasDescription")), array("name" => "Complete", "hidden" => true, "postSaveEvent" => function () {
            \WHMCS\Config\Setting::setValue("DisableSetupWizard", 1);
        }));
    }
    public function hasRequiredAdminPermissions()
    {
        return \WHMCS\User\Admin\Permission::currentAdminHasPermissionName("Configure General Settings");
    }
}

?>