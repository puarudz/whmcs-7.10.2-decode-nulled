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

class ConfigOptionGroupLinks extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblproductconfiglinks";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = array("pid", "gid");
    protected $configOptionGroupClass = "WHMCS\\Product\\ConfigOptionGroup";
    protected $productClass = "WHMCS\\Product\\Product";
    public function configGroup()
    {
        return $this->hasOne($this->configOptionGroupClass, "id", "gid");
    }
    public function product()
    {
        return $this->hasOne($this->productClass, "id", "pid");
    }
    public function scopeGroupId($query, $groupId)
    {
        return $query->where("gid", $groupId);
    }
    public function scopeProductId($query, $productId)
    {
        return $query->where("pid", $productId);
    }
}

?>