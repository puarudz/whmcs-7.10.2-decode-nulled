<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module;

class GatewaySetting extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblpaymentgateways";
    public $timestamps = false;
    protected $fillable = array("gateway", "setting");
    public function scopeGateway(\Illuminate\Database\Eloquent\Builder $query, $gatewayName)
    {
        return $query->where("gateway", $gatewayName);
    }
    public function scopeSetting(\Illuminate\Database\Eloquent\Builder $query, $settingName)
    {
        return $query->where("setting", $settingName);
    }
    public static function getValue($gateway, $setting)
    {
        $setting = self::gateway($gateway)->setting($setting)->first();
        return $setting->value;
    }
    public static function setValue($gateway, $setting, $value)
    {
        $setting = self::firstOrNew(array("gateway" => $gateway, "setting" => $setting));
        $setting->value = $value;
        $setting->save();
        return $setting;
    }
    public static function getForGateway($gateway)
    {
        return self::gateway($gateway)->pluck("value", "setting");
    }
}

?>