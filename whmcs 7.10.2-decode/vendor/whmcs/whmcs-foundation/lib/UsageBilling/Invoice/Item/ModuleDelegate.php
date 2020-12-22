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

class ModuleDelegate extends AbstractUsageItem
{
    public function getUsageCalculations(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric, \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface $pricingSchema)
    {
        return array();
    }
    public function getInvoiceItem()
    {
        $attributes = $this->getDefaultServiceAttributes();
        $serviceMetric = $this->getServiceMetric();
        $serviceName = $this->getServiceName();
        $service = $serviceMetric->service();
        $metricName = $serviceMetric->displayName();
        $usageAmount = $serviceMetric->usage()->value();
        $price = null;
        $description = "";
        $module = $this->getModule();
        $surchargeCalculation = $module->call("metric_price_calculation", array("metricUsage" => $serviceMetric, "service" => $service, "serviceName" => $serviceName, "usageAmount" => $usageAmount, "metricName" => $metricName));
        if (isset($surchargeCalculation["price"])) {
            $price = $surchargeCalculation["price"];
        }
        if ($price instanceof \WHMCS\View\Formatter\Price) {
            $price = $price->toFull();
        }
        if (!$price) {
            $price = 0;
        }
        if (isset($surchargeCalculation["description"])) {
            $description = $surchargeCalculation["description"];
        }
        if (!$description) {
            $description = $usageAmount . " " . $metricName . " @ " . $price;
        }
        $attributes["description"] = $description;
        $attributes["price"] = $price;
        return new \WHMCS\Billing\Invoice\Item($attributes);
    }
}

?>