<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Environment\WHMCS;

class UsageBilling extends \WHMCS\Environment\Component
{
    const NAME = "UsageBilling";
    public function __construct()
    {
        parent::__construct(static::NAME);
        $this->addTopic("MetricSettings", array($this, "topicSettings"))->addTopic("ProductMetrics", array($this, "topicProductMetrics"));
    }
    protected function topicSettings()
    {
        return array(array("key" => \WHMCS\UsageBilling\MetricUsageSettings::NAME_COLLECTION, "value" => \WHMCS\UsageBilling\MetricUsageSettings::isCollectionEnable()), array("key" => \WHMCS\UsageBilling\MetricUsageSettings::NAME_INVOICING, "value" => \WHMCS\UsageBilling\MetricUsageSettings::isInvoicingEnabled()));
    }
    protected function topicProductMetrics()
    {
        $productCache = array();
        $metrics = array();
        $usageItems = \WHMCS\UsageBilling\Product\UsageItem::all();
        foreach ($usageItems as $usageItem) {
            $hasFree = $hasOnetime = $hasNonMonthlyRecurring = false;
            if ($usageItem->isHidden || $usageItem->rel_type !== \WHMCS\Contracts\ProductServiceTypes::TYPE_PRODUCT_PRODUCT) {
                continue;
            }
            if (!isset($productCache[$usageItem->rel_id])) {
                $product = $usageItem->relationEntity;
                $productCache[$usageItem->rel_id] = $product;
            } else {
                $product = $productCache[$usageItem->rel_id];
            }
            if (!$product) {
                continue;
            }
            if (!$product->isHidden && !$product->isRetired) {
                $pricing = $usageItem->pricingSchema;
                $schemaType = $pricing->schemaType();
                $floors = $pricing->count();
                $included = $usageItem->included;
                $cycleType = $product->paymentType;
                $cycles = $product->getAvailableBillingCycles();
                if ($cycleType == "free") {
                    $hasFree = true;
                } else {
                    if ($cycleType == "onetime") {
                        $hasOnetime = true;
                    } else {
                        if ($cycleType == "recurring") {
                            foreach ($cycles as $cycle) {
                                if ($cycle !== "monthly") {
                                    $hasNonMonthlyRecurring = true;
                                    break;
                                }
                            }
                        }
                    }
                }
                $metrics[] = array("key" => $usageItem->metric, "value" => array("hasIncluded" => !valueIsZero($included), "schemaType" => $schemaType, "hasMultipleBrackets" => 1 < count($floors), "hasFreeCycle" => $hasFree, "hasOnetimeCycle" => $hasOnetime, "hasNonMonthlyRecurringCycle" => $hasNonMonthlyRecurring, "module" => $usageItem->moduleName, "productType" => $product->type));
            }
        }
        return $metrics;
    }
}

?>