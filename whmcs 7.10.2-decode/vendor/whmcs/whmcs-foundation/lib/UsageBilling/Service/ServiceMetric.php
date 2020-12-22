<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Service;

class ServiceMetric extends \WHMCS\UsageBilling\Metrics\Metric
{
    private $service = NULL;
    private $usageItem = NULL;
    private $historicUsage = NULL;
    private $tenantStatId = NULL;
    public function __construct(\WHMCS\Service\Service $service, $systemName, $displayName = NULL, $type = NULL, \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface $units = NULL, \WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $usage = NULL, \WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $historicUsage = NULL, \WHMCS\UsageBilling\Product\UsageItem $usageItem = NULL, $tenantStatId = NULL)
    {
        parent::__construct($systemName, $displayName, $type, $units, $usage);
        $this->service = $service;
        $this->usageItem = $usageItem;
        $this->historicUsage = $historicUsage;
        $this->tenantStatId = $tenantStatId;
    }
    public static function factoryFromMetric(\WHMCS\Service\Service $service, \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $metric, \WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $historicUsage = NULL, \WHMCS\UsageBilling\Product\UsageItem $usageItem = NULL, $tenantStatId = NULL)
    {
        return new static($service, $metric->systemName(), $metric->displayName(), $metric->type(), $metric->units(), $metric->usage(), $historicUsage, $usageItem, $tenantStatId);
    }
    public function withUsageItem(\WHMCS\UsageBilling\Product\UsageItem $usageItem)
    {
        return new static($this->service(), $this->systemName(), $this->displayName(), $this->type(), $this->units(), $this->usage(), $this->historicUsage(), $usageItem);
    }
    public function usageItem()
    {
        return $this->usageItem;
    }
    public function withUsage(\WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $usage = NULL, $tenantStatId = NULL)
    {
        return new static($this->service(), $this->systemName(), $this->displayName(), $this->type(), $this->units(), $usage, $this->historicUsage(), $this->usageItem(), $tenantStatId);
    }
    public function withHistoricUsage(\WHMCS\UsageBilling\Contracts\Metrics\UsageInterface $historicUsage = NULL)
    {
        return new static($this->service(), $this->systemName(), $this->displayName(), $this->type(), $this->units(), $this->usage(), $historicUsage, $this->usageItem, $this->tenantStatId());
    }
    public function historicUsage()
    {
        return $this->historicUsage;
    }
    public function service()
    {
        return $this->service;
    }
    public function isEnabled()
    {
        if ($this->usageItem()) {
            return !(bool) $this->usageItem()->isHidden;
        }
        return false;
    }
    public function tenantStatId()
    {
        return $this->tenantStatId;
    }
}

?>