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

class Helper
{
    public static function convertCurrency($amount, \WHMCS\Billing\Currency $currency, \WHMCS\Billing\Invoice $invoice)
    {
        $userCurrency = $invoice->client->currencyrel;
        if ($userCurrency->id != $currency->id) {
            $amount = convertCurrency($amount, $currency->id, $userCurrency->id);
            if ($invoice->total < $amount + 1 && $amount - 1 < $invoice->total) {
                $amount = $invoice->total;
            }
        }
        return $amount;
    }
}

?>