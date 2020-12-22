<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Metrics\Units;

class Accounts extends WholeNumber
{
    public function __construct($name = "Accounts", $singlePerUnitName = "Account", $pluralPerUnitName = "Accounts", $prefix = NULL, $suffix = "")
    {
        parent::__construct($name, $singlePerUnitName, $pluralPerUnitName, $prefix, $suffix);
    }
}

?>