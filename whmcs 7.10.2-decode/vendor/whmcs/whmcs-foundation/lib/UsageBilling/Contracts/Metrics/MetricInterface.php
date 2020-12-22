<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Contracts\Metrics;

interface MetricInterface
{
    const TYPE_SNAPSHOT = "snapshot";
    const TYPE_PERIOD_DAY = "day";
    const TYPE_PERIOD_MONTH = "month";
    public function usage();
    public function withUsage(UsageInterface $usage);
    public function units();
    public function systemName();
    public function displayName();
    public function type();
}

?>