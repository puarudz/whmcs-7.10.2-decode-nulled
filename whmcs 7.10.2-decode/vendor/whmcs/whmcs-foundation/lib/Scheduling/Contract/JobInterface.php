<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Scheduling\Contract;

interface JobInterface
{
    public function jobName($name);
    public function jobClassName($className);
    public function jobMethodName($methodName);
    public function jobMethodArguments($arguments);
    public function jobAvailableAt(\WHMCS\Carbon $date);
    public function jobDigestHash($hash);
}

?>