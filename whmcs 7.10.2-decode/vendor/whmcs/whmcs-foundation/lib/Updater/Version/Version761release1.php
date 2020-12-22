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

class Version761release1 extends IncrementalVersion
{
    protected $updateActions = array("correctWhmcsWhoisToWhmcsDomains");
    protected function correctWhmcsWhoisToWhmcsDomains()
    {
        $query = \WHMCS\Database\Capsule::table("tblconfiguration")->where("setting", "domainLookupProvider");
        if (!$query->count()) {
            \WHMCS\Config\Setting::setValue("domainLookupProvider", "WhmcsDomains");
        } else {
            $settingNotConverted = \WHMCS\Database\Capsule::table("tblconfiguration")->where("setting", "domainLookupProvider")->whereIn("value", array("BasicWhois", "WhmcsWhois", ""))->where("updated_at", "<", "2018-06-28 00:00:00")->first();
            if ($settingNotConverted) {
                \WHMCS\Config\Setting::setValue("domainLookupProvider", "WhmcsDomains");
            }
        }
        return $this;
    }
}

?>