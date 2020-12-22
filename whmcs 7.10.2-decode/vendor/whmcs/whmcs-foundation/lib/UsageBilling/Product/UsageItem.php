<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Product;

class UsageItem extends \WHMCS\Model\AbstractModel
{
    use \WHMCS\Model\HasModuleTrait;
    use \WHMCS\Model\HasProductEntityTrait;
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $cachedModuleMetric = NULL;
    protected $table = "tblusage_items";
    protected $primaryKey = "id";
    protected $fillable = array("rel_type", "rel_id", "module", "module_type", "metric", "included", "is_hidden");
    protected $casts = array("is_hidden" => "boolean");
    public static function boot()
    {
        parent::boot();
        static::loadRelationClassMap();
        static::addGlobalScope("visible_only", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where("is_hidden", 0);
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
                $table->decimal("included", 19, 6)->default("0.0");
                $table->tinyInteger("is_hidden")->default(1);
                $table->timestamps();
                $table->softDeletes();
                $table->index(array("rel_type", "rel_id"), "tblusage_items_rel_type_id");
                $table->index(array("module_type"), "tblusage_items_module_type");
                $table->index(array("module"), "tblusage_items_module");
                $table->index(array("metric"), "tblusage_items_metric");
            });
        }
    }
    public function scopeIncludeDisabled($query)
    {
        return $query->withoutGlobalScope("visible_only");
    }
    public function scopeOfMetricName($query, $metric)
    {
        return $query->where("metric", $metric);
    }
    public static function firstOrNewByRelations($metric, \WHMCS\Model\AbstractModel $model = NULL, \WHMCS\Module\AbstractModule $module = NULL)
    {
        $query = static::includeDisabled();
        $query->ofMetricName($metric);
        if ($model) {
            $query->ofRelated($model);
        }
        if ($module) {
            $query->ofModule($module);
        }
        $item = $query->first();
        if ($item) {
            return $item;
        }
        $item = new static();
        $item->metric = $metric;
        if ($model) {
            $item->relationEntity = $model;
        }
        if ($module) {
            $item->module = $module;
        }
        $item->isHidden = 1;
        return $item;
    }
    public function pricingSchema()
    {
        return $this->hasMany("WHMCS\\UsageBilling\\Pricing\\Product\\Bracket", "rel_id", "id")->orderBy("floor");
    }
    public function getModuleMetric()
    {
        if (!$this->cachedModuleMetric) {
            $metricToCache = null;
            $module = $this->module;
            $metricName = $this->metric;
            if (!$metricName) {
                throw new \RuntimeException("Invalid metric");
            }
            if (!$module instanceof \WHMCS\Module\AbstractModule) {
                throw new \RuntimeException("Invalid module");
            }
            $metricProvider = $module->call("MetricProvider");
            if (!$metricProvider instanceof \WHMCS\UsageBilling\Contracts\Metrics\ProviderInterface) {
                throw new \RuntimeException("Invalid module metric provider");
            }
            foreach ($metricProvider->metrics() as $metric) {
                if ($metric->systemName() == $metricName) {
                    $metricToCache = $metric;
                    break;
                }
            }
            if (!$metricToCache) {
                throw new \RuntimeException("Invalid module metric");
            }
            $this->cachedModuleMetric = $metricToCache;
        }
        return $this->cachedModuleMetric;
    }
    public function setModuleMetric(\WHMCS\UsageBilling\Contracts\Metrics\MetricInterface $value)
    {
        $this->cachedModuleMetric = $value;
        return $this;
    }
    public function createPriceSchemaZero()
    {
        $pricingDetails = array(array("floor" => 0, "ceiling" => 0, "type" => \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_SIMPLE, "price" => array()));
        $price = new \WHMCS\Billing\Pricing();
        $cycles = $price->priceFields();
        foreach (\WHMCS\Billing\Currency::defaultSorting()->pluck("id")->toArray() as $id) {
            $pricingDetails[0]["price"][$id] = array_fill_keys($cycles, 0);
        }
        $this->createPriceSchema($pricingDetails);
    }
    public function createPriceSchema(array $pricingDetails = array())
    {
        $priceStub = new \WHMCS\UsageBilling\Pricing\Product\Pricing();
        foreach ($pricingDetails as $bracketDetail) {
            $floor = isset($bracketDetail["floor"]) ? $bracketDetail["floor"] : 0;
            $ceiling = isset($bracketDetail["ceiling"]) ? $bracketDetail["ceiling"] : 0;
            $bracket = new \WHMCS\UsageBilling\Pricing\Product\Bracket();
            $bracket->rel_type = \WHMCS\Contracts\ProductServiceTypes::TYPE_USAGE_ITEM;
            $bracket->rel_id = $this->id;
            $bracket->floor = $floor;
            $bracket->ceiling = $ceiling;
            $bracket->schema_type = $bracketDetail["type"];
            $bracket->save();
            foreach ($bracketDetail["price"] as $currencyId => $cyclePricing) {
                $setupPrice = \WHMCS\UsageBilling\Pricing\Product\Pricing::where("relid", $bracket->id)->where("type", $priceStub->pricingType())->where("currency", $currencyId)->first();
                if (!$setupPrice) {
                    $setupPrice = new \WHMCS\UsageBilling\Pricing\Product\Pricing(array("type" => $priceStub->pricingType(), "relid" => $bracket->id, "currency" => $currencyId));
                }
                foreach ($cyclePricing as $cycle => $value) {
                    if ($value <= 0) {
                        $value = 0;
                    }
                    $setupPrice->{$cycle} = $value;
                }
                $setupPrice->save();
            }
        }
    }
}

?>