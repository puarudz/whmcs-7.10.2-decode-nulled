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

class Version790release1 extends IncrementalVersion
{
    protected $updateActions = array("pruneOrphanedSslOrders");
    public function pruneOrphanedSslOrders()
    {
        $orphanedSslOrderIds = \WHMCS\Database\Capsule::table("tblsslorders")->leftJoin("tblhostingaddons", "tblsslorders.addon_id", "=", "tblhostingaddons.id")->whereNull("tblhostingaddons.id")->where("tblsslorders.addon_id", "!=", 0)->pluck("tblsslorders.id");
        \WHMCS\Database\Capsule::table("tblsslorders")->whereIn("id", $orphanedSslOrderIds)->delete();
        return $this;
    }
}

?>