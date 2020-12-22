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

class Version700alpha1 extends IncrementalVersion
{
    public function __construct(\WHMCS\Version\SemanticVersion $version)
    {
        parent::__construct($version);
        $config = \DI::make("config");
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . ($config["customadminpath"] ?: "admin") . DIRECTORY_SEPARATOR . "browser.php";
    }
}

?>