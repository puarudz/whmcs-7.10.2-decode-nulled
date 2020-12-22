<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin;

class AdminServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider
{
    public function register()
    {
        if (!defined("ADMINAREA")) {
            define("ADMINAREA", true);
        }
        if (!function_exists("checkPermission")) {
            gracefulCoreRequiredFileInclude("/includes/adminfunctions.php");
        }
    }
    public static function getAdminRouteBase()
    {
        $adminDirectoryName = \App::get_admin_folder_name();
        if (substr($adminDirectoryName, 0, 1) != "/") {
            $adminDirectoryName = "/" . $adminDirectoryName;
        }
        if (substr($adminDirectoryName, -1) == "/") {
            $adminDirectoryName = substr($adminDirectoryName, 0, -1);
        }
        return $adminDirectoryName;
    }
    public static function hasDefaultAdminDirectory()
    {
        return is_dir(ROOTDIR . DIRECTORY_SEPARATOR . \WHMCS\Config\Application::DEFAULT_ADMIN_FOLDER);
    }
    public static function hasConfiguredCustomAdminPath()
    {
        $adminPath = \DI::make("config")->customadminpath;
        if (!$adminPath) {
            return false;
        }
        return $adminPath != \WHMCS\Config\Application::DEFAULT_ADMIN_FOLDER;
    }
}

?>