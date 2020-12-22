<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Addon;

class Setting extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbladdonmodules";
    protected $fillable = array("module", "setting");
    public $timestamps = false;
    public function scopeModule($query, $module)
    {
        return $query->where("module", $module);
    }
}

?>