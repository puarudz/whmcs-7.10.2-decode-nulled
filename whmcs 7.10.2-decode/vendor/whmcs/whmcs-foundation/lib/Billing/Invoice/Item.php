<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing\Invoice;

class Item extends \WHMCS\Model\AbstractModel implements \WHMCS\Billing\InvoiceItemInterface
{
    protected $table = "tblinvoiceitems";
    public $timestamps = false;
    protected $booleans = array("taxed");
    protected $dates = array("dueDate");
    protected $columnMap = array("relatedEntityId" => "relid");
    protected $fillable = array("type", "relid", "description", "amount", "userid", "paymentmethod", "duedate", "taxed", "invoiceid");
    public function invoice()
    {
        return $this->belongsTo("WHMCS\\Billing\\Invoice", "invoiceid");
    }
    public function addon()
    {
        return $this->belongsTo("WHMCS\\Service\\Addon", "relid");
    }
    public function domain()
    {
        return $this->belongsTo("WHMCS\\Domain\\Domain", "relid");
    }
    public function service()
    {
        return $this->belongsTo("WHMCS\\Service\\Service", "relid");
    }
    public function scopeOnlyServices($query)
    {
        return $query->where("type", self::TYPE_SERVICE);
    }
    public function scopeOnlyAddons($query)
    {
        return $query->where("type", self::TYPE_SERVICE_ADDON);
    }
    public function scopeOnlyDomains($query)
    {
        return $query->whereIn("type", array(self::TYPE_DOMAIN, self::TYPE_DOMAIN_REGISTRATION, self::TYPE_DOMAIN_TRANSFER));
    }
}

?>