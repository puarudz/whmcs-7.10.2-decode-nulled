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

class Version700beta1 extends IncrementalVersion
{
    protected $updateActions = array("removeLegacyClassLocations");
    public function removeLegacyClassLocations()
    {
        $legacyClassesDir = ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR;
        $dirsToRemove = array($legacyClassesDir . "WHMCS", $legacyClassesDir . "phlyLabs");
        foreach ($dirsToRemove as $dir) {
            if (is_dir($dir)) {
                try {
                    \WHMCS\Utility\File::recursiveDelete($dir);
                } catch (\Exception $e) {
                }
            }
        }
        return $this;
    }
}

?>