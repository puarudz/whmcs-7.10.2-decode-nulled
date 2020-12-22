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

class ConfigOption extends \WHMCS\Model\AbstractModel
{
    use CompoundNameTrait;
    protected $table = "tblproductconfigoptions";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = array("gid", "optionname", "optiontype", "qtyminimum", "qtymaximum", "order", "hidden");
    protected $casts = array("gid" => "integer", "optionname" => "string", "optiontype" => "integer", "qtyminimum" => "integer", "qtymaximum" => "integer", "order" => "integer", "hidden" => "boolean");
    protected $columnMap = array("groupId" => "gid", "isHidden" => "hidden");
    protected $selectableOptionClass = "WHMCS\\Product\\ConfigOptionSelection";
    protected $configGroupClass = "WHMCS\\Product\\ConfigOptionGroup";
    public function selectableOptions()
    {
        return $this->hasMany($this->selectableOptionClass, "configid", "id");
    }
    public function configGroup()
    {
        return $this->belongsTo($this->configGroupClass, "gid", "id");
    }
    public function scopeOfProduct($query, Product $product)
    {
        return $query->ofProductId($product->id);
    }
    public function scopeOfProductId($query, $productId)
    {
        return $query->whereIn("gid", ConfigOptionGroupLinks::productId($productId)->pluck("gid"));
    }
}

?>