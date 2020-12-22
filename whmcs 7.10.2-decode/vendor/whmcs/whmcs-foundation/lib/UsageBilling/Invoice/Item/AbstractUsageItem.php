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

abstract class AbstractUsageItem implements \WHMCS\UsageBilling\Contracts\Invoice\UsageItemInterface
{
    private $serviceMetric = NULL;
    private $calculations = array();
    public function __construct(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric)
    {
        $serviceMetric = $this->useHistoricalUsage($serviceMetric);
        $this->setServiceMetric($serviceMetric);
        $usageItem = $serviceMetric->usageItem();
        $pricingSchema = $usageItem->pricingSchema;
        $calculations = $this->reduceIncludedCalculations($this->getUsageCalculations($serviceMetric, $pricingSchema));
        $this->setCalculations($calculations);
    }
    private function reduceIncludedCalculations($calculations)
    {
        $includedTotal = null;
        $reducedCalculations = array();
        foreach ($calculations as $calculation) {
            if ($calculation instanceof \WHMCS\UsageBilling\Invoice\Calculation\Included) {
                $includedTotal = ($includedTotal ?: 0) + $calculation->consumed();
            } else {
                $reducedCalculations[] = $calculation;
            }
        }
        if (!is_null($includedTotal)) {
            array_unshift($reducedCalculations, new \WHMCS\UsageBilling\Invoice\Calculation\Included($includedTotal, null, null, true));
        }
        return $reducedCalculations;
    }
    public function getInvoiceItem()
    {
        if (!$this->getCalculations()) {
            return null;
        }
        $attributes = $this->getDefaultServiceAttributes();
        $price = $this->calculatePrice();
        if ($price instanceof \WHMCS\View\Formatter\Price) {
            $price = $price->toNumeric();
        }
        if (!$price) {
            $price = 0;
        }
        $attributes["amount"] = $price;
        $attributes["description"] = $this->getLineItemDescription();
        return new \WHMCS\Billing\Invoice\Item($attributes);
    }
    protected function useHistoricalUsage(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric)
    {
        $historicalUsage = $serviceMetric->historicUsage();
        if ($historicalUsage) {
            $serviceMetric = $serviceMetric->withUsage($historicalUsage)->withHistoricUsage(null);
        }
        return $serviceMetric;
    }
    public function getServiceMetric()
    {
        return $this->serviceMetric;
    }
    public function setServiceMetric($serviceMetric)
    {
        $this->serviceMetric = $serviceMetric;
        return $this;
    }
    public function getCalculations()
    {
        return $this->calculations;
    }
    public function setCalculations($calculations)
    {
        $this->calculations = $calculations;
        return $this;
    }
    public function getServiceName()
    {
        $service = $this->getServiceMetric()->service();
        return $service->product->name . " - " . $service->domain;
    }
    public function getModule()
    {
        return $this->getServiceMetric()->service()->serverModel->getModuleInterface();
    }
    protected function getDefaultServiceAttributes()
    {
        $serviceMetric = $this->getServiceMetric();
        $service = $serviceMetric->service();
        if (\WHMCS\Config\Setting::getValue("ContinuousInvoiceGeneration")) {
            $dateField = "nextinvoicedate";
        } else {
            $dateField = "nextduedate";
        }
        return array("type" => \WHMCS\Billing\InvoiceItemInterface::TYPE_BILLABLE_USAGE, "relid" => (int) $serviceMetric->tenantStatId(), "userid" => $service->clientId, "paymentmethod" => $service->paymentGateway, "duedate" => $service->{$dateField}, "taxed" => false, "invoiceid" => 0);
    }
    public abstract function getUsageCalculations(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric, \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface $pricingSchema);
    protected function getLineItemDescription()
    {
        $serviceMetric = $this->getServiceMetric();
        $metricName = $serviceMetric->displayName();
        $units = $serviceMetric->units();
        $usage = $serviceMetric->usage();
        $type = $serviceMetric->type();
        $usageDateRange = "";
        if ($type != $serviceMetric::TYPE_SNAPSHOT) {
            $now = \WHMCS\Carbon::now();
            $usageRecord = $usage->collectedAt();
            $usageDateRange = "";
            if ($serviceMetric::TYPE_PERIOD_MONTH) {
                $usageDateRange = $usageRecord->startOfMonth()->toAdminDateFormat();
                if ($usageRecord->month === $now->month) {
                    $usageDateRange .= " - " . $now->toAdminDateFormat();
                } else {
                    $usageDateRange .= " - " . $usageRecord->endOfMonth()->toAdminDateFormat();
                }
            }
        }
        if ($usageDateRange) {
            $usageDateRange = " (" . $usageDateRange . ")";
        }
        $serviceName = sprintf("%s\n%s %s %s", $this->getServiceName(), $units->decorate($usage->value()), $metricName, $usageDateRange);
        $calculations = $this->getCalculations();
        $descriptions = array();
        foreach ($calculations as $calculation) {
            $descriptions[] = $this->getSingleLineDescription($calculation, $units);
        }
        array_unshift($descriptions, $serviceName);
        $description = implode("\n", $descriptions);
        return $description;
    }
    private function getSingleLineDescription(\WHMCS\UsageBilling\Contracts\Invoice\UsageCalculationInterface $calculation, \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface $units)
    {
        $consumed = $calculation->consumed();
        if (valueIsZero($consumed)) {
            return "";
        }
        $consumed = $units->formatForType($units->roundForType($consumed));
        if ($calculation->isIncluded()) {
            return \Lang::trans("metrics.invoiceitem.included", array(":included" => $consumed, ":metricname" => $units->perUnitName($consumed)));
        }
        $pricing = $calculation->price();
        $pricePerUnitPrice = new \WHMCS\View\Formatter\Price($pricing->monthly, $pricing->currency->toArray());
        $description = \Lang::trans("metrics.invoiceitem.perunit", array(":consumed" => $consumed, ":metricname" => $units->perUnitName($consumed), ":price" => $pricePerUnitPrice->toFull(), ":perUnitName" => $units->perUnitName(1)));
        return $description;
    }
    protected function calculatePrice()
    {
        $pricingAmounts = $this->getCalculations();
        $allPricing = array();
        foreach ($pricingAmounts as $pricingDetails) {
            if ($pricingDetails->isIncluded()) {
                continue;
            }
            $pricing = $pricingDetails->price();
            $consumed = $pricingDetails->consumed();
            $pricePerUnit = $pricing->monthly;
            if ($consumed < 0) {
                $consumed = 0;
            }
            $factoredPrice = $consumed * $pricePerUnit;
            $price = new \WHMCS\View\Formatter\Price($factoredPrice, $pricing->currency->toArray());
            $priceFormatted = $price->toNumeric();
            if ($priceFormatted < $factoredPrice && valueIsZero($priceFormatted) && !valueIsZero($consumed)) {
                $allPricing[] = new \WHMCS\View\Formatter\Price("0.01", $pricing->currency->toArray());
            } else {
                $allPricing[] = $price;
            }
        }
        $aggregatedPrice = 0;
        foreach ($allPricing as $sumMe) {
            $aggregatedPrice += (double) $sumMe->toNumeric();
        }
        if ($aggregatedPrice < 0.01) {
            return null;
        }
        return new \WHMCS\View\Formatter\Price($aggregatedPrice, $allPricing[0]->getCurrency());
    }
}

?>