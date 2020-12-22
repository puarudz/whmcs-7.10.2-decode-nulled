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

class FixedTermTerminations extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1600;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Process Fixed Term Terminations";
    protected $defaultName = "Fixed Term Terminations";
    protected $systemName = "FixedTermTerminations";
    protected $outputs = array("terminations" => array("defaultValue" => 0, "identifier" => "terminations", "name" => "Services Terminated"), "manual" => array("defaultValue" => 0, "identifier" => "manual", "name" => "Manual Terminations Required"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "fas fa-plug";
    protected $successCountIdentifier = "terminations";
    protected $failureCountIdentifier = "manual";
    protected $successKeyword = "Terminated";
    protected $hasDetail = true;
    public function __invoke()
    {
        $result = select_query("tblproducts", "id,autoterminatedays,autoterminateemail,servertype,name", "autoterminatedays>0", "id", "ASC");
        while ($data = mysql_fetch_array($result)) {
            list($pid, $autoterminatedays, $autoterminateemail, $module, $prodname) = $data;
            if ($autoterminateemail) {
                $autoTerminateMailTemplate = \WHMCS\Mail\Template::find($autoterminateemail);
            }
            $terminatebefore = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $autoterminatedays, date("Y")));
            $result2 = select_query("tblhosting", "tblhosting.id,userid,domain,firstname,lastname", "packageid=" . $pid . " AND regdate<='" . $terminatebefore . "' AND (domainstatus='Active' OR domainstatus='Suspended')", "id", "ASC", "", "tblclients ON tblclients.id=tblhosting.userid");
            while ($data = mysql_fetch_array($result2)) {
                list($serviceid, $userid, $domain, $firstname, $lastname) = $data;
                $moduleresult = "No Module";
                logActivity("Cron Job: Auto Terminating Fixed Term Service - Service ID: " . $serviceid);
                if ($module) {
                    $moduleresult = ServerTerminateAccount($serviceid);
                }
                if ($domain) {
                    $domain = " - " . $domain;
                }
                $loginfo = sprintf("%s%s - %s %s (Service ID: %s - User ID: %s)", $prodname, $domain, $firstname, $lastname, $serviceid, $userid);
                if ($moduleresult == "success") {
                    if ($autoterminateemail) {
                        sendMessage($autoTerminateMailTemplate, $serviceid);
                    }
                    $msg = "SUCCESS: " . $loginfo;
                    $this->addSuccess(array("service", $serviceid));
                } else {
                    $msg = "ERROR: Manual Terminate Required - " . $moduleresult . " - " . $loginfo;
                    $this->addFailure(array("service", $serviceid, $moduleresult));
                }
                logActivity("Cron Job: " . $msg);
            }
        }
        $this->output("terminations")->write(count($this->getSuccesses()));
        $this->output("manual")->write(count($this->getFailures()));
        $this->output("action.detail")->write(json_encode($this->getDetail()));
        return $this;
    }
}

?>