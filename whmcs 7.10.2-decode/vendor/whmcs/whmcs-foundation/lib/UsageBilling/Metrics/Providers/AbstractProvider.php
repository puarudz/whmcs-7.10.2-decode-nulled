<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Metrics\Providers;

abstract class AbstractProvider implements \WHMCS\UsageBilling\Contracts\Metrics\ProviderInterface
{
    protected $storage = array();
    protected $metrics = NULL;
    public abstract function usage();
    public abstract function tenantUsage($tenant);
    public function __construct(array $metrics = array())
    {
        $data = array();
        foreach ($metrics as $v) {
            if ($v instanceof \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface) {
                $data[$v->systemName()] = $v;
            }
        }
        $this->metrics = $data;
    }
    public function metrics()
    {
        return $this->metrics;
    }
    protected function getStorage()
    {
        return $this->storage;
    }
    protected function setStorage(array $storage)
    {
        $this->storage = $storage;
        return $this;
    }
}

?>