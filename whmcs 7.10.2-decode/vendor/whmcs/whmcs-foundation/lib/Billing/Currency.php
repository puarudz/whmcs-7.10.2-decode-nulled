<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing;

class Currency extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblcurrencies";
    public $timestamps = false;
    const DEFAULT_CURRENCY_ID = 1;
    public function scopeDefaultCurrency($query)
    {
        return $query->where("default", 1);
    }
    public function scopeDefaultSorting($query)
    {
        return $query->orderBy("default", "desc")->orderBy("code");
    }
    public static function validateCurrencyCode(&$currencyCode)
    {
        $currencyCode = strtoupper(trim($currencyCode));
        return (bool) preg_match("/^[A-Z]{2,4}\$/", $currencyCode);
    }
}

?>