<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Installer\Composer;

class UpdateEnvironment
{
    public static function initEnvironment($updateTempDir)
    {
        $environmentErrors = array();
        if (empty($updateTempDir) || !is_dir($updateTempDir)) {
            $environmentErrors[] = \AdminLang::trans("update.missingUpdateTempDir");
        } else {
            if (!is_writable($updateTempDir)) {
                $environmentErrors[] = \AdminLang::trans("update.updateTempDirNotWritable");
            }
        }
        return $environmentErrors;
    }
}

?>