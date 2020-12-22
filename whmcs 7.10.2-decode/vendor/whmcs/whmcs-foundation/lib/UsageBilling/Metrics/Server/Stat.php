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

class Stat extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbltenant_stats";
    public $timestamps = true;
    protected $fillable = array("tenant_id", "metric", "type", "value", "measured_at", "invoice_id");
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->integer("tenant_id")->default(0);
                $table->string("metric")->default("");
                $table->string("type")->default("");
                $table->decimal("value", 19, 6)->default("0.0");
                $table->decimal("measured_at", 18, 6)->default("0.0");
                $table->integer("invoice_id")->default(0)->nullable();
                $table->timestamps();
                $table->index(array("tenant_id"), "tenant_id");
            });
        }
    }
    public function unbilledValueBefore(\WHMCS\Carbon $startOfCycle, Tenant $tenant, \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $metric)
    {
        return $this->unbilledQueryBefore($startOfCycle, $tenant, $metric)->select(\WHMCS\Database\Capsule::raw("sum(value) as sumvalue, metric"))->groupBy("metric")->value("sumvalue");
    }
    public function unbilledQueryBefore(\WHMCS\Carbon $startOfCycle, Tenant $tenant, \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $metric)
    {
        return $this->where("tenant_id", $tenant->id)->where("measured_at", "<", $startOfCycle->toMicroTime())->where("invoice_id", 0)->where("metric", $metric->systemName());
    }
    public function unbilledValueAfter(\WHMCS\Carbon $startOfCycle, Tenant $tenant, \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $metric)
    {
        return $this->where("tenant_id", $tenant->id)->where("measured_at", ">=", $startOfCycle->toMicroTime())->where("invoice_id", 0)->where("metric", $metric->systemName())->select(\WHMCS\Database\Capsule::raw("sum(value) as value, metric, measured_at"))->groupBy("metric")->first();
    }
    public function unbilledFirstAfter(\WHMCS\Carbon $startOfCycle, Tenant $tenant, \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $metric)
    {
        return $this->where("tenant_id", $tenant->id)->where("measured_at", ">=", $startOfCycle->toMicroTime())->where("invoice_id", 0)->where("metric", $metric->systemName())->first();
    }
    public function unbilledValueFirst(Tenant $tenant, \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $metric)
    {
        return $this->where("tenant_id", $tenant->id)->where("invoice_id", 0)->where("metric", $metric->systemName())->orderBy("measured_at", "desc")->first();
    }
}

?>