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

class StorageProviderFactory
{
    private static $providerClasses = array("WHMCS\\File\\Provider\\LocalStorageProvider", "WHMCS\\File\\Provider\\S3StorageProvider");
    public static function getProviderClasses()
    {
        $providers = array();
        foreach (self::$providerClasses as $providerClass) {
            $providers[$providerClass::getShortName()] = $providerClass;
        }
        return $providers;
    }
    public static function createProvider($providerShortName)
    {
        $providers = self::getProviderClasses();
        if (array_key_exists($providerShortName, $providers)) {
            return new $providers[$providerShortName]();
        }
        return null;
    }
    public static function getLocalStoragePathsInUse()
    {
        $storagePaths = array();
        foreach (\WHMCS\File\Configuration\StorageConfiguration::local()->get() as $config) {
            $fileAssetSetting = \WHMCS\File\Configuration\FileAssetSetting::usingConfiguration($config->id)->first();
            if (!$fileAssetSetting) {
                continue;
            }
            $provider = $config->createStorageProvider();
            if ($provider instanceof LocalStorageProviderInterface) {
                $storagePaths[$fileAssetSetting->asset_type] = $provider->getLocalPath();
            }
        }
        return $storagePaths;
    }
    public static function getTopLevelLocalStoragePathsInUse()
    {
        $localStoragePaths = static::getLocalStoragePathsInUse();
        $uniqueTopLevelPaths = array();
        foreach ($localStoragePaths as $storagePath) {
            foreach ($uniqueTopLevelPaths as &$topLevelPath) {
                if (strpos($topLevelPath . DIRECTORY_SEPARATOR, $storagePath . DIRECTORY_SEPARATOR) === 0) {
                    $topLevelPath = $storagePath;
                    continue 2;
                }
                if (strpos($storagePath . DIRECTORY_SEPARATOR, $topLevelPath . DIRECTORY_SEPARATOR) === 0) {
                    continue 2;
                }
            }
            unset($topLevelPath);
            $uniqueTopLevelPaths[] = $storagePath;
        }
        return $uniqueTopLevelPaths;
    }
}

?>