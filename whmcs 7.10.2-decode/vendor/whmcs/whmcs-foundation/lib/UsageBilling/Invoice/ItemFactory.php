<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Invoice;

class ItemFactory
{
    public function factoryItemsFromService(\WHMCS\Service\Service $service, $mode = NULL)
    {
        if (is_null($mode)) {
            $mode = ServiceUsage::getRecurringInvoiceMode();
        }
        $userLang = getUsersLang($service->clientId);
        $serviceMetrics = $service->metrics(false, $mode);
        $items = array();
        foreach ($serviceMetrics as $serviceMetric) {
            if ($serviceMetric->isEnabled()) {
                $item = $this->factoryInvoiceItem($serviceMetric);
                if ($item) {
                    $items[] = $item;
                }
            } else {
                $this->markHistoryAsNeverBill($serviceMetric, $mode);
            }
        }
        if ($userLang) {
            swapLang($userLang);
        }
        return $items;
    }
    private function markHistoryAsNeverBill(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric, $mode)
    {
        if ($mode == ServiceUsage::getQuickViewMode()) {
            return NULL;
        }
        if ($serviceMetric->type() === \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_SNAPSHOT) {
            return NULL;
        }
        $tenantStatId = $serviceMetric->tenantStatId();
        if (!$tenantStatId) {
            return NULL;
        }
        $startOfMetricPeriod = $serviceMetric->usage()->startAt();
        $startOfCurrentPeriod = \WHMCS\Carbon::now()->startOfMonth();
        if ($startOfMetricPeriod->lte($startOfCurrentPeriod) || ServiceUsage::isAllUsage($mode)) {
            \WHMCS\UsageBilling\Metrics\Server\Stat::where("id", $tenantStatId)->update(array("invoice_id" => -1));
        }
    }
    public function factoryInvoiceItem(\WHMCS\UsageBilling\Service\ServiceMetric $serviceMetric)
    {
        $service = $serviceMetric->service();
        $module = $service->serverModel->getModuleInterface();
        if ($module->functionExists("metric_price_calculation")) {
            $delegate = new Item\ModuleDelegate($serviceMetric);
        } else {
            $usageItem = $serviceMetric->usageItem();
            $pricingSchema = $usageItem->pricingSchema;
            $schemaType = $pricingSchema->schemaType();
            switch ($schemaType) {
                case \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_GRADUATED:
                    $delegate = new Item\Graduated($serviceMetric);
                    break;
                case \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_FLAT:
                    $delegate = new Item\Flat($serviceMetric);
                    break;
                case \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_SIMPLE:
                default:
                    $delegate = new Item\Simple($serviceMetric);
            }
            return $delegate->getInvoiceItem();
        }
    }
}

?>