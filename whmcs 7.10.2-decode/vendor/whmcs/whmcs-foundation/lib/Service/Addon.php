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

class Addon extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblhostingaddons";
    protected $columnMap = array("orderId" => "orderid", "serviceId" => "hostingid", "clientId" => "userid", "recurringFee" => "recurring", "registrationDate" => "regdate", "applyTax" => "tax", "terminationDate" => "termination_date", "paymentGateway" => "paymentmethod", "serverId" => "server", "productId" => "addonid", "subscriptionId" => "subscriptionid");
    protected $dates = array("regDate", "registrationDate", "nextdueDate", "nextinvoiceDate", "terminationDate");
    protected $appends = array("serviceProperties");
    public static function boot()
    {
        parent::boot();
        self::deleted(function (Addon $addon) {
            Ssl::where("addon_id", $addon->id)->delete();
        });
    }
    public function scopeUserId(\Illuminate\Database\Eloquent\Builder $query, $userId)
    {
        return $query->where("userid", "=", $userId);
    }
    public function scopeOfService(\Illuminate\Database\Eloquent\Builder $query, $serviceId)
    {
        return $query->where("hostingid", $serviceId);
    }
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where("status", Service::STATUS_ACTIVE);
    }
    public function scopeMarketConnect(\Illuminate\Database\Eloquent\Builder $query)
    {
        $marketConnectAddonIds = \WHMCS\Product\Addon::marketConnect()->pluck("id");
        return $query->whereIn("addonid", $marketConnectAddonIds);
    }
    public function scopeIsConsideredActive(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->whereIn("status", array(Service::STATUS_ACTIVE, Service::STATUS_SUSPENDED));
    }
    public function scopeIsNotRecurring($query)
    {
        return $query->whereIn("billingcycle", array("Free", "Free Account", "One Time"));
    }
    public function service()
    {
        return $this->belongsTo("WHMCS\\Service\\Service", "hostingid");
    }
    public function productAddon()
    {
        return $this->belongsTo("WHMCS\\Product\\Addon", "addonid");
    }
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public function customFieldValues()
    {
        return $this->hasMany("WHMCS\\CustomField\\CustomFieldValue", "relid");
    }
    protected function getCustomFieldType()
    {
        return "addon";
    }
    protected function getCustomFieldRelId()
    {
        return $this->addonId;
    }
    public function order()
    {
        return $this->belongsTo("WHMCS\\Order\\Order", "orderid");
    }
    public function paymentGateway()
    {
        return $this->hasMany("WHMCS\\Billing\\Gateway", "gateway", "paymentmethod");
    }
    public function getServicePropertiesAttribute()
    {
        return new Properties($this);
    }
    public function ssl()
    {
        return $this->hasMany("WHMCS\\Service\\Ssl");
    }
    public function canBeUpgraded()
    {
        return $this->status == "Active";
    }
    public function isService()
    {
        return false;
    }
    public function isAddon()
    {
        return true;
    }
    public function serverModel()
    {
        return $this->hasOne("\\WHMCS\\Product\\Server", "id", "server");
    }
    public function failedActions()
    {
        return $this->hasMany("WHMCS\\Module\\Queue", "service_id")->where("service_type", "=", "addon");
    }
    public function legacyProvision()
    {
        try {
            if (!function_exists("ModuleCallFunction")) {
                require_once ROOTDIR . "/includes/modulefunctions.php";
            }
            return ModuleCallFunction("Create", $this->serviceId, array(), $this->id);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    public function isRecurring()
    {
        return !in_array($this->billingCycle, array(\WHMCS\Billing\Cycles::DISPLAY_FREE, \WHMCS\Billing\Cycles::DISPLAY_ONETIME));
    }
    public function getLink()
    {
        return \App::get_admin_folder_name() . "/clientsservices.php?productselect=a" . $this->id;
    }
}

?>