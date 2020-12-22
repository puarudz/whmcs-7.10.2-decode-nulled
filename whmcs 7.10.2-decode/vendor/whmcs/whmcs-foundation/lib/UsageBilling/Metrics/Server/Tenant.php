<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Metrics\Server;

class Tenant extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblserver_tenants";
    public $timestamps = true;
    protected $fillable = array("server_id", "tenant", "created_at", "updated_at");
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->integer("server_id")->default(0);
                $table->string("tenant")->default("");
                $table->timestamps();
                $table->index(array("tenant", "server_id"), "server_tenant");
            });
        }
    }
    public function metricProvider()
    {
        $server = $this->server;
        $module = $server->getModuleInterface();
        if ($module->functionExists("MetricProvider")) {
            return $module->call("MetricProvider");
        }
        return null;
    }
    public function server()
    {
        return $this->belongsTo("WHMCS\\Product\\Server");
    }
    public function createStats(array $metrics)
    {
        foreach ($metrics as $metric) {
            $metricUsage = $metric->usage();
            $collectedAt = $metricUsage->collectedAt();
            $statFingerprint = array("tenant_id" => $this->id, "metric" => $metric->systemName());
            $type = $metric->type();
            if ($type == \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_SNAPSHOT) {
                $stat = Stat::firstOrNew($statFingerprint);
                $stat->value = $metricUsage->value();
            } else {
                $rangeDate = $collectedAt->copy();
                if ($type == \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_PERIOD_DAY) {
                    $startDay = $rangeDate->startOfDayMicro();
                    $endDay = $rangeDate->endOfDayMicro();
                    $range = array($startDay, $endDay);
                } else {
                    $startMonth = $rangeDate->startOfMonthMicro();
                    $endMonth = $rangeDate->endOfMonthMicro();
                    $range = array($startMonth, $endMonth);
                }
                $stat = Stat::whereBetween("measured_at", $range)->firstOrNew($statFingerprint);
                $stat->value = $metricUsage->value();
            }
            $stat->type = $type;
            if (!$stat->exists || $stat->measuredAt && $stat->measuredAt < $collectedAt->toMicroTime()) {
                $stat->measuredAt = $collectedAt->toMicroTime();
            }
            $stat->save();
        }
        $this->touch();
    }
}

?>