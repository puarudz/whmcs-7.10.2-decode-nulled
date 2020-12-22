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

class WhmcsComposerFactory extends \Composer\Factory
{
    protected function addLocalRepository(\Composer\IO\IOInterface $io, \Composer\Repository\RepositoryManager $rm, $vendorDir)
    {
        $rm->setRepositoryClass(WhmcsRepository::REPOSITORY_TYPE, "WHMCS\\Installer\\Composer\\WhmcsRepository");
        parent::addLocalRepository($io, $rm, $vendorDir);
    }
}

?>