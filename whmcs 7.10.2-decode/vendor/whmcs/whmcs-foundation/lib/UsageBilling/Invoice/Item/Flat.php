<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Invoice\Item;

class Flat extends AbstractUsageItem
{
    public function getUsageCalculations(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric, \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface $pricingSchema)
    {
        $calculations = array();
        $units = $serviceMetric->units();
        $totalConsumption = $units->roundForType($serviceMetric->usage()->value());
        $firstBracket = $pricingSchema->first();
        if (!$firstBracket) {
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($totalConsumption);
            return $calculations;
        }
        $freeLimit = $pricingSchema->freeLimit();
        if ($pricingSchema->isFree() || is_null($freeLimit)) {
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($totalConsumption);
            return $calculations;
        }
        $firstCostBracket = $pricingSchema->firstCostBracket();
        if ($firstCostBracket->belowRange($totalConsumption)) {
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($totalConsumption);
            return $calculations;
        }
        $included = $serviceMetric->usageItem()->included;
        $consumptionToCharge = $totalConsumption - $included;
        if (!valueIsZero($included)) {
            if ($consumptionToCharge < 0 || valueIsZero($consumptionToCharge)) {
                $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($totalConsumption);
                return $calculations;
            }
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($included);
        }
        $currency = $serviceMetric->service()->client->currencyrel;
        $brackets = $pricingSchema->filter(function (\WHMCS\UsageBilling\Contracts\Pricing\PriceBracketInterface $model) use($consumptionToCharge, $units) {
            return $model->withinRange($consumptionToCharge, $units->type());
        });
        if ($brackets->isEmpty()) {
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($totalConsumption);
            return $calculations;
        }
        if (1 < $brackets->count()) {
            $bracket = $brackets->where("floor", $brackets->max("floor"))->first();
        } else {
            $bracket = $brackets->first();
        }
        if ($firstBracket->isFree()) {
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($firstBracket->ceiling);
        }
        $chargeFloor = $pricingSchema->freeLimit();
        $consumed = $consumptionToCharge - $chargeFloor;
        if (!valueIsZero($consumed)) {
            $pricing = $bracket->pricingForCurrencyId($currency->id);
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Charge($consumed, $pricing, $bracket);
        }
        return $calculations;
    }
}

?>