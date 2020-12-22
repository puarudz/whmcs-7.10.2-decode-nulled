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

class AutoTerminations extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1590;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Process Overdue Terminations";
    protected $defaultName = "Overdue Terminations";
    protected $systemName = "AutoTerminations";
    protected $outputs = array("terminations" => array("defaultValue" => 0, "identifier" => "terminations", "name" => "Terminations"), "manual" => array("defaultValue" => 0, "identifier" => "manual", "name" => "Manual Termination Required"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "far fa-calendar-times";
    protected $successCountIdentifier = "terminations";
    protected $failureCountIdentifier = "manual";
    protected $successKeyword = "Terminated";
    protected $hasDetail = true;
    public function __invoke()
    {
        if (!\WHMCS\Config\Setting::getValue("AutoTermination")) {
            $this->output("terminations")->write(0);
            $this->output("success.detail")->write("{}");
            $this->output("manual")->write(0);
            $this->output("failure.detail")->write("{}");
            return $this;
        }
        $clientGroups = \WHMCS\Database\Capsule::table("tblclientgroups")->pluck("susptermexempt", "id");
        $clients = array();
        $terminatedate = \WHMCS\Carbon::today()->subDays(\WHMCS\Config\Setting::getValue("AutoTerminationDays"))->toDateString();
        $query = "SELECT * FROM tblhosting" . " WHERE (domainstatus = 'Active' OR domainstatus = 'Suspended')" . " AND billingcycle != 'Free Account'" . " AND billingcycle != 'One Time'" . " AND billingcycle != 'onetime'" . " AND nextduedate <= '" . $terminatedate . "'" . " AND tblhosting.nextduedate != '0000-00-00'" . " AND overideautosuspend != '1'" . " ORDER BY domain ASC";
        $result = full_query($query);
        while ($data = mysql_fetch_array($result)) {
            $serviceid = $data["id"];
            $userid = $data["userid"];
            $domain = $data["domain"];
            $packageid = $data["packageid"];
            $nextDueDate = $data["nextduedate"];
            if (!array_key_exists($userid, $clients)) {
                $client = \WHMCS\Database\Capsule::table("tblclients")->where("id", $userid)->first(array("firstname", "lastname", "groupid"));
                if (!$client) {
                    continue;
                }
                $clients[$userid] = array("firstname" => $client->firstname, "lastname" => $client->lastname, "groupid" => $client->groupid);
            }
            $firstname = $clients[$userid]["firstname"];
            $lastname = $clients[$userid]["lastname"];
            $groupid = $clients[$userid]["groupid"];
            $result2 = select_query("tblproducts", "tblproducts.name, tblproducts.servertype, tblhosting.nextduedate", array("tblproducts.id" => $packageid, "tblhosting.id" => $serviceid), "", "", "", "tblhosting on tblproducts.id = tblhosting.packageid");
            $data2 = mysql_fetch_array($result2);
            $prodname = $data2["name"];
            $module = $data2["servertype"];
            $nextDueDate2 = $data2["nextduedate"];
            $susptermexempt = 0;
            if ($groupid) {
                $susptermexempt = $clientGroups[$groupid];
            }
            if ($susptermexempt) {
                continue;
            }
            $serverresult = "No Module";
            logActivity("Cron Job: Terminating Service - Service ID: " . $serviceid);
            if ($module) {
                if ($nextDueDate != $nextDueDate2) {
                    continue;
                }
                $serverresult = ServerTerminateAccount($serviceid);
            }
            if ($domain) {
                $domain = " - " . $domain;
            }
            $loginfo = sprintf("%s%s - %s %s (Service ID: %s - User ID: %s)", $prodname, $domain, $firstname, $lastname, $serviceid, $userid);
            if ($serverresult == "success") {
                $this->addSuccess(array("service", $serviceid));
            } else {
                $this->addFailure(array("service", $serviceid, $serverresult));
                logActivity(sprintf("ERROR: Manual Terminate Required - %s - %s", $serverresult, $loginfo));
            }
        }
        $addons = \WHMCS\Service\Addon::whereHas("service", function ($query) {
            $query->where("overideautosuspend", "!=", 1);
        })->with("client", "productAddon", "service")->whereIn("status", array("Active", "Suspended"))->whereNotIn("billingcycle", array("Free", "Free Account", "One Time"))->where("nextduedate", "<=", $terminatedate)->where("nextduedate", "!=", "0000-00-00")->get();
        foreach ($addons as $addon) {
            if (!$addon->service) {
                continue;
            }
            $suspendTerminateExempt = 0;
            if ($addon->client->groupId) {
                $suspendTerminateExempt = $clientGroups[$addon->client->groupId];
            }
            if ($suspendTerminateExempt) {
                continue;
            }
            if ($addon->productAddon->module) {
                $automation = \WHMCS\Service\Automation\AddonAutomation::factory($addon);
                $automationResult = $automation->runAction("TerminateAccount");
                if ($automationResult) {
                    $this->addSuccess(array("addon", $addon->id));
                } else {
                    $this->addFailure(array("addon", $addon->id, $automation->getError()));
                    $logInfo = sprintf("%s - %s %s (Service ID: %d - Addon ID: %d - User ID: %d)", $addon->name ? $addon->name : $addon->productAddon->name, $addon->client->firstName, $addon->client->lastName, $addon->serviceId, $addon->id, $addon->clientId);
                    logActivity(sprintf("ERROR: Manual Terminate Required - %s - %s", $automation->getError(), $logInfo));
                }
            } else {
                $addon->status = "Terminated";
                $addon->save();
                run_hook("AddonTerminated", array("id" => $addon->id, "userid" => $addon->clientId, "serviceid" => $addon->serviceId, "addonid" => $addon->addonId));
            }
        }
        $this->output("terminations")->write(count($this->getSuccesses()));
        $this->output("manual")->write(count($this->getFailures()));
        $this->output("action.detail")->write(json_encode($this->getDetail()));
        return true;
    }
}

?>