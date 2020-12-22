<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\GoCardless;

class GoCardless
{
    const SUPPORTED_CURRENCIES = array("AUD", "CAD", "DKK", "EUR", "GBP", "NZD", "SEK", "USD");
    const SCHEMES = array("AUD" => "becs", "CAD" => "pad", "DKK" => "betalingsservice", "EUR" => "sepa", "GBP" => "bacs", "NZD" => "becs_nz", "SEK" => "autogiro", "USD" => "ach");
    const SCHEME_NAMES = array("becs" => "BECS", "pad" => "PAD", "betalingsservice" => "Betalingsservice", "sepa" => "SEPA", "bacs" => "BACS", "becs_nz" => "BECS NZ", "autogiro" => "Autogiro", "ach" => "ACH");
}

?>