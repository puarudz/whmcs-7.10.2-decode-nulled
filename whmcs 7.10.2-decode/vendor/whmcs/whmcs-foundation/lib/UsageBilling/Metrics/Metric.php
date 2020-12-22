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

class Metric implements \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface
{
    private $type = "";
    private $systemName = "";
    private $displayName = NULL;
    private $units = NULL;
    private $usage = NULL;
    public function __construct($systemName, $displayName = NULL, $type = NULL, \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface $units = NULL, \WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $usage = NULL)
    {
        if (is_null($displayName)) {
            $displayName = $systemName;
        }
        if (is_null($type) || !in_array($type, $this->calculationTypes())) {
            $type = static::TYPE_SNAPSHOT;
        }
        if (is_null($units)) {
            $units = new Units\FloatingPoint("");
        }
        if (is_null($usage)) {
            $usage = new NoUsage(0);
        }
        $this->systemName = $systemName;
        $this->displayName = $displayName;
        $this->type = $type;
        $this->units = $units;
        $this->usage = $usage;
    }
    public function usage()
    {
        return $this->usage;
    }
    public function withUsage(\WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $usage = NULL)
    {
        return new static($this->systemName(), $this->displayName(), $this->type(), $this->units(), $usage);
    }
    public function units()
    {
        return $this->units;
    }
    public function systemName()
    {
        return $this->systemName;
    }
    public function displayName()
    {
        return $this->displayName;
    }
    public function type()
    {
        return $this->type;
    }
    private function calculationTypes()
    {
        return array(static::TYPE_SNAPSHOT, static::TYPE_PERIOD_DAY, static::TYPE_PERIOD_MONTH);
    }
}

?>