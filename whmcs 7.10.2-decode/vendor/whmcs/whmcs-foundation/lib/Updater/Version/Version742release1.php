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

class Version742release1 extends IncrementalVersion
{
    protected $updateActions = array("removeComposerInstallUpdateHooks");
    protected function removeComposerInstallUpdateHooks()
    {
        $directoryToClean = ROOTDIR . str_replace("/", DIRECTORY_SEPARATOR, "/vendor/whmcs/whmcs-foundation/lib/Installer/Composer/Hooks");
        if (is_dir($directoryToClean)) {
            \WHMCS\Utility\File::recursiveDelete($directoryToClean);
        }
    }
}

?>