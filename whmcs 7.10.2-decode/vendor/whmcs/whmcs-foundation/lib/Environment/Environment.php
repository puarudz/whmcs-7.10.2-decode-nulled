<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Environment;

final class Environment
{
    public static function toArray()
    {
        $report = array();
        if (!Php::isCli()) {
            $report["webServer"] = array("family" => WebServer::getServerFamily(), "version" => WebServer::getServerVersion(), "hasModRewrite" => WebServer::hasModRewrite());
        }
        $ioncubeLoaderVersion = Ioncube\Loader\LocalLoader::getVersion();
        $report = array_merge($report, array("controlPanel" => WebServer::getControlPanelInfo(), "install" => array("isTesting" => (bool) is_dir(ROOTDIR . DIRECTORY_SEPARATOR . "install2"), "hasRootHtaccess" => WebServer::hasRootHtaccess(), "hasAdminHtaccess" => WebServer::hasAdminHtaccess(), "autoUpdatePinChannel" => \WHMCS\Config\Setting::getValue("WHMCSUpdatePinVersion")), "php" => array("version" => Php::getVersion(), "extensions" => Php::getLoadedExtensions(), "memoryLimit" => Php::getPhpMemoryLimitInBytes(), "ioncubeLoaderVersion" => $ioncubeLoaderVersion ? $ioncubeLoaderVersion->getVersion() : null), "db" => DbEngine::getInfo(), "curl" => Curl::getInfo()));
        return $report;
    }
}

?>