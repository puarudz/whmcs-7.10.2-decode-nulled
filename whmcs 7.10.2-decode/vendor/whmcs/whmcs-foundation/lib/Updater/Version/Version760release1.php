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

class Version760release1 extends IncrementalVersion
{
    protected $updateActions = array("removeUnusedLegacyModules", "storeCaptchaForms");
    private function getUnusedLegacyModules()
    {
        return array("gateways" => array("secpay"));
    }
    protected function removeUnusedLegacyModules()
    {
        (new \WHMCS\Module\LegacyModuleCleanup())->removeModulesIfInstalledAndUnused($this->getUnusedLegacyModules());
        $secPay = ROOTDIR . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "gateways" . DIRECTORY_SEPARATOR . "secpay.php";
        if (!file_exists($secPay)) {
            $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "xmlrpc.php";
        }
        return $this;
    }
    protected function storeCaptchaForms()
    {
        $captcha = new \WHMCS\Utility\Captcha();
        $captcha->setStoredFormSettings(\WHMCS\Utility\Captcha::getDefaultFormSettings());
        return $this;
    }
}

?>