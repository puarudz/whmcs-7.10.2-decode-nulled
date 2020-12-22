<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\ApplicationSupport\View\Traits;

trait VersionTrait
{
    private $version = NULL;
    public function getFeatureVersion()
    {
        $version = $this->getVersion();
        return $version->getMajor() . "." . $version->getMinor();
    }
    public function getVersion()
    {
        if (!$this->version) {
            $app = \DI::make("app");
            $this->version = $app->getVersion();
        }
        return $this->version;
    }
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }
}

?>