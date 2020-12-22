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

class Version760rc1 extends IncrementalVersion
{
    public function __construct(\WHMCS\Version\SemanticVersion $version)
    {
        parent::__construct($version);
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . ".phplint.foundation.yml";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . ".phplint.non-foundation.yml";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "package-lock.json";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "phpcs.ruleset.WHMCS_loose.xml";
        $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . "phpcs.ruleset.WHMCS_strict.xml";
    }
}

?>