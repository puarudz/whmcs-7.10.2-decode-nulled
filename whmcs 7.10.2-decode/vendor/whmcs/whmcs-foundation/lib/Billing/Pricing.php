<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing;

class Pricing extends \WHMCS\Model\AbstractModel implements PricingInterface
{
    protected $table = "tblpricing";
    public $timestamps = false;
    protected $fillable = array("relid", "type", "currency", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
    protected $types = NULL;
    public static function boot()
    {
        parent::boot();
        self::saving(function (Pricing $model) {
            if (empty($model->type)) {
                $model->type = $model->pricingType();
            }
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
                $table->enum("type", $this->types);
                $table->integer("currency")->default(0);
                $table->integer("relid")->default(0);
                $table->decimal("msetupfee", 10, 2);
                $table->decimal("qsetupfee", 10, 2);
                $table->decimal("ssetupfee", 10, 2);
                $table->decimal("asetupfee", 10, 2);
                $table->decimal("bsetupfee", 10, 2);
                $table->decimal("tsetupfee", 10, 2);
                $table->decimal("monthly", 10, 2);
                $table->decimal("quarterly", 10, 2);
                $table->decimal("semiannually", 10, 2);
                $table->decimal("annually", 10, 2);
                $table->decimal("biennially", 10, 2);
                $table->decimal("triennially", 10, 2);
            });
        }
    }
    public function priceFields()
    {
        return array("monthly", "quarterly", "semiannually", "annually", "biennially", "triennially");
    }
    public function setupFields()
    {
        return array("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee");
    }
    public function updateEnumField()
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($schemaBuilder->hasTable($this->getTable()) && $schemaBuilder->hasColumn($this->table, "type")) {
            \WHMCS\Database\Capsule::connection()->statement("ALTER TABLE " . $this->table . " CHANGE type type " . "enum('" . implode("','", $this->types) . "') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
        }
    }
    public function getCurrencyAttribute()
    {
        return Currency::find($this->getRawAttribute("currency"));
    }
    public function setCurrencyAttribute($value)
    {
        if ($value instanceof Currency && $value->exists) {
            $this->attributes["currency"] = $value->id;
        } else {
            if (is_numeric($value)) {
                $this->attributes["currency"] = $value;
            }
        }
    }
    public function getCurrencyIdAttribute()
    {
        return $this->getRawAttribute("currency");
    }
    public function setCurrencyIdAttribute($value)
    {
        $this->attributes["currency"] = $value;
    }
    public function pricingType()
    {
        return "";
    }
    public function supportedTypes()
    {
        return $this->types;
    }
}

?>