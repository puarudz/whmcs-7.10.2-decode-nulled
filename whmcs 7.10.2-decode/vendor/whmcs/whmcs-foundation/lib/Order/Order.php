<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Order;

class Order extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblorders";
    public $timestamps = false;
    protected $dates = array("date");
    protected $columnMap = array("clientId" => "userid", "orderNumber" => "ordernum");
    protected $appends = array("isPaid");
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public function contact()
    {
        return $this->belongsTo("WHMCS\\User\\Client\\Contact", "contactid");
    }
    public function services()
    {
        return $this->hasMany("WHMCS\\Service\\Service", "orderid");
    }
    public function addons()
    {
        return $this->hasMany("WHMCS\\Service\\Addon", "orderid");
    }
    public function domains()
    {
        return $this->hasMany("WHMCS\\Domain\\Domain", "orderid");
    }
    public function invoice()
    {
        return $this->hasOne("WHMCS\\Billing\\Invoice", "id", "invoiceid");
    }
    public function getIsPaidAttribute()
    {
        if (0 < $this->invoiceId) {
            return $this->invoice->status == "Paid";
        }
        return false;
    }
}

?>