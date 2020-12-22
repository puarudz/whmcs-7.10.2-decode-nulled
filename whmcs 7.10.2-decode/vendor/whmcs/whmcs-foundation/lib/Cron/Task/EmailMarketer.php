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

class EmailMarketer extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $defaultPriority = 1640;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "Process Email Marketer Rules";
    protected $defaultName = "Email Marketer Rules";
    protected $systemName = "EmailMarketer";
    protected $outputs = array("sent" => array("defaultValue" => 0, "identifier" => "sent", "name" => "Emails Sent"));
    protected $icon = "fas fa-envelope";
    protected $successCountIdentifier = "sent";
    protected $successKeyword = "Emails Sent";
    public function __invoke()
    {
        $emailsSent = 0;
        $emailRules = \WHMCS\Admin\Utilities\Tools\EmailMarketer::where("disable", 0)->orderBy("id")->get();
        foreach ($emailRules as $emailRule) {
            $name = $emailRule->name;
            $type = $emailRule->type;
            $settings = $emailRule->settings;
            $marketing = $emailRule->marketing;
            $clientnumdays = $settings["clientnumdays"];
            $clientsminactive = $settings["clientsminactive"];
            $clientsmaxactive = $settings["clientsmaxactive"];
            $clientemailtpl = $settings["clientemailtpl"];
            $products = $settings["products"];
            $addons = $settings["addons"];
            $prodstatus = $settings["prodstatus"];
            $billingCycles = $settings["product_cycle"];
            $prodnumdays = $settings["prodnumdays"];
            $prodfiltertype = $settings["prodfiltertype"];
            $prodexcludepid = $settings["prodexcludepid"];
            $prodexcludeaid = $settings["prodexcludeaid"];
            $prodemailtpl = $settings["prodemailtpl"];
            $query = $query1 = $emailtplid = "";
            $criteria = array();
            if ($type == "client") {
                $emailtplid = $clientemailtpl;
                $query = "SELECT id FROM tblclients";
                if (0 < $clientnumdays) {
                    $criteria[] = "datecreated='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $clientnumdays, date("Y"))) . "'";
                }
                if (strlen($clientsminactive)) {
                    $criteria[] = "(SELECT COUNT(*) FROM tblhosting WHERE tblhosting.userid=tblclients.id AND tblhosting.domainstatus='Active')>=" . (int) $clientsminactive;
                }
                if (strlen($clientsmaxactive)) {
                    $criteria[] = "(SELECT COUNT(*) FROM tblhosting WHERE tblhosting.userid=tblclients.id AND tblhosting.domainstatus='Active')<=" . (int) $clientsmaxactive;
                }
                if ($marketing) {
                    $thisCriteria = "marketing_emails_opt_in = '1'";
                    if (\WHMCS\Config\Setting::getValue("MarketingEmailConvert") != "on") {
                        $thisCriteria = "emailoptout = '0'";
                    }
                    $criteria[] = $thisCriteria;
                }
                $query .= "  WHERE " . implode(" AND ", $criteria);
            } else {
                if ($type == "product") {
                    $emailtplid = $prodemailtpl;
                    if (count($products)) {
                        $query = "SELECT id FROM tblhosting";
                        $criteria[] = "packageid IN (" . db_build_in_array($products) . ")";
                        if (0 < $prodnumdays) {
                            if (in_array($prodfiltertype, array("afterorder", "after_order"))) {
                                $criteria[] = "regdate='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))) . "'";
                            } else {
                                if (in_array($prodfiltertype, array("beforedue", "before_due"))) {
                                    $criteria[] = "nextduedate='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $prodnumdays, date("Y"))) . "'";
                                } else {
                                    if (in_array($prodfiltertype, array("afterdue", "after_due"))) {
                                        $criteria[] = "nextduedate='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))) . "'";
                                    } else {
                                        continue;
                                    }
                                }
                            }
                        }
                        if (count($prodstatus)) {
                            $criteria[] = "domainstatus IN (" . db_build_in_array($prodstatus) . ")";
                        }
                        if (count($billingCycles)) {
                            $criteria[] = "billingcycle IN (" . db_build_in_array($billingCycles) . ")";
                        }
                        if (count($prodexcludepid) && ($productExcludePidIn = db_build_in_array($prodexcludepid))) {
                            $criteria[] = "(SELECT COUNT(*) FROM tblhosting h2 WHERE h2.userid=tblhosting.userid AND h2.packageid IN (" . $productExcludePidIn . ") AND h2.domainstatus='Active' and h2.id != tblhosting.id)=0";
                        }
                        if (count($prodexcludeaid) && ($productExcludeAidIn = db_build_in_array($prodexcludeaid))) {
                            $criteria[] = "(SELECT COUNT(*) FROM tblhostingaddons WHERE tblhostingaddons.hostingid=tblhosting.id AND tblhostingaddons.addonid IN (" . $productExcludeAidIn . ") AND tblhostingaddons.status='Active')=0";
                        }
                        if ($marketing) {
                            $thisCriteria = "marketing_emails_opt_in = '1'";
                            if (\WHMCS\Config\Setting::getValue("MarketingEmailConvert") != "on") {
                                $thisCriteria = "emailoptout = '0'";
                            }
                            $criteria[] = "(SELECT COUNT(*) FROM tblclients h3 WHERE h3.id=tblhosting.userid AND h3." . $thisCriteria . ")=1";
                        }
                        $query .= " WHERE " . implode(" AND ", $criteria);
                    }
                    if (count($addons)) {
                        $criteria = array();
                        $query1 = "SELECT hostingid FROM tblhostingaddons";
                        $criteria[] = "addonid IN (" . db_build_in_array($addons) . ")";
                        if (0 < $prodnumdays) {
                            if (in_array($prodfiltertype, array("afterorder", "after_order"))) {
                                $criteria[] = "regdate='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))) . "'";
                            } else {
                                if (in_array($prodfiltertype, array("beforedue", "before_due"))) {
                                    $criteria[] = "nextduedate='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $prodnumdays, date("Y"))) . "'";
                                } else {
                                    if (in_array($prodfiltertype, array("afterdue", "after_due"))) {
                                        $criteria[] = "nextduedate='" . date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $prodnumdays, date("Y"))) . "'";
                                    } else {
                                        continue;
                                    }
                                }
                            }
                        }
                        if (count($prodstatus)) {
                            $criteria[] = "status IN (" . db_build_in_array($prodstatus) . ")";
                        }
                        if (count($billingCycles)) {
                            $criteria[] = "billingcycle IN (" . db_build_in_array($billingCycles) . ")";
                        }
                        if (count($prodexcludepid) && ($productExcludePidIn = db_build_in_array($prodexcludepid))) {
                            $criteria[] = "(SELECT COUNT(*) FROM tblhosting h2 WHERE h2.userid=(SELECT userid FROM tblhosting WHERE tblhosting.id=tblhostingaddons.hostingid) AND h2.packageid IN (" . $productExcludePidIn . ") AND h2.domainstatus='Active')=0";
                        }
                        if (count($prodexcludeaid) && ($productExcludeAidIn = db_build_in_array($prodexcludeaid))) {
                            $criteria[] = "(SELECT COUNT(*) FROM tblhostingaddons h2 WHERE h2.hostingid=tblhostingaddons.hostingid AND h2.addonid IN (" . $productExcludeAidIn . ") AND h2.status='Active' and h2.id != tblhostingaddons.id)=0";
                        }
                        if ($marketing) {
                            $thisCriteria = "marketing_emails_opt_in = '1'";
                            if (\WHMCS\Config\Setting::getValue("MarketingEmailConvert") != "on") {
                                $thisCriteria = "emailoptout = '0'";
                            }
                            $criteria[] = "(SELECT COUNT(*) FROM tblclients h3 WHERE h3.id=(SELECT userid FROM tblhosting WHERE tblhosting.id=tblhostingaddons.hostingid) AND h3." . $thisCriteria . ")=1";
                        }
                        $query1 .= " WHERE " . implode(" AND ", $criteria);
                    }
                }
            }
            $mailTemplate = \WHMCS\Mail\Template::find($emailtplid);
            if ($query) {
                for ($result2 = full_query($query); $data = mysql_fetch_array($result2); $emailsSent++) {
                    $id = $data[0];
                    sendMessage($mailTemplate, $id);
                }
            }
            if ($query1) {
                for ($result2 = full_query($query1); $data = mysql_fetch_array($result2); $emailsSent++) {
                    $id = $data[0];
                    sendMessage($mailTemplate, $id);
                }
            }
        }
        $this->output("sent")->write($emailsSent);
        return $this;
    }
}

?>