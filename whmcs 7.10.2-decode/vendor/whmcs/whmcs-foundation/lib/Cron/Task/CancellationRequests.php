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

class CancellationRequests extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1570;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Process Cancellation Requests";
    protected $defaultName = "Cancellation Requests";
    protected $systemName = "CancellationRequests";
    protected $outputs = array("cancellations" => array("defaultValue" => 0, "identifier" => "cancellations", "name" => "Cancelled"), "manual" => array("defaultValue" => 0, "identifier" => "manual", "name" => "Manual Cancellation Required"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "fas fa-times";
    protected $successCountIdentifier = "cancellations";
    protected $failureCountIdentifier = "manual";
    protected $successKeyword = "Processed";
    protected $hasDetail = true;
    public function __invoke()
    {
        if (!\WHMCS\Config\Setting::getValue("AutoCancellationRequests")) {
            $this->output("cancellations")->write(0);
            $this->output("success.detail")->write("{}");
            $this->output("manual")->write(0);
            $this->output("failed.detail")->write("{}");
            return $this;
        }
        $terminatedate = \WHMCS\Carbon::today()->toDateString();
        $query = "SELECT * FROM tblcancelrequests" . " INNER JOIN tblhosting ON tblhosting.id = tblcancelrequests.relid" . " WHERE (domainstatus!='Terminated' AND domainstatus!='Cancelled')" . " AND (type='Immediate'" . " OR ( type='End of Billing Period' AND nextduedate<='" . $terminatedate . "' )" . ")" . " AND (tblhosting.billingcycle='Free'" . " OR tblhosting.billingcycle='Free Account'" . " OR tblhosting.nextduedate != '0000-00-00'" . ")" . " ORDER BY domain ASC";
        $result = full_query($query);
        while ($data = mysql_fetch_array($result)) {
            $id = $data["id"];
            $userid = $data["userid"];
            $domain = $data["domain"];
            $nextduedate = $data["nextduedate"];
            $packageid = $data["packageid"];
            $nextduedate = fromMySQLDate($nextduedate);
            $result2 = select_query("tblclients", "firstname,lastname", array("id" => $userid));
            $data2 = mysql_fetch_array($result2);
            $firstname = $data2["firstname"];
            $lastname = $data2["lastname"];
            $result2 = select_query("tblproducts", "name,servertype,freedomain", array("id" => $packageid));
            $data2 = mysql_fetch_array($result2);
            $prodname = $data2["name"];
            $module = $data2["servertype"];
            $freedomain = $data2["freedomain"];
            if ($freedomain) {
                $result2 = select_query("tbldomains", "id,registrationperiod", array("domain" => $domain, "recurringamount" => "0.00"));
                $data2 = mysql_fetch_array($result2);
                $domainid = $data2["id"];
                $regperiod = $data2["registrationperiod"];
                if ($domainid) {
                    $domainparts = explode(".", $domain, 2);
                    $tld = $domainparts[1];
                    getCurrency($userid);
                    $temppricelist = getTLDPriceList("." . $tld);
                    $renewprice = $temppricelist[$regperiod]["renew"];
                    update_query("tbldomains", array("recurringamount" => $renewprice), array("id" => $domainid));
                }
            }
            $serverresult = "No Module";
            if ($module) {
                $serverresult = ServerTerminateAccount($id);
            }
            $loginfo = sprintf("%s%s - %s %s (Due Date: %s)", $prodname, $domain ? " - " . $domain : "", $firstname, $lastname, $nextduedate);
            if ($serverresult == "success") {
                update_query("tblhosting", array("domainstatus" => "Cancelled"), array("id" => $id));
                $addons = \WHMCS\Service\Addon::with("productAddon")->where("hostingid", "=", $id)->whereNotIn("status", array("Cancelled", "Terminated"))->get();
                foreach ($addons as $addon) {
                    $automationResult = "";
                    $noModule = true;
                    $automation = null;
                    if ($addon->productAddon->module) {
                        $automation = \WHMCS\Service\Automation\AddonAutomation::factory($addon);
                        $automationResult = $automation->runAction("CancelAccount");
                        $noModule = false;
                    }
                    if ($noModule || $automationResult) {
                        $addon->status = "Cancelled";
                        $addon->terminationDate = \WHMCS\Carbon::now()->toDateString();
                        $addon->save();
                    } else {
                        if (!$noModule && !$automationResult) {
                            $logInfo = sprintf("%s - %s %s (Due Date: %s) - Addon ID: %d", $addon->name ?: $addon->productAddon->name, $firstname, $lastname, fromMySQLDate($addon->nextDueDate), $addon->id);
                            $msg = sprintf("ERROR: Manual Cancellation Required - %s - %s", $automation->getError(), $logInfo);
                            $this->addFailure(array("addon", $addon->id, $automation->getError()));
                            logActivity("Cron Job: " . $msg);
                        }
                    }
                    if ($noModule) {
                        run_hook("AddonCancelled", array("id" => $addon->id, "userid" => $addon->clientId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId));
                    }
                }
                $msg = "SUCCESS: " . $loginfo;
                logActivity("Cron Job: " . $msg);
                $this->addSuccess(array("service", $id));
            } else {
                $msg = sprintf("ERROR: Manual Cancellation Required - %s - %s", $serverresult, $loginfo);
                $this->addFailure(array("service", $id, $serverresult));
                logActivity("Cron Job: " . $msg);
            }
        }
        $this->output("cancellations")->write(count($this->getSuccesses()));
        $this->output("manual")->write(count($this->getFailures()));
        $this->output("action.detail")->write(json_encode($this->getDetail()));
        return $this;
    }
}

?>