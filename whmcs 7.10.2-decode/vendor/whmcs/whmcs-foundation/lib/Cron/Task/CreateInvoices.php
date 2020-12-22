<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cron\Task;

class CreateInvoices extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1520;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Generate Invoices";
    protected $defaultName = "Invoices";
    protected $systemName = "CreateInvoices";
    protected $outputs = array("invoice.created" => array("defaultValue" => 0, "identifier" => "invoice.created", "name" => "Total Invoices"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "far fa-file-alt";
    protected $successCountIdentifier = "invoice.created";
    protected $failedCountIdentifier = "";
    protected $successKeyword = "Generated";
    protected $hasDetail = true;
    public function __invoke()
    {
        $this->setDetails(array("success" => array()));
        if (!function_exists("createInvoices")) {
            include_once ROOTDIR . "/includes/processinvoices.php";
        }
        createInvoices("", "", "", "", $this);
        return $this;
    }
}

?>