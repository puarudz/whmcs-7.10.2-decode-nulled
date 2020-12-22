<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Service;

class Service extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblhosting";
    protected $columnMap = array("clientId" => "userid", "productId" => "packageid", "serverId" => "server", "registrationDate" => "regdate", "paymentGateway" => "paymentmethod", "status" => "domainstatus", "promotionId" => "promoid", "promotionCount" => "promocount", "overrideAutoSuspend" => "overideautosuspend", "overrideSuspendUntilDate" => "overidesuspenduntil", "bandwidthUsage" => "bwusage", "bandwidthLimit" => "bwlimit", "lastUpdateDate" => "lastupdate", "firstPaymentAmount" => "firstpaymentamount", "recurringAmount" => "amount", "recurringFee" => "amount");
    protected $dates = array("registrationDate", "overrideSuspendUntilDate", "lastUpdateDate");
    protected $booleans = array("overideautosuspend");
    protected $appends = array("serviceProperties");
    protected $hidden = array("password");
    const STATUS_PENDING = \WHMCS\Utility\Status::PENDING;
    const STATUS_ACTIVE = \WHMCS\Utility\Status::ACTIVE;
    const STATUS_SUSPENDED = \WHMCS\Utility\Status::SUSPENDED;
    public function scopeUserId($query, $userId)
    {
        return $query->where("userid", "=", $userId);
    }
    public function scopeActive($query)
    {
        return $query->where("domainstatus", self::STATUS_ACTIVE);
    }
    public function scopeMarketConnect($query)
    {
        $marketConnectProductIds = \WHMCS\Product\Product::marketConnect()->pluck("id");
        return $query->whereIn("packageid", $marketConnectProductIds);
    }
    public function scopeIsConsideredActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->whereIn("domainstatus", array(Service::STATUS_ACTIVE, Service::STATUS_SUSPENDED));
    }
    public function scopeIsNotRecurring(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->whereIn("billingcycle", array("Free", "Free Account", "One Time"));
    }
    public function isRecurring()
    {
        return !in_array($this->billingcycle, array(\WHMCS\Billing\Cycles::DISPLAY_FREE, \WHMCS\Billing\Cycles::DISPLAY_ONETIME));
    }
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public function product()
    {
        return $this->belongsTo("WHMCS\\Product\\Product", "packageid");
    }
    public function paymentGateway()
    {
        return $this->hasMany("WHMCS\\Billing\\Gateway", "gateway", "paymentmethod");
    }
    public function addons()
    {
        return $this->hasMany("WHMCS\\Service\\Addon", "hostingid");
    }
    public function order()
    {
        return $this->belongsTo("WHMCS\\Order\\Order", "orderid");
    }
    public function promotion()
    {
        return $this->hasMany("WHMCS\\Product\\Promotion", "id", "promoid");
    }
    public function cancellationRequests()
    {
        return $this->hasMany("WHMCS\\Service\\CancellationRequest", "relid");
    }
    public function ssl()
    {
        return $this->hasMany("WHMCS\\Service\\Ssl", "serviceid")->where("addon_id", "=", 0);
    }
    public function hasAvailableUpgrades()
    {
        return 0 < $this->product->upgradeProducts->count();
    }
    public function failedActions()
    {
        return $this->hasMany("WHMCS\\Module\\Queue", "service_id")->where("service_type", "=", "service");
    }
    public function customFieldValues()
    {
        return $this->hasMany("WHMCS\\CustomField\\CustomFieldValue", "relid");
    }
    protected function getCustomFieldType()
    {
        return "product";
    }
    protected function getCustomFieldRelId()
    {
        return $this->product->id;
    }
    public function getServicePropertiesAttribute()
    {
        return new Properties($this);
    }
    public function canBeUpgraded()
    {
        return $this->status == "Active";
    }
    public function isService()
    {
        return true;
    }
    public function isAddon()
    {
        return false;
    }
    public function serverModel()
    {
        return $this->belongsTo("WHMCS\\Product\\Server", "server");
    }
    public function legacyProvision()
    {
        try {
            if (!function_exists("ModuleCallFunction")) {
                require_once ROOTDIR . "/includes/modulefunctions.php";
            }
            return ModuleCallFunction("Create", $this->id);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function getMetricProvider()
    {
        $server = $this->serverModel;
        if ($server) {
            $metricProvider = $server->getMetricProvider();
            if ($metricProvider) {
                return $metricProvider;
            }
        }
        return null;
    }
    public function metrics($onlyBilledMetrics = false, $mode = NULL)
    {
        if (is_null($mode)) {
            $mode = \WHMCS\UsageBilling\Invoice\ServiceUsage::getQuickViewMode();
        }
        $serviceMetrics = array();
        $metricProvider = $this->getMetricProvider();
        if (!$metricProvider) {
            return $serviceMetrics;
        }
        $product = $this->product;
        $storedProductUsageItems = array();
        foreach ($product->metrics as $usageItem) {
            $storedProductUsageItems[$usageItem->metric] = $usageItem;
        }
        $usageTenant = $this->serverModel->usageTenantByService($this);
        foreach ($metricProvider->metrics() as $metric) {
            $currentUsage = null;
            $usageItem = null;
            $totalHistoricUsage = null;
            $totalHistoricSum = 0;
            $historicUsageByPeriod = array();
            $currentTenantStatId = null;
            if (isset($storedProductUsageItems[$metric->systemName()])) {
                $usageItem = $storedProductUsageItems[$metric->systemName()];
            }
            if ($onlyBilledMetrics && $usageItem->isHidden) {
                continue;
            }
            if ($usageTenant) {
                $stat = new \WHMCS\UsageBilling\Metrics\Server\Stat();
                if ($metric->type() == \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_PERIOD_MONTH) {
                    $startOfMetricPeriod = \WHMCS\Carbon::now()->startOfMonth();
                    $currentPeriodSum = 0;
                    $currentLastUpdated = \WHMCS\Carbon::now();
                    $currentPeriodStat = $stat->unbilledFirstAfter($startOfMetricPeriod, $usageTenant, $metric);
                    if ($currentPeriodStat) {
                        $currentLastUpdated = \WHMCS\Carbon::createFromTimestamp($currentPeriodStat->measuredAt);
                        $currentPeriodSum = $currentPeriodStat->value;
                        $currentTenantStatId = $currentPeriodStat->id;
                    }
                    $metric = $metric->withUsage(new \WHMCS\UsageBilling\Metrics\Usage($currentPeriodSum, $currentLastUpdated, $startOfMetricPeriod->copy(), $startOfMetricPeriod->copy()->endOfMonthMicro()));
                    $previousMonthlyMetricPeriod = $startOfMetricPeriod->copy()->subMonth();
                    $historicUsageEnd = $previousMonthlyMetricPeriod->copy()->endOfMonthMicro();
                    $historicUsageStart = $previousMonthlyMetricPeriod->copy()->startOfMonthMicro();
                    $previousStats = $stat->unbilledQueryBefore($startOfMetricPeriod, $usageTenant, $metric)->get();
                    foreach ($previousStats as $previous) {
                        $measured = \WHMCS\Carbon::createFromTimestamp($previous->measuredAt);
                        $start = $measured->copy()->startOfMonthMicro();
                        $end = $measured->copy()->endOfMonthMicro();
                        if ($start < $historicUsageStart) {
                            $historicUsageStart = $start;
                        }
                        if ($historicUsageEnd < $end) {
                            $historicUsageEnd = $end;
                        }
                        $historicUsageByPeriod[$previous->id] = new \WHMCS\UsageBilling\Metrics\Usage($previous->value, $measured->copy(), $start, $end);
                        $totalHistoricSum += $previous->value;
                    }
                    $totalHistoricUsage = new \WHMCS\UsageBilling\Metrics\Usage($totalHistoricSum, $historicUsageEnd, $historicUsageStart, $historicUsageEnd);
                } else {
                    if ($metric->type() == \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_SNAPSHOT) {
                        $currentValue = 0;
                        $currentLastUpdated = null;
                        $currentPeriodStat = $stat->unbilledValueFirst($usageTenant, $metric);
                        if ($currentPeriodStat) {
                            $currentLastUpdated = \WHMCS\Carbon::createFromTimestamp($currentPeriodStat->measuredAt);
                            $currentValue = $currentPeriodStat->value;
                            $currentTenantStatId = $currentPeriodStat->id;
                        }
                        $nextinvoicedate = $this->nextInvoiceDate;
                        if ($nextinvoicedate != "0000-00-00") {
                            $nextinvoicedate = \WHMCS\Carbon::createFromFormat("Y-m-d", $nextinvoicedate);
                        } else {
                            $nextinvoicedate = \WHMCS\Carbon::now();
                        }
                        $nextinvoicedate->startOfDay();
                        $periodStart = $nextinvoicedate->copy()->subMonthNoOverflow();
                        if (!is_null($currentLastUpdated)) {
                            $metric = $metric->withUsage(new \WHMCS\UsageBilling\Metrics\Usage($currentValue, $currentLastUpdated, $periodStart, $nextinvoicedate));
                        } else {
                            $usage = new \WHMCS\UsageBilling\Metrics\NoUsage();
                            $metric = $metric->withUsage($usage);
                        }
                    }
                }
            }
            if (\WHMCS\UsageBilling\Invoice\ServiceUsage::isMultiHistory($mode) && $historicUsageByPeriod) {
                if (\WHMCS\UsageBilling\Invoice\ServiceUsage::isAllUsage($mode)) {
                    $serviceMetrics[] = \WHMCS\UsageBilling\Service\ServiceMetric::factoryFromMetric($this, $metric, null, $usageItem, $currentTenantStatId);
                }
                foreach ($historicUsageByPeriod as $tenantStatId => $usage) {
                    $serviceMetrics[] = \WHMCS\UsageBilling\Service\ServiceMetric::factoryFromMetric($this, $metric->withUsage($usage), null, $usageItem, $tenantStatId);
                }
            } else {
                $serviceMetrics[] = \WHMCS\UsageBilling\Service\ServiceMetric::factoryFromMetric($this, $metric, $totalHistoricUsage, $usageItem, $currentTenantStatId);
            }
        }
        return $serviceMetrics;
    }
    public function getLink()
    {
        return \App::get_admin_folder_name() . "/clientsservices.php?productselect=" . $this->id;
    }
    public function getUniqueIdentifierValue($uniqueIdField)
    {
        $uniqueIdValue = null;
        if (!$uniqueIdField) {
            $uniqueIdField = "domain";
        }
        if (substr($uniqueIdField, 0, 12) == "customfield.") {
            $customFieldName = substr($uniqueIdField, 12);
            $uniqueIdValue = $this->serviceProperties->get($customFieldName);
        } else {
            $uniqueIdValue = $this->getAttribute($uniqueIdField);
        }
        return $uniqueIdValue;
    }
}

?>