<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Contracts\Pricing;

interface PriceBracketInterface
{
    public function schemaType();
    public function withinRange($value, $unitType);
    public function belowRange($value, $unitType);
    public function pricing();
    public function isFree();
    public function pricingForCurrencyId($id);
    public function newCollection();
    public function relationEntity();
}

?>