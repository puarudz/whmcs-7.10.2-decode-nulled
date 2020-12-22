<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Pricing\Fixed;

class Pricing extends \WHMCS\Billing\Pricing
{
    protected $table = "tblpricing_fixed";
    public function bracket()
    {
        $this->belongsTo("WHMCS\\UsageBilling\\Pricing\\Fixed\\Bracket", "id", "relid");
    }
    public function pricingType()
    {
        return \WHMCS\Billing\PricingInterface::TYPE_USAGE;
    }
}

?>