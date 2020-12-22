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

class CallbackUsage extends AbstractProvider
{
    private $usageCallable = NULL;
    private $tenantUsageCallable = NULL;
    public function __construct(array $items = array(), $usageCallable = NULL, $tenantUsageCallable = NULL)
    {
        parent::__construct($items);
        if (is_callable($usageCallable)) {
            $this->usageCallable = $usageCallable;
        }
        if (is_callable($tenantUsageCallable)) {
            $this->tenantUsageCallable = $tenantUsageCallable;
        }
    }
    public function tenantUsage($tenant)
    {
        $cache = $this->getStorage();
        if (isset($cache[$tenant])) {
            return $cache[$tenant];
        }
        if ($this->tenantUsageCallable) {
            $usage = call_user_func($this->tenantUsageCallable, $tenant, $this);
            if (is_array($usage)) {
                $cache[$tenant] = $usage;
                $this->setStorage($cache);
                return $usage;
            }
        } else {
            $cache = $this->usage(false);
            if (isset($cache[$tenant])) {
                return $cache[$tenant];
            }
        }
        return null;
    }
    public function usage($useCache = true)
    {
        if ($useCache) {
            $cache = $this->getStorage();
            if (count($cache)) {
                return $cache;
            }
        }
        if ($this->usageCallable) {
            $usage = call_user_func($this->usageCallable, $this);
            if (is_array($usage)) {
                $this->setStorage($usage);
                return $usage;
            }
        }
        return array();
    }
}

?>