<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Metrics;

class NoUsage implements \WHMCS\UsageBilling\Contracts\Metrics\UsageStubInterface
{
    private $now = NULL;
    public function __construct()
    {
        $this->now = \WHMCS\Carbon::now();
    }
    public function collectedAt()
    {
        return $this->now->copy();
    }
    public function startAt()
    {
        return $this->now->copy();
    }
    public function endAt()
    {
        return $this->now->copy();
    }
    public function value()
    {
        return 0;
    }
}

?>