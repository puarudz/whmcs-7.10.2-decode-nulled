<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\File\Provider;

interface StorageProviderInterface
{
    public static function getShortName();
    public static function getName();
    public function getConfigSummaryText();
    public function getConfigSummaryHtml();
    public function getIcon();
    public function applyConfiguration(array $configSettings);
    public function testConfiguration();
    public function exportConfiguration(\WHMCS\File\Configuration\StorageConfiguration $config);
    public function getConfigurationFields();
    public function getAccessCredentialFieldNames();
    public function getFieldsLockedInUse();
    public function isLocal();
    public function createFilesystemAdapterForAssetType($assetType, $subPath);
    public static function getExceptionErrorMessage(\Exception $e);
}

?>