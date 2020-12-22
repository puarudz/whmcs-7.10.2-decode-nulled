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

class ProcessCreditCardPayments extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1540;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Process Credit Card Charges";
    protected $defaultName = "Credit Card Charges";
    protected $systemName = "ProcessCreditCardPayments";
    protected $outputs = array("captured" => array("defaultValue" => 0, "identifier" => "captured", "name" => "Captured Payments"), "failures" => array("defaultValue" => 0, "identifier" => "failures", "name" => "Failed Capture Payments"), "deleted" => array("defaultValue" => 0, "identifier" => "deleted", "name" => "Expired Credit Cards Deleted"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "fas fa-credit-card";
    protected $successCountIdentifier = "captured";
    protected $failureCountIdentifier = "failures";
    protected $successKeyword = "Captured";
    protected $failureKeyword = "Declined";
    protected $hasDetail = true;
    public function __invoke()
    {
        if (!function_exists("ccProcessing")) {
            include_once ROOTDIR . "/includes/ccfunctions.php";
        }
        ccProcessing($this);
        return $this;
    }
}

?>