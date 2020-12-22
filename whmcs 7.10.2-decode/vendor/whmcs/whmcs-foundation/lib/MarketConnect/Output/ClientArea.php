<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect\Output;

class ClientArea extends \WHMCS\ClientArea
{
    private $pricedCurrencyIds = NULL;
    protected function getCurrencyOptions()
    {
        $currencyOptions = parent::getCurrencyOptions();
        if (is_array($currencyOptions)) {
            if (is_null($this->pricedCurrencyIds)) {
                $this->pricedCurrencyIds = \WHMCS\Database\Capsule::table("tblpricing")->pluck("currency");
            }
            $pricedCurrencyOptions = array_filter($currencyOptions, function ($value) {
                return in_array($value["id"], $this->pricedCurrencyIds);
            });
        }
        return 1 < count($pricedCurrencyOptions) ? $pricedCurrencyOptions : "";
    }
}

?>