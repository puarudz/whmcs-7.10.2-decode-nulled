<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Markup\Error;

trait ErrorLevelTrait
{
    protected $errorLevel = ErrorLevelInterface::ERROR;
    public function isAnError()
    {
        return ErrorLevelInterface::ERROR <= $this->errorLevel;
    }
    public function errorName()
    {
        return ucfirst(strtolower(\Monolog\Logger::getLevelName($this->errorLevel)));
    }
}

?>