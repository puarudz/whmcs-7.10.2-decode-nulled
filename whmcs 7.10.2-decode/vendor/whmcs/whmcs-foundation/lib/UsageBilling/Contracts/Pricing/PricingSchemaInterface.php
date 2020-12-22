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

interface PricingSchemaInterface extends \WHMCS\Contracts\CollectionInterface
{
    const TYPE_SIMPLE = "simple";
    const TYPE_FLAT = "flat";
    const TYPE_GRADUATED = "grad";
    public function getStubInclusiveBracket();
    public static function getSchemaTypes();
    public function schemaType();
    public function isFree();
    public function freeLimit();
    public function firstCostBracket();
    public function fixedUsagePricing();
}

?>