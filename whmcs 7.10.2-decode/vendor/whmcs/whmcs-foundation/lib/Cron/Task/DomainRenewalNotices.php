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

class DomainRenewalNotices extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1560;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Processing Domain Renewal Notices";
    protected $defaultName = "Domain Renewal Notices";
    protected $systemName = "DomainRenewalNotices";
    protected $outputs = array("sent" => array("defaultValue" => 0, "identifier" => "sent", "name" => "Renewal Notices"), "action.detail" => array("defaultValue" => "", "identifier" => "action.detail", "name" => "Action Detail"));
    protected $icon = "fas fa-globe";
    protected $successCountIdentifier = "sent";
    protected $successKeyword = "Sent";
    protected $hasDetail = true;
    public function __invoke()
    {
        $renewalTypes = array("first", "second", "third", "fourth", "fifth");
        $this->setDetails(array("first" => array(), "second" => array(), "third" => array(), "fourth" => array(), "fifth" => array(), "failed" => array()));
        if (!function_exists("RegGetRegistrantContactEmailAddress")) {
            include_once ROOTDIR . "/includes/registrarfunctions.php";
        }
        $whmcs = \DI::make("app");
        $renewalsNoticesCount = 0;
        $renewals = explode(",", $whmcs->get_config("DomainRenewalNotices"));
        foreach ($renewals as $count => $renewal) {
            if ((int) $renewal != 0) {
                $renewalDate = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + (int) $renewal, date("Y")));
                if ($renewal < -1) {
                    $status = "'Expired', 'Grace', 'Redemption'";
                    $emailToSend = "Expired Domain Notice";
                } else {
                    if ($renewal == -1) {
                        $status = "'Active'";
                        $emailToSend = "Expired Domain Notice";
                    } else {
                        $status = "'Active'";
                        $emailToSend = "Upcoming Domain Renewal Notice";
                    }
                }
                for ($result = select_query("tbldomains", "id,userid,domain,registrar,reminders", "status IN (" . $status . ") AND nextduedate='" . $renewalDate . "' AND " . "recurringamount!='0.00' AND reminders NOT LIKE '%|" . (int) $renewal . "|%'"); $data = mysql_fetch_array($result); $renewalsNoticesCount++) {
                    $params = array();
                    $params["domainid"] = $data["id"];
                    $domainParts = explode(".", $data["domain"]);
                    list($params["sld"], $params["tld"]) = $domainParts;
                    $params["registrar"] = $data["registrar"];
                    $extra = RegGetRegistrantContactEmailAddress($params);
                    $client = new \WHMCS\Client($data["userid"]);
                    $details = $client->getDetails();
                    $recipients = array();
                    $recipients[] = $details["email"];
                    if (isset($extra["registrantEmail"])) {
                        $recipients[] = $extra["registrantEmail"];
                    }
                    $emailSent = sendMessage($emailToSend, $data["id"], $extra);
                    if ($emailSent === true) {
                        update_query("tbldomains", array("reminders" => $data["reminders"] . "|" . (int) $renewal . "|"), array("id" => $data["id"]));
                        insert_query("tbldomainreminders", array("domain_id" => $data["id"], "date" => date("Y-m-d"), "recipients" => implode(",", $recipients), "type" => $count + 1, "days_before_expiry" => $renewal));
                        $this->addCustom($renewalTypes[$count], array("domain", $data["id"], ""));
                    } else {
                        if (is_string($emailSent)) {
                            $this->addCustom("failed", array("domain", $data["id"], $emailSent));
                        }
                    }
                }
            }
        }
        $this->output("sent")->write($renewalsNoticesCount);
        $this->output("action.detail")->write(json_encode($this->getDetail()));
        return $this;
    }
}

?>