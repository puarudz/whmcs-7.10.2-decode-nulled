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

class SystemConfiguration extends \WHMCS\Scheduling\Task\AbstractTask
{
    protected $accessLevel = \WHMCS\Scheduling\Task\TaskInterface::ACCESS_SYSTEM;
    protected $defaultPriority = 800;
    protected $defaultFrequency = 1440;
    protected $defaultDescription = "System Configuration Check";
    protected $defaultName = "System Configuration Check";
    protected $systemName = "SystemConfiguration";
    protected $outputs = array();
    public function __invoke()
    {
        \WHMCS\Config\Setting::setValue("CronPHPVersion", \WHMCS\Environment\Php::getVersion());
        $clientStatus = $productModules = $productModules30 = $productModules90 = $productAddonModules = $productAddonModules30 = $productAddonModules90 = $domainModules = $domainModules30 = $domainModules90 = $invoicePaidLast30d = $invoiceModules = $addonModules = $enabledFraud = $fraudModuleOfOrders = $servers = $orderforms = $defaultOrderform = $clientTheme = $adminThemes = array();
        $result = @mysql_query("SELECT DISTINCT status, COUNT(id) FROM tblclients GROUP BY status ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $clientStatus[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT DISTINCT(tblproducts.servertype), COUNT(tblhosting.id) FROM tblhosting" . " INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid" . " WHERE domainstatus='Active' GROUP BY tblproducts.servertype" . " ORDER BY tblproducts.servertype ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $productModules[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT DISTINCT(tblproducts.servertype), COUNT(tblhosting.id) FROM tblhosting" . " INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid" . " WHERE domainstatus='Active' " . " AND tblhosting.regdate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)" . " GROUP BY tblproducts.servertype" . " ORDER BY tblproducts.servertype ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $productModules30[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT DISTINCT(tblproducts.servertype), COUNT(tblhosting.id) FROM tblhosting" . " INNER JOIN tblproducts ON tblproducts.id=tblhosting.packageid" . " WHERE domainstatus='Active' " . " AND tblhosting.regdate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)" . " GROUP BY tblproducts.servertype" . " ORDER BY tblproducts.servertype ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $productModules90[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT DISTINCT(tbladdons.module), COUNT(tblhostingaddons.id) FROM tblhostingaddons" . " INNER JOIN tbladdons ON tbladdons.id=tblhostingaddons.addonid" . " WHERE status='Active' GROUP BY tbladdons.module" . " ORDER BY tbladdons.module");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $productAddonModules[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT DISTINCT(tbladdons.module), COUNT(tblhostingaddons.id) FROM tblhostingaddons" . " INNER JOIN tbladdons ON tbladdons.id=tblhostingaddons.addonid" . " WHERE status='Active' " . " AND tblhostingaddons.regdate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)" . " GROUP BY tbladdons.module" . " ORDER BY tbladdons.module");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $productAddonModules30[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT DISTINCT(tbladdons.module), COUNT(tblhostingaddons.id) FROM tblhostingaddons" . " INNER JOIN tbladdons ON tbladdons.id=tblhostingaddons.addonid" . " WHERE status='Active' " . " AND tblhostingaddons.regdate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)" . " GROUP BY tbladdons.module" . " ORDER BY tbladdons.module");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $productAddonModules90[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT registrar, COUNT(id) FROM tbldomains" . " WHERE status='Active' GROUP BY registrar ORDER BY registrar ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $domainModules[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT registrar, COUNT(id) FROM tbldomains" . " WHERE status='Active' " . " AND tbldomains.registrationdate >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)" . " GROUP BY registrar ORDER BY registrar ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $domainModules30[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT registrar, COUNT(id) FROM tbldomains" . " WHERE status='Active' " . " AND tbldomains.registrationdate >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)" . " GROUP BY registrar ORDER BY registrar ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $domainModules90[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT paymentmethod, COUNT(id) FROM tblinvoices" . " WHERE status='Paid' GROUP BY paymentmethod ORDER BY paymentmethod ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $invoiceModules[$data[0]] = $data[1];
                }
            }
        }
        $isTcoInline = $tcoEnabled = false;
        if (array_key_exists("tco", $invoiceModules)) {
            $tcoEnabled = true;
            $isTcoInline = \WHMCS\Database\Capsule::table("tblpaymentgateways")->where("gateway", "tco")->where("setting", "integrationMethod")->value("value") == "inline";
        }
        $result = @mysql_query("SELECT paymentmethod, COUNT(id) FROM tblinvoices" . " WHERE status='Paid' and datepaid >= DATE_SUB(CURDATE(),INTERVAL 30 DAY) GROUP BY paymentmethod ORDER BY paymentmethod ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $invoicePaidLast30d[$data[0]] = $data[1];
                }
            }
        }
        $result = @mysql_query("SELECT module, value FROM tbladdonmodules" . " WHERE setting = 'version' ORDER BY module ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $addonModules[$data[0]] = $data[1];
                }
            }
        }
        $enabledFraud = \WHMCS\Database\Capsule::table("tblfraud")->where("setting", "Enable")->where("value", "!=", "")->value("fraud");
        if (!$enabledFraud) {
            $enabledFraud = "NONE";
        }
        $result = @mysql_query("SELECT fraudmodule, COUNT(id) FROM tblorders" . " WHERE  date >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)" . " GROUP BY fraudmodule" . " ORDER BY fraudmodule");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    if (empty($data[0])) {
                        $data[0] = "NONE";
                    }
                    $fraudModuleOfOrders[$data[0]] = $data[1];
                }
            }
        }
        $notificationModules = \WHMCS\Notification\Provider::pluck("active", "name");
        $providerKey = \WHMCS\Config\Setting::getValue("domainLookupProvider");
        if ($providerKey == "Registrar") {
            $providerKey .= "." . \WHMCS\Config\Setting::getValue("domainLookupRegistrar");
        }
        $domainLookupProvider = array($providerKey => 1);
        $result = @mysql_query("SELECT DISTINCT(type), COUNT(id) from tblservers" . " WHERE disabled != 1" . " GROUP BY type");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                if (is_array($data) && count($data) == 4) {
                    $servers[$data[0]] = $data[1];
                }
            }
        }
        $appLinks = array();
        $result = @mysql_query("SELECT module_type, module_name FROM tblapplinks" . " WHERE is_enabled = 1 ORDER BY module_type ASC, module_name ASC");
        if ($result) {
            while ($data = @mysql_fetch_array($result)) {
                $moduleType = $data["module_type"];
                $moduleName = $data["module_name"];
                $entityCount = 0;
                if ($moduleType == "servers") {
                    $entityCount = get_query_val("tblservers", "COUNT(id)", array("type" => $moduleName, "disabled" => "0"));
                }
                $appLinks[$moduleType . "_" . $moduleName] = $entityCount;
            }
        }
        $languages = array();
        $defaultLanguage = strtolower(\WHMCS\Config\Setting::getValue("Language"));
        $languages["systemDefault"] = $defaultLanguage;
        $languages["clientUsage"] = \WHMCS\Database\Capsule::table("tblclients")->groupBy("language")->orderBy("language")->pluck(\WHMCS\Database\Capsule::raw("count(language) AS cnt"), \WHMCS\Database\Capsule::raw("IF(language='', 'default', language) AS language"));
        if (!isset($languages["clientUsage"]["default"])) {
            $languages["clientUsage"]["default"] = 0;
        }
        if (isset($languages["clientUsage"][$defaultLanguage])) {
            $languages["clientUsage"]["default"] += $languages["clientUsage"][$defaultLanguage];
            unset($languages["clientUsage"][$defaultLanguage]);
        }
        $languages["clientUsage"][$defaultLanguage] = $languages["clientUsage"]["default"];
        unset($languages["clientUsage"]["default"]);
        ksort($languages["clientUsage"]);
        $languages["adminUsage"] = \WHMCS\Database\Capsule::table("tbladmins")->groupBy("language")->orderBy("language")->pluck(\WHMCS\Database\Capsule::raw("count(language) AS cnt"), \WHMCS\Database\Capsule::raw("IF(language='', 'default', language) AS language"));
        if (!isset($languages["adminUsage"]["default"])) {
            $languages["adminUsage"]["default"] = 0;
        }
        if (isset($languages["adminUsage"][$defaultLanguage])) {
            $languages["adminUsage"]["default"] += $languages["adminUsage"][$defaultLanguage];
            unset($languages["adminUsage"][$defaultLanguage]);
        }
        $languages["adminUsage"][$defaultLanguage] = $languages["adminUsage"]["default"];
        unset($languages["adminUsage"]["default"]);
        ksort($languages["adminUsage"]);
        $backupSystems = (new \WHMCS\Backups\Backups())->getActiveProviders();
        $twofa = new \WHMCS\TwoFactorAuthentication();
        $duosecurity = $totp = $yubico = false;
        if ($twoFactorCurrentSettings) {
            $twoFactorModules = array("duosecurity", "totp", "yubico");
            foreach ($twoFactorModules as $module) {
                ${$module} = $twofa->isModuleActive($module);
            }
        }
        $gracePeriods = \WHMCS\Domains\Extension::where("grace_period", ">", -1)->orWhere("grace_period_fee", ">", -1)->count();
        $redemptionPeriods = \WHMCS\Domains\Extension::where("redemption_grace_period", ">", -1)->orWhere("redemption_grace_period_fee", ">", -1)->count();
        try {
            $remoteAuth = new \WHMCS\Authentication\Remote\RemoteAuth();
            $authProviders = array();
            foreach ($remoteAuth->getEnabledProviders() as $provider) {
                $authProviders[$provider::NAME] = \WHMCS\Authentication\Remote\AccountLink::where("provider", $provider::NAME)->count();
            }
        } catch (\Exception $e) {
        }
        try {
            $dbCollationStats = $this->getDbColumnCollationStats();
        } catch (\Exception $e) {
            $dbCollationStats = array();
        }
        $captchaSettings = array("setting" => "disabled", "type" => "", "invisible" => "");
        $captchaSetting = \WHMCS\Config\Setting::getValue("CaptchaSetting");
        if ($captchaSetting) {
            $captchaType = \WHMCS\Config\Setting::getValue("CaptchaType");
            $captchaSettings["setting"] = $captchaSetting;
            $captchaSettings["type"] = $captchaType ?: "default";
        }
        $distroForms = array("boxes", "premium_comparison", "standard_cart", "universal_slider", "cloud_slider", "modern", "pure_comparison", "supreme_comparison", "ajaxcart", "cart", "comparison", "slider", "verticalsteps", "web20cart");
        $defaultOrderform = \WHMCS\Config\Setting::getValue("OrderFormTemplate");
        if (!in_array($defaultOrderform, $distroForms)) {
            $defaultOrderform = "CUSTOM";
        }
        $groupForms = \WHMCS\Database\Capsule::table("tblproductgroups")->where("hidden", "!=", 1)->groupBy("orderfrmtpl")->pluck("orderfrmtpl");
        foreach ($groupForms as $tmpl) {
            if (!$tmpl) {
                $tmpl = $defaultOrderform;
            }
            if (!in_array($tmpl, $distroForms)) {
                $defaultOrderform = "CUSTOM";
            }
            $orderforms[$tmpl] = 1;
        }
        $distroThemes = array("six", "portal", "classic", "default");
        $clientTheme = \WHMCS\Config\Setting::getValue("Template");
        if (!in_array($clientTheme, $distroThemes)) {
            $clientTheme = "CUSTOM";
        }
        $distroAdminThemes = array("blend", "v4", "original");
        $themes = \WHMCS\Database\Capsule::table("tbladmins")->where("disabled", 0)->groupBy("template")->pluck("template");
        foreach ($themes as $tmpl) {
            if (!in_array($tmpl, $distroAdminThemes)) {
                $tmpl = "CUSTOM";
            }
            $adminThemes[$tmpl] = 1;
        }
        $systemStats = array("clientStatus" => $clientStatus, "productModules" => $productModules, "productModules30" => $productModules30, "productModules90" => $productModules90, "productAddonModules" => $productAddonModules, "productAddonModules30" => $productAddonModules30, "productAddonModules90" => $productAddonModules90, "domainModules" => $domainModules, "domainModules30" => $domainModules30, "domainModules90" => $domainModules90, "invoiceModules" => $invoiceModules, "invoicePaidLast30d" => $invoicePaidLast30d, "addonModules" => $addonModules, "enabledFraudModule" => array($enabledFraud => 1), "fraudModuleOfOrders" => $fraudModuleOfOrders, "notificationModules" => $notificationModules, "domainLookupProvider" => $domainLookupProvider, "servers" => $servers, "appLinks" => $appLinks, "authProviders" => $authProviders, "defaultOrderform" => array($defaultOrderform => 1), "orderforms" => $orderforms, "clientTheme" => array($clientTheme => 1), "adminThemes" => $adminThemes, "backups" => array("ftp" => (bool) in_array("ftp", $backupSystems), "cpanel" => (bool) in_array("cpanel", $backupSystems), "email" => in_array("email", $backupSystems)), "twoFactorAuth" => array("duo" => $duosecurity, "totp" => $totp, "yubikey" => $yubico), "featureShowcase" => json_decode(\WHMCS\Config\Setting::getValue("WhatNewLinks"), true), "autoUpdate" => array("count" => (int) \WHMCS\Config\Setting::getValue("AutoUpdateCount"), "success" => (int) \WHMCS\Config\Setting::getValue("AutoUpdateCountSuccess")), "languages" => $languages, "dbCollationStats" => $dbCollationStats, "hasSslAvailable" => \App::isSSLAvailable(), "captcha" => $captchaSettings, "domainFeatures" => array("isEnabled" => (int) (!(bool) (int) \WHMCS\Config\Setting::getValue("DisableDomainGraceAndRedemptionFees")), "gracePeriods" => $gracePeriods, "redemptionPeriods" => $redemptionPeriods), "domainSyncSettings" => array("isEnabled" => (bool) \WHMCS\Config\Setting::getValue("DomainSyncEnabled"), "dueDateSyncEnabled" => (bool) \WHMCS\Config\Setting::getValue("DomainSyncNextDueDate"), "dueDateSyncDays" => (int) \WHMCS\Config\Setting::getValue("DomainSyncNextDueDateDays"), "notifyOnlyEnabled" => (bool) \WHMCS\Config\Setting::getValue("DomainSyncNotifyOnly"), "statusSyncHours" => (int) \WHMCS\Config\Setting::getValue("DomainStatusSyncFrequency"), "transferPendingSyncHours" => (int) \WHMCS\Config\Setting::getValue("DomainTransferStatusCheckFrequency")), "additionalModuleInfo" => array());
        if ($tcoEnabled) {
            $systemStats["additionalModuleInfo"]["tcoInline"] = $isTcoInline;
        }
        \WHMCS\Config\Setting::setValue("SystemStatsCache", json_encode($systemStats));
        \WHMCS\Config\Setting::setValue("ComponentStatsCache", $this->getComponentReport()->toJson());
        $this->checkPopCron();
        $this->pruneActivityLog();
        return $this;
    }
    private function getDbColumnCollationStats()
    {
        $whmcsEnv = new \WHMCS\Environment\WHMCS();
        $collationInfo = $whmcsEnv->getDbCollations();
        if (!is_array($collationInfo) || !isset($collationInfo["columns"]) || !is_array($collationInfo["columns"])) {
            return array();
        }
        $collationCounts = array();
        foreach ($collationInfo["columns"] as $column) {
            $collationCounts[$column->collation] = count(explode(",", $column->entity_names));
        }
        arsort($collationCounts);
        $stats = array("synced" => (bool) count($collationCounts) === 1, "collations" => array_keys($collationCounts), "stats" => $collationCounts);
        return $stats;
    }
    private function checkPopCron()
    {
        if (!\WHMCS\Support\Department::where("host", "!=", "")->exists()) {
            return NULL;
        }
        if (\WHMCS\TransientData::getInstance()->retrieve("popCronComplete")) {
            return NULL;
        }
        sendAdminNotification("system", "WHMCS Pop Cron Did Not Run", "<p>Your WHMCS install contains support departments that are configured to receive" . " email via POP3 Import. However, WHMCS POP cron has not completed recently.</p>" . "<p>This message will be sent daily until POP cron is restored or POP3 import is disabled " . "for all relevant support departments. " . "<a href=\"https://go.whmcs.com/1467/pop-cron-did-not-run\">Learn more</a></p>");
    }
    private function pruneActivityLog()
    {
        $activityLog = new \WHMCS\Log\Activity();
        $activityLog->prune();
    }
    public function getComponentReport()
    {
        $report = new \WHMCS\Environment\Report(\WHMCS\Environment\Report::TYPE_STATE, array(new \WHMCS\Environment\WHMCS\UsageBilling()));
        return $report;
    }
}

?>