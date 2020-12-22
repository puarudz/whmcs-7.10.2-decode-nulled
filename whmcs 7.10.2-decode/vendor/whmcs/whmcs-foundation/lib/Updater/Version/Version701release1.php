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

class Version701release1 extends IncrementalVersion
{
    protected $updateActions = array("removeAdminForceSSLSetting");
    public function removeAdminForceSSLSetting()
    {
        \WHMCS\Database\Capsule::table("tblconfiguration")->where("setting", "=", "AdminForceSSL")->delete();
        return $this;
    }
}

?>