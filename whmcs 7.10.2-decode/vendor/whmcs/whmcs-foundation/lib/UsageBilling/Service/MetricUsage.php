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

class MetricUsage extends \WHMCS\Model\AbstractModel
{
    use \WHMCS\Model\HasModuleTrait;
    use \WHMCS\Model\HasServiceEntityTrait;
    protected $table = "tblmetric_usage";
    protected $primaryKey = "id";
    protected $fillable = array("rel_type", "rel_id", "module", "module_type", "metric");
    public static function boot()
    {
        parent::boot();
        static::loadRelationClassMap();
        static::deleting(function (MetricUsage $metricUsage) {
            $metricUsage->pricingSchema->each(function (\WHMCS\UsageBilling\Pricing\Fixed\Bracket $bracket) {
                $bracket->delete();
            });
        });
    }
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->string("rel_type", 200)->default("");
                $table->integer("rel_id")->default(0);
                $table->string("module_type", 200)->default("");
                $table->string("module", 200)->default("");
                $table->string("metric", 200)->default("");
                $table->string("value", 255)->default("");
                $table->timestamps();
                $table->index(array("rel_type", "rel_id"), "tblusage_items_rel_type_id");
                $table->index(array("module_type"), "tblusage_items_module_type");
                $table->index(array("module"), "tblusage_items_module");
                $table->index(array("metric"), "tblusage_items_metric");
            });
        }
    }
    public function pricingSchema()
    {
        return $this->hasMany("WHMCS\\UsageBilling\\Pricing\\Fixed\\Bracket", "id")->orderBy("floor", "asc")->orderBy("ceiling", "desc");
    }
    public function factoryInvoiceItem()
    {
        $service = $this->relationEntity;
        if ($service instanceof \WHMCS\Service\ConfigOption) {
            $billingName = $service->productConfigOptionSelection->displayName;
            $service = $service->service;
        } else {
            $billingName = $service->product->name;
        }
        $metricName = $this->metric;
        $module = $this->module;
        if ($module->functionExists("metric_unit_value")) {
            $units = $this->module->call("metric_unit_value", array("metricUsage" => $this));
            $this->value = $units;
            $this->save();
        } else {
            $units = $this->value;
        }
        $price = null;
        $description = "";
        if ($module->functionExists("metric_price_calculation")) {
            $surchargeCalculation = $module->call("metric_price_calculation", array("metricUsage" => $this, "service" => $service, "billingName" => $billingName, "units" => $units, "metricName" => $metricName));
            if (isset($surchargeCalculation["price"])) {
                $description = $surchargeCalculation["description"];
                $price = $surchargeCalculation["price"];
            }
        } else {
            $pricing = $this->pricingSchema->fixedUsagePricing();
            if ($pricing) {
                $pricePerUnit = $pricing->{$service->billingCycle};
                $bracket = $pricing->bracket;
                $unitsToBill = $units - (int) $bracket->floor;
                $price = new \WHMCS\View\Formatter\Price($unitsToBill * $pricePerUnit, $pricing->currency->toArray());
                $description = sprintf("%s - %s %s Included\n" . "%s Additional %s @ %s Each", $billingName, $bracket->floor, $metricName, $unitsToBill, $metricName, $price->toFull());
            }
        }
        if (!$description) {
            $description = $units . " " . $metricName . " @ 0.00";
        }
        $matchfield = \WHMCS\Config\Setting::getValue("ContinuousInvoiceGeneration") ? "nextinvoicedate" : "nextduedate";
        $invoiceItem = new \WHMCS\Billing\Invoice\Item();
        $invoiceItem->type = \WHMCS\Billing\InvoiceItemInterface::TYPE_BILLABLE_USAGE;
        $invoiceItem->relatedEntityId = $this->id;
        $invoiceItem->description = $description;
        $invoiceItem->amount = $price ? $price->toNumeric() : 0;
        $invoiceItem->userId = $service->clientId;
        $invoiceItem->paymentMethod = $service->paymentGateway;
        $invoiceItem->dueDate = $service->{$matchfield};
        $invoiceItem->description = $description;
        $invoiceItem->taxed = false;
        $invoiceItem->invoiceId = 0;
        return $invoiceItem;
    }
}

?>