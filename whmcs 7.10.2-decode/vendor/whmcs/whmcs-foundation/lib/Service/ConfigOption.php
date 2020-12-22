<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Service;

class ConfigOption extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblhostingconfigoptions";
    public $timestamps = false;
    public function scopeOfService($query, Service $service)
    {
        return $query->where("relid", $service->id);
    }
    public function productConfigOptionSelection()
    {
        return $this->hasOne("WHMCS\\Product\\ConfigOptionSelection", "id", "optionid");
    }
    public function productConfigOption()
    {
        return $this->hasOne("WHMCS\\Product\\ConfigOption", "id", "configid");
    }
    public function service()
    {
        return $this->hasOne("WHMCS\\Service\\Service", "id", "relid");
    }
    public function metricUsage()
    {
        return $this->hasMany("WHMCS\\UsageBilling\\Service\\MetricUsage", "rel_id", "id")->where("rel_type", \WHMCS\Contracts\ProductServiceTypes::TYPE_SERVICE_CONFIGOPTION);
    }
}

?>