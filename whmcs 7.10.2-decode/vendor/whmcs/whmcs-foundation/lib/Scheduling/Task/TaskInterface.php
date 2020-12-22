<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Scheduling\Task;

interface TaskInterface
{
    const ACCESS_DEBUG = 256;
    const ACCESS_SYSTEM = 128;
    const ACCESS_HOOK = 64;
    const ACCESS_USER = 32;
    public function getName();
    public function setName($name);
    public function run();
    public function getOutputKeys();
    public function getLatestOutputs(array $outputKeys);
    public function getOutputsSince(\WHMCS\Carbon $since, array $outputKeys);
    public function getPriority();
    public function setPriority($priority);
    public function getDescription();
    public function setDescription($description);
    public function getFrequencyMinutes();
    public function setFrequencyMinutes($minutes);
    public function anticipatedNextRun(\WHMCS\Carbon $date);
    public function isEnabled();
    public function setEnabled($state);
    public function isPeriodic();
    public function setPeriodic($state);
    public function getStatus();
    public function getSystemName();
    public function getAccessLevel();
    public static function all();
    public static function register();
    public function output($key);
    public function isDailyTask();
    public function monthlyDayOfExecution();
    public function isSkipDailyCron();
    public function addSuccess(array $data);
    public function addFailure(array $data);
    public function getSuccesses();
    public function getFailures();
    public function addCustom($type, array $data);
    public function getCustom($type);
    public function getDetail();
}

?>