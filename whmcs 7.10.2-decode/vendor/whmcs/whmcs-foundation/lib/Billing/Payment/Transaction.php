<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing\Payment;

class Transaction extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblaccounts";
    protected $dates = array("date");
    protected $columnMap = array("clientId" => "userid", "currencyId" => "currency", "paymentGateway" => "gateway", "exchangeRate" => "rate", "transactionId" => "transid", "amountIn" => "amountin", "amountOut" => "amountout", "invoiceId" => "invoiceid", "refundId" => "refundid");
    public $timestamps = false;
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public function invoice()
    {
        return $this->belongsTo("WHMCS\\Billing\\Invoice", "invoiceid");
    }
    public function scopeLookup($query, $gateway, $transactionId)
    {
        return $query->where("gateway", $gateway)->where("transid", $transactionId);
    }
}

?>