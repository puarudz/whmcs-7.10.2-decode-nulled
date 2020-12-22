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

class ConfigOptionGroup extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblproductconfiggroups";
    protected $primaryKey = "id";
    public $timestamps = false;
    protected $fillable = array("name", "description");
    protected $casts = array("name" => "string", "description" => "string");
    protected $configOptionClass = "WHMCS\\Product\\ConfigOption";
    public function configOptions()
    {
        return $this->hasMany($this->configOptionClass, "gid", "id");
    }
}

?>