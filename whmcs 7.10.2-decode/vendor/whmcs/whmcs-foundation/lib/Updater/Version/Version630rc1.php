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

class Version630rc1 extends IncrementalVersion
{
    protected $updateActions = array("insertUpgradeTimeForMDE");
    public function insertUpgradeTimeForMDE()
    {
        \WHMCS\Config\Setting::setValue("MDEFromTime", \WHMCS\Carbon::now());
        return $this;
    }
}

?>