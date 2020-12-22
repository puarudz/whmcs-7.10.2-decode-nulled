<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Product;

class Server extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblservers";
    public $timestamps = false;
    protected $columnMap = array("ipAddress" => "ipaddress", "assignedIpAddresses" => "assignedips", "monthlyCost" => "monthlycost", "dataCenter" => "noc", "statusAddress" => "statusaddress", "nameserverOne" => "nameserver1", "nameserverOneIpAddress" => "nameserver1ip", "nameserverTwo" => "nameserver2", "nameserverTwoIpAddress" => "nameserver2ip", "nameserverThree" => "nameserver3", "nameserverThreeIpAddress" => "nameserver3ip", "nameserverFour" => "nameserver4", "nameserverFourIpAddress" => "nameserver4ip", "nameserverFive" => "nameserver5", "nameserverFiveIpAddress" => "nameserver5ip", "maxAccounts" => "maxaccounts", "accessHash" => "accesshash");
    protected $appends = array("activeAccountsCount");
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("ordered", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->orderBy("tblservers.name");
        });
        static::deleted(function (Server $server) {
            Server\Remote::where("server_id", $server->id)->delete();
        });
    }
    public function services()
    {
        return $this->hasMany("\\WHMCS\\Service\\Service", "server");
    }
    public function addons()
    {
        return $this->hasMany("\\WHMCS\\Service\\Addon", "server");
    }
    public function scopeOfModule(\Illuminate\Database\Eloquent\Builder $query, $module)
    {
        return $query->where("type", $module);
    }
    public function scopeEnabled(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where("disabled", 0);
    }
    public function scopeDefault(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where("active", 1);
    }
    public function getActiveAccountsCountAttribute()
    {
        $activeStatuses = array("Active", "Suspended");
        return $this->services()->whereIn("domainstatus", $activeStatuses)->count() + $this->addons()->whereIn("status", $activeStatuses)->count();
    }
    public function getModuleInterface()
    {
        $moduleInterface = new \WHMCS\Module\Server();
        $moduleInterface->load($this->type);
        return $moduleInterface;
    }
    public function remote()
    {
        return $this->hasOne("WHMCS\\Product\\Server\\Remote");
    }
    public function usageTenants()
    {
        return $this->hasMany("WHMCS\\UsageBilling\\Metrics\\Server\\Tenant");
    }
    public function usageTenant($tenant)
    {
        return $this->usageTenants()->where("tenant", $tenant)->first();
    }
    public function usageTenantByService(\WHMCS\Service\Service $service)
    {
        $module = $this->getModuleInterface();
        $field = $module->getMetaDataValue("ListAccountsUniqueIdentifierField");
        $tenant = $service->getUniqueIdentifierValue($field);
        if ($tenant) {
            return $this->usageTenant($tenant);
        }
        return null;
    }
    public function getMetricProvider()
    {
        $module = $this->getModuleInterface();
        if ($module->functionExists("MetricProvider")) {
            $params = $module->getServerParams($this);
            $provider = $module->call("MetricProvider", $params);
            if ($provider instanceof \WHMCS\UsageBilling\Contracts\Metrics\ProviderInterface) {
                return $provider;
            }
        }
        return null;
    }
    public function syncTenantUsage($tenantName)
    {
        $provider = $this->getMetricProvider();
        if ($provider) {
            $metrics = $provider->tenantUsage($tenantName);
            if (!empty($metrics)) {
                $tenant = \WHMCS\UsageBilling\Metrics\Server\Tenant::firstOrCreate(array("server_id" => $this->id, "tenant" => $tenantName));
                $tenant->createStats($metrics);
                return $metrics;
            }
        }
        return array();
    }
    public function syncAllUsage()
    {
        $provider = $this->getMetricProvider();
        if ($provider) {
            $usage = $provider->usage();
            foreach ($usage as $tenantName => $metrics) {
                $tenant = \WHMCS\UsageBilling\Metrics\Server\Tenant::firstOrCreate(array("server_id" => $this->id, "tenant" => $tenantName));
                $tenant->createStats($metrics);
            }
            return $usage;
        } else {
            return array();
        }
    }
}

?>