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

class Version770rc1 extends IncrementalVersion
{
    protected $updateActions = array("removeUnusedLegacyModules");
    private function getUnusedLegacyModules()
    {
        return array("gateways" => array("eeecurrency"), "servers" => array("lxadmin", "veportal", "xpanel"), "registrars" => array("ovh", "resellone", "dotdns"));
    }
    protected function removeUnusedLegacyModules()
    {
        (new \WHMCS\Module\LegacyModuleCleanup())->removeModulesIfInstalledAndUnused($this->getUnusedLegacyModules());
        return $this;
    }
}

?>