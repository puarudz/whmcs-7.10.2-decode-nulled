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

class Version7102release1 extends IncrementalVersion
{
    protected $updateActions = array("checkForInvalidFileStorageLocation", "ensure2CheckOutDisabled");
    protected function checkForInvalidFileStorageLocation()
    {
        $settingsToFix = array(\WHMCS\File\FileAsset::TYPE_KB_IMAGES, \WHMCS\File\FileAsset::TYPE_EMAIL_IMAGES);
        foreach ($settingsToFix as $setting) {
            $existingSetting = \WHMCS\File\Configuration\FileAssetSetting::forAssetType($setting)->first();
            switch ($setting) {
                case \WHMCS\File\FileAsset::TYPE_EMAIL_IMAGES:
                    $existingConfigs = array(\WHMCS\File\FileAsset::TYPE_EMAIL_TEMPLATE_ATTACHMENTS, \WHMCS\File\FileAsset::TYPE_DOWNLOADS, \WHMCS\File\FileAsset::TYPE_TICKET_ATTACHMENTS, \WHMCS\File\FileAsset::TYPE_CLIENT_FILES, \WHMCS\File\FileAsset::TYPE_EMAIL_ATTACHMENTS);
                    break;
                default:
                    $existingConfigs = array(\WHMCS\File\FileAsset::TYPE_DOWNLOADS, \WHMCS\File\FileAsset::TYPE_TICKET_ATTACHMENTS, \WHMCS\File\FileAsset::TYPE_CLIENT_FILES, \WHMCS\File\FileAsset::TYPE_EMAIL_ATTACHMENTS, \WHMCS\File\FileAsset::TYPE_EMAIL_TEMPLATE_ATTACHMENTS);
                    break;
            }
            if (!$existingSetting) {
                $existingConfigSetting = null;
                foreach ($existingConfigs as $existingConfig) {
                    $existingConfigSetting = \WHMCS\File\Configuration\FileAssetSetting::where("asset_type", $existingConfig)->first();
                    if ($existingConfigSetting) {
                        break;
                    }
                }
                $fileAssetSetting = new \WHMCS\File\Configuration\FileAssetSetting();
                $fileAssetSetting->asset_type = $setting;
                $fileAssetSetting->storageconfiguration_id = $existingConfigSetting->configuration->id;
                $fileAssetSetting->migratetoconfiguration_id = null;
                $fileAssetSetting->save();
            } else {
                $storageConfiguration = \WHMCS\File\Configuration\StorageConfiguration::find($existingSetting->storageconfiguration_id);
                if (is_null($storageConfiguration)) {
                    $existingConfigSetting = null;
                    foreach ($existingConfigs as $existingConfig) {
                        $existingConfigSetting = \WHMCS\File\Configuration\FileAssetSetting::where("asset_type", $existingConfig)->first();
                        if ($existingConfigSetting) {
                            break;
                        }
                    }
                    $existingSetting->storageconfiguration_id = $existingConfigSetting->configuration->id;
                    $existingSetting->save();
                }
            }
        }
        return $this;
    }
    protected function ensure2CheckOutDisabled()
    {
        $isGatewayActive = \WHMCS\Database\Capsule::table("tblpaymentgateways")->where("gateway", "=", "tco")->whereNotIn("setting", array("recurringBilling", "integrationMethod"))->first();
        if (!$isGatewayActive) {
            \WHMCS\Database\Capsule::table("tblpaymentgateways")->where("gateway", "=", "tco")->whereIn("setting", array("recurringBilling", "integrationMethod"))->delete();
        }
        return $this;
    }
}

?>