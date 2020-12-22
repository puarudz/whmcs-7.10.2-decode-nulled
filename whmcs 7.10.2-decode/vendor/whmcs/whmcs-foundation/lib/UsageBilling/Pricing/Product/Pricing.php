<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Pricing\Product;

class Pricing extends \WHMCS\Billing\Pricing
{
    public function pricingType()
    {
        return \WHMCS\Billing\PricingInterface::TYPE_USAGE;
    }
    public function bracket()
    {
        $this->belongsTo("WHMCS\\UsageBilling\\Pricing\\Product\\Bracket", "id", "relid");
    }
    public function createFixedPricing(\WHMCS\UsageBilling\Pricing\Fixed\Bracket $bracket)
    {
        return \WHMCS\UsageBilling\Pricing\Fixed\Pricing::create(array("currency" => $this->getRawAttribute("currency"), "relid" => $bracket->id, "type" => \WHMCS\Billing\PricingInterface::TYPE_USAGE, "msetupfee" => $this->msetupfee, "qsetupfee" => $this->qsetupfee, "ssetupfee" => $this->ssetupfee, "asetupfee" => $this->asetupfee, "bsetupfee" => $this->bsetupfee, "tsetupfee" => $this->tsetupfee, "monthly" => $this->monthly, "quarterly" => $this->quarterly, "semiannually" => $this->semiannually, "annually" => $this->annually, "biennially" => $this->biennially, "triennially" => $this->triennially));
    }
}

?>