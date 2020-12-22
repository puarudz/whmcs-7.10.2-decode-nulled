<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Scheduling;

interface StatusInterface
{
    public function isInProgress();
    public function isDueNow();
    public function calculateAndSetNextDue();
    public function setNextDue(\WHMCS\Carbon $nextDue);
    public function setInProgress($state);
    public function getLastRuntime();
    public function setLastRuntime(\WHMCS\Carbon $date);
    public function getNextDue();
}

?>