<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Notification;

class VersionFeatureHighlights
{
    protected $version = NULL;
    protected $incrementalVersion = NULL;
    const FEATURE_HIGHLIGHT_VERSION = "7.10.0-alpha.1";
    public function __construct($featureVersion = self::FEATURE_HIGHLIGHT_VERSION, \WHMCS\Updater\Version\IncrementalVersion $incrementalVersion = NULL)
    {
        $this->version = $featureVersion;
        if (is_null($incrementalVersion)) {
            $this->incrementalVersion = \WHMCS\Updater\Version\IncrementalVersion::factory($this->version);
        } else {
            $this->incrementalVersion = $incrementalVersion;
        }
        return $this;
    }
    public function getFeatureHighlights()
    {
        $highlights = $this->incrementalVersion->getFeatureHighlights();
        if (empty($highlights)) {
            throw new \WHMCS\Exception("No highlights returned for: " . $this->version);
        }
        return $highlights;
    }
}

?>