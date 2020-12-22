<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Plesk_Utils
{
    public static function getAccountsCount($userId)
    {
        $hostingAccounts = WHMCS\Database\Capsule::table("tblhosting")->join("tblservers", "tblservers.id", "=", "tblhosting.server")->where("tblhosting.userid", $userId)->where("tblservers.type", "plesk")->whereIn("tblhosting.domainstatus", array("Active", "Suspended", "Pending"))->count();
        $hostingAddonAccounts = WHMCS\Database\Capsule::table("tblhostingaddons")->join("tblservers", "tblhostingaddons.server", "=", "tblservers.id")->where("tblhostingaddons.userid", $userId)->where("tblservers.type", "plesk")->whereIn("status", array("Active", "Suspended", "Pending"))->count();
        return $hostingAccounts + $hostingAddonAccounts;
    }
}

?>