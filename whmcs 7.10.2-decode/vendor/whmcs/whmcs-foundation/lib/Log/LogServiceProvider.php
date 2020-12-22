<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Log;

class LogServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider
{
    public function register()
    {
        $logService = $this;
        $this->app->singleton("log", function () use($logService) {
            return $logService->factoryDefaultChannelLogger();
        });
        $this->app->singleton("ActivityLog", function () {
            return new Activity();
        });
        $this->app->singleton("ApiLog", function () {
            return new \Monolog\Logger("WHMCS API", array(new \WHMCS\Api\Log\Handler()));
        });
        $this->importLogHandlers();
    }
    public function factoryDefaultChannelLogger()
    {
        return new \Monolog\Logger("WHMCS Application");
    }
    protected function importLogHandlers($baseDirectory = NULL)
    {
        if (is_null($baseDirectory)) {
            $baseDirectory = ROOTDIR;
        }
        $distributedFile = $baseDirectory . DIRECTORY_SEPARATOR . "dist.loghandler.php";
        $userFile = $baseDirectory . DIRECTORY_SEPARATOR . "loghandler.php";
        if (file_exists($userFile)) {
            include_once $userFile;
        } else {
            if (file_exists($distributedFile)) {
                include_once $distributedFile;
            }
        }
        return $this;
    }
}

?>