<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Pricing;

abstract class AbstractPriceBracket extends \WHMCS\Model\AbstractModel implements \WHMCS\UsageBilling\Contracts\Pricing\PriceBracketInterface
{
    protected $fillable = array("floor", "ceiling", "rel_type", "rel_id", "schema_type");
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->decimal("floor", 19, 6)->default("0.0");
                $table->decimal("ceiling", 19, 6)->default("0.0");
                $table->string("rel_type", 200)->default("");
                $table->string("rel_id", 200)->default("");
                $table->string("schema_type", 32)->default(\WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_FLAT);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
    public static function boot()
    {
        parent::boot();
        static::deleting(function (AbstractPriceBracket $bracket) {
            $bracket->pricing->each(function (\WHMCS\Model\AbstractModel $pricing) {
                $pricing->delete();
            });
        });
    }
    public function updateColumnsForDecimalsAndInclusive()
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($schemaBuilder->hasTable($this->getTable())) {
            \WHMCS\Database\Capsule::connection()->statement("ALTER TABLE " . $this->table . " CHANGE floor floor" . " decimal(19,6) NOT NULL DEFAULT '0.000000'");
            \WHMCS\Database\Capsule::connection()->statement("ALTER TABLE " . $this->table . " CHANGE ceiling ceiling" . " decimal(19,6) NOT NULL DEFAULT '0.000000'");
            if (!$schemaBuilder->hasColumn($this->getTable(), "schema_type")) {
                \WHMCS\Database\Capsule::connection()->statement("ALTER TABLE " . $this->table . " ADD schema_type" . " varchar(32) NOT NULL DEFAULT '" . \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_FLAT . "' AFTER `rel_id`");
            }
        }
    }
    public function newCollection(array $models = array())
    {
        return new PricingSchema($models);
    }
    public function belowRange($value, $unitType = \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT)
    {
        $valueIsLessThanFloor = $value < $this->floor;
        if ($valueIsLessThanFloor) {
            return true;
        }
        if ($unitType != \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT && $this->schemaType() == \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_GRADUATED) {
            $floorIsZero = valueIsZero($this->floor);
            $differenceIsZero = valueIsZero($value - $this->floor);
            return $differenceIsZero && !$floorIsZero;
        }
        return false;
    }
    public function withinRange($value, $unitType = \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT)
    {
        $ceilingIsUnlimited = valueIsZero($this->ceiling);
        $ceilingIsFloor = $this->ceiling <= $this->floor;
        if ($unitType != \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT && $this->schemaType() == \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_GRADUATED) {
            if ($this->floor < $value) {
                if ($ceilingIsFloor || $ceilingIsUnlimited) {
                    return true;
                }
                if ($value <= $this->ceiling) {
                    return true;
                }
            } else {
                if (valueIsZero($value) && valueIsZero($this->floor)) {
                    return true;
                }
            }
        } else {
            if ($this->floor <= $value) {
                if ($ceilingIsFloor || $ceilingIsUnlimited) {
                    return true;
                }
                if ($value < $this->ceiling) {
                    return true;
                }
            } else {
                if (valueIsZero($value) && valueIsZero($this->floor)) {
                    return true;
                }
            }
        }
        return false;
    }
    public function pricing()
    {
        $classname = $this->getPricingMorphClassname();
        $class = new $classname();
        $type = $class->pricingType();
        return $this->hasMany($class, "relid", "id")->where("type", $type);
    }
    public function pricingForCurrencyId($id)
    {
        $pricing = null;
        foreach ($this->pricing as $price) {
            if ($price->currencyId == $id) {
                $pricing = $price;
                break;
            }
        }
        if ($pricing) {
            $pricing->setRelation("bracket", $this);
        }
        return $pricing;
    }
    public abstract function getPricingMorphClassname();
    public abstract function relationEntity();
    public function schemaType()
    {
        $type = $this->schema_type;
        if (!$type || !in_array($type, PricingSchema::getSchemaTypes())) {
            $type = \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_SIMPLE;
        }
        return $type;
    }
    public function isFree()
    {
        $pricing = $this->pricing;
        foreach ($pricing as $currencyPrice) {
            foreach ($currencyPrice->priceFields() as $field) {
                $price = $currencyPrice->{$field};
                if (is_numeric($price) && $price < 0) {
                    continue;
                }
                if (!valueIsZero($price)) {
                    return false;
                }
            }
        }
        return true;
    }
}

?>