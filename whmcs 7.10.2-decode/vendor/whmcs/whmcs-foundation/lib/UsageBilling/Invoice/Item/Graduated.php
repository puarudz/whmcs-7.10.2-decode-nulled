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

class Graduated extends AbstractUsageItem
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
            return !$model->belowRange($consumptionToCharge, $units->type());
        });
        if ($brackets->isEmpty()) {
            $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($totalConsumption);
            return $calculations;
        }
        foreach ($brackets as $bracket) {
            $chargeFloor = $bracket->floor;
            $chargeCeiling = $bracket->ceiling;
            if ($bracket->withinRange($consumptionToCharge, $units->type())) {
                $consumed = $consumptionToCharge - $chargeFloor;
                $pricing = $bracket->pricingForCurrencyId($currency->id);
            } else {
                if ($units->type() === \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT && valueIsZero($chargeFloor)) {
                    $chargeFloor++;
                    $consumptionToCharge++;
                }
                $consumed = $chargeCeiling - $chargeFloor;
                $pricing = $bracket->pricingForCurrencyId($currency->id);
            }
            if ($bracket->isFree()) {
                $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Included($consumed);
            } else {
                if (!valueIsZero($consumed)) {
                    $calculations[] = new \WHMCS\UsageBilling\Invoice\Calculation\Charge($consumed, $pricing, $bracket);
                }
            }
        }
        return $calculations;
    }
}

?>