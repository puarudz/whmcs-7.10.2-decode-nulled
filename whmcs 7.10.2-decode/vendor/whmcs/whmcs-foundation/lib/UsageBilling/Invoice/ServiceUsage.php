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

class ServiceUsage
{
    private $service = NULL;
    const MODE_VALUES_COMPACT_HIST = 1;
    const MODE_VALUES_NON_COMPACT_HIST = 2;
    const MODE_PERIODS_ANY = 4;
    const MODE_PERIODS_CLOSED = 8;
    public function __construct($service)
    {
        $this->service = $this->getServiceOrFail($service);
        if (!function_exists("createInvoices")) {
            include_once ROOTDIR . "/includes/processinvoices.php";
        }
    }
    private function getServiceOrFail($service)
    {
        if (is_numeric($service)) {
            $service = \WHMCS\Service\Service::find($service);
        }
        if (!$service instanceof \WHMCS\Service\Service) {
            throw new \RuntimeException("Invalid Service or Service ID");
        }
        return $service;
    }
    public function generateInvoiceItems($mode = NULL, $nextDueDate = NULL, $tax = 0)
    {
        if (is_null($mode)) {
            $mode = ServiceUsage::getRecurringInvoiceMode();
        }
        if (is_null($nextDueDate)) {
            $nextDueDate = \WHMCS\Carbon::now()->toDateString();
        }
        $service = $this->service;
        $itemFactory = new ItemFactory();
        $usageInvoiceItems = $itemFactory->factoryItemsFromService($service, $mode);
        $itemIds = array();
        foreach ($usageInvoiceItems as $item) {
            $item->taxed = $tax;
            $item->dueDate = $nextDueDate;
            $item->save();
            $itemIds[] = $item->id;
        }
        return $usageInvoiceItems;
    }
    public static function markUsageAsInvoiced($invoiceId, $invoiceLineItems = array())
    {
        $statIds = array();
        foreach ($invoiceLineItems as $item) {
            if ($item->type == \WHMCS\Billing\InvoiceItemInterface::TYPE_BILLABLE_USAGE) {
                $statIds[] = $item->relid;
            }
        }
        if (!empty($statIds)) {
            \WHMCS\UsageBilling\Metrics\Server\Stat::whereIn("id", $statIds)->where("type", "!=", \WHMCS\UsageBilling\Contracts\Metrics\MetricInterface::TYPE_SNAPSHOT)->update(array("invoice_id" => $invoiceId));
        }
    }
    public function createUsageInvoice()
    {
        $clientId = $this->service->clientId;
        createInvoices($clientId, "", "", array("serviceUsage" => array($this->service->id)));
    }
    public function hasUsageForInvoicing()
    {
        $itemFactory = new ItemFactory();
        $items = $itemFactory->factoryItemsFromService($this->service, self::getQuickViewMode());
        return (bool) count($items);
    }
    public static function isSingleHistory($mode)
    {
        return (self::MODE_VALUES_COMPACT_HIST & $mode) == self::MODE_VALUES_COMPACT_HIST;
    }
    public static function isMultiHistory($mode)
    {
        return (self::MODE_VALUES_NON_COMPACT_HIST & $mode) == self::MODE_VALUES_NON_COMPACT_HIST;
    }
    public static function isAllUsage($mode)
    {
        return (self::MODE_PERIODS_ANY & $mode) == self::MODE_PERIODS_ANY;
    }
    public static function getAllUsageMode()
    {
        return self::MODE_PERIODS_ANY | self::MODE_VALUES_NON_COMPACT_HIST;
    }
    public static function getQuickViewMode()
    {
        return self::MODE_PERIODS_CLOSED | self::MODE_VALUES_COMPACT_HIST;
    }
    public static function getRecurringInvoiceMode()
    {
        return self::MODE_PERIODS_CLOSED | self::MODE_VALUES_NON_COMPACT_HIST;
    }
}

?>