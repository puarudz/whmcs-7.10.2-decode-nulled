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

class Version700rc1 extends IncrementalVersion
{
    protected $updateActions = array("setDefaultUpdateDetails", "setDefaultDailyCronInvocationHour");
    public function setDefaultUpdateDetails()
    {
        \WHMCS\Config\Setting::setValue("UpdaterLatestVersion", \WHMCS\Application::FILES_VERSION);
        \WHMCS\Config\Setting::setValue("UpdaterLatestBetaVersion", \WHMCS\Application::FILES_VERSION);
        \WHMCS\Config\Setting::setValue("UpdaterLatestStableVersion", \WHMCS\Application::FILES_VERSION);
        \WHMCS\Config\Setting::setValue("UpdaterLatestSupportAndUpdatesVersion", \WHMCS\Application::FILES_VERSION);
        return $this;
    }
    public function setDefaultDailyCronInvocationHour()
    {
        \WHMCS\Cron::setDailyCronExecutionHour();
    }
}

?>