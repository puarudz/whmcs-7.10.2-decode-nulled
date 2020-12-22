<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Backups;

class Backups
{
    public function getActiveProviders()
    {
        $activeBackupSystems = \WHMCS\Config\Setting::getValue("ActiveBackupSystems");
        $activeBackupSystems = explode(",", $activeBackupSystems);
        $activeBackupSystems = array_filter($activeBackupSystems);
        return $activeBackupSystems;
    }
}

?>