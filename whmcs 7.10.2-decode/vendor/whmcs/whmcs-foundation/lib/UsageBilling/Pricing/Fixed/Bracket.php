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

class Bracket extends \WHMCS\UsageBilling\Pricing\AbstractPriceBracket
{
    use \WHMCS\Model\HasServiceEntityTrait;
    protected $table = "tblpricing_fixed_bracket";
    public function getPricingMorphClassname()
    {
        return "WHMCS\\UsageBilling\\Pricing\\Fixed\\Pricing";
    }
    public function servicePricing($service)
    {
        if ($service instanceof \WHMCS\Service\ConfigOption) {
            $service = $service->service;
        }
        $currency = $service->client->currencyrel;
        $billingCycle = $service->billingCycle;
        $pricing = $this->pricing()->where("currency", $currency->id)->where($billingCycle, ">", 0)->first();
        if ($pricing) {
            $pricing->setRelation("bracket", $this);
        }
        return $pricing;
    }
}

?>