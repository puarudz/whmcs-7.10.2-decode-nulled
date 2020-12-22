<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

define("ADMINAREA", true);
require dirname(__DIR__) . DIRECTORY_SEPARATOR . "init.php";
$aInt = new WHMCS\Admin("Configure Domain Pricing");
require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "domainfunctions.php";
$currency = getCurrency();
ob_start();
$whmcs = App::self();
$action = $whmcs->get_req_var("action");
$response = "";
if ($action == "whichDomainLookupProvider") {
    $response = array("status" => 0, "errorMsg" => "");
    try {
        $providerName = WHMCS\Domains\DomainLookup\Provider::getDomainLookupProvider();
        $registrarName = WHMCS\Domains\DomainLookup\Provider::getDomainLookupRegistrar();
        $response["status"] = 1;
        $response["domainLookupProvider"] = $providerName;
        $response["domainLookupRegistrar"] = $registrarName;
    } catch (Exception $e) {
        $response["errorMsg"] = $aInt->lang("general", "couldNotProcessRequest") . " " . $e->getMessage();
        logActivity("Error processing request: " . $e->getMessage());
    }
} else {
    if ($action == "save") {
        $response = array("status" => 0, "errorMsg" => "");
        try {
            check_token("WHMCS.admin.default");
            $providerName = $whmcs->get_req_var("domainLookupProvider");
            $registrarName = $whmcs->get_req_var("domainLookupRegistrar");
            $formName = "Registrar" . $registrarName;
            $provider = WHMCS\Domains\DomainLookup\Provider::factory($providerName, $registrarName);
            $configureStep = $whmcs->get_req_var("configureStep");
            $existingProvider = WHMCS\Config\Setting::getValue("domainLookupProvider");
            if (!$registrarName) {
                $registrarName = $formName = "WhmcsWhois";
            }
            if (!($providerSettings = $provider->getSettings())) {
                throw new WHMCS\Exception\Information(sprintf($aInt->lang("general", "domainLookupProviderHasNoSettings"), $providerName));
            }
            $userInputAllSettings = $whmcs->get_req_var("providerSettings");
            if (isset($userInputAllSettings[$formName]["suggestTlds"]) && is_array($userInputAllSettings[$formName]["suggestTlds"])) {
                $userInputAllSettings[$formName]["suggestTlds"] = implode(",", $userInputAllSettings[$formName]["suggestTlds"]);
            }
            if ($provider->getProviderName() == "WhmcsDomains") {
                $userProvidedSettings = $userInputAllSettings["WhmcsWhois"];
            } else {
                $settingKey = $providerName;
                if ($providerName == "Registrar") {
                    $settingKey = $providerName . $registrarName;
                }
                $userProvidedSettings = $userInputAllSettings[$settingKey];
            }
            WHMCS\Domains\DomainLookup\Settings::ofRegistrar($registrarName)->delete();
            foreach ($userProvidedSettings as $userProvidedSettingName => $userProvidedSettingValue) {
                $setting = new WHMCS\Domains\DomainLookup\Settings();
                $setting->registrar = $registrarName;
                $setting->setting = $userProvidedSettingName;
                $setting->value = $userProvidedSettingValue;
                $setting->save();
            }
            if ($providerName == "WhmcsWhois") {
                WHMCS\Config\Setting::setValue("PremiumDomains", 0);
                WHMCS\Config\Setting::setValue("domainLookupProvider", "WhmcsWhois");
                WHMCS\Config\Setting::setValue("domainLookupRegistrar", "");
            }
            if ($providerName == "Registrar") {
                $loggedName = $provider->getRegistrar()->getDisplayName();
            } else {
                if ($provider->getProviderName() == "WhmcsDomains") {
                    $loggedName = "WHMCS Namespinning";
                } else {
                    $loggedName = "Standard Whois";
                }
            }
            if ($providerName != $existingProvider) {
                logAdminActivity("Domain Lookup Provider Activated: '" . $loggedName . "'");
            } else {
                logAdminActivity("Domain Lookup Provider Settings Modified: '" . $loggedName . "'");
            }
            $response["status"] = 1;
            $response["statusMsg"] = AdminLang::trans("global.Success");
            $response["successMsg"] = AdminLang::trans("global.changesuccess");
            $response["successMsgTitle"] = AdminLang::trans("global.success");
            $response["dismiss"] = true;
        } catch (Exception $e) {
            $response["status"] = 0;
            $response["errorMsg"] = AdminLang::trans("general.couldNotProcessRequest") . " " . $e->getMessage();
            $response["errorMsgTitle"] = AdminLang::trans("global.error");
            logActivity("Error processing request: " . $e->getMessage());
        }
    } else {
        if ($action == "configure") {
            $providerName = "";
            $registrarName = "WhmcsWhois";
            try {
                $provider = WHMCS\Domains\DomainLookup\Provider::factory();
                $providerName = $provider->getProviderName();
                if ($providerName == "Registrar") {
                    $registrarName = $provider->getRegistrar()->getLoadedModule();
                }
                $providerSettings = $provider->getSettings();
                if (!$providerSettings) {
                    throw new WHMCS\Exception\Information(sprintf(AdminLang::trans("general.domainLookupProviderHasNoSettings"), $providerName == "Registrar" ? $provider->getRegistrar()->getDisplayName() : $providerName));
                }
                $settings = WHMCS\Domains\DomainLookup\Settings::ofRegistrar($registrarName)->pluck("value", "setting");
                $settings["suggestTlds"] = json_encode(explode(",", $settings["suggestTlds"]));
                if ($provider instanceof WHMCS\Domains\DomainLookup\Provider\Registrar) {
                    $displayName = $provider->getRegistrar()->getDisplayName();
                    $logoUrl = $provider->getRegistrar()->getLogoFilename();
                    if (!$logoUrl) {
                        $logoUrl = WHMCS\Utility\Environment\WebHelper::getBaseUrl(ROOTDIR, $_SERVER["SCRIPT_NAME"]) . "/images/spacer.gif";
                    }
                    if (!function_exists("moduleConfigFieldOutput")) {
                        require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "modulefunctions.php";
                    }
                    $fields = array();
                    foreach ($providerSettings as $name => $values) {
                        $values["Name"] = "providerSettings[Registrar" . $registrarName . "][" . $name . "]";
                        $values["Value"] = $settings[$name];
                        $fields[$values["FriendlyName"] ?: $name] = moduleConfigFieldOutput($values);
                    }
                    $form = "\n<div id=\"containerProviderSettingsEnom\">\n    <div style=\"padding:15px;text-align:center;\">\n        <img src=\"" . $logoUrl . "\" width=\"200\">\n    </div>\n    <div id=\"settingSaveStatusEnom\"></div>\n    <br/>\n    <form action=\"configdomainlookup.php\" method=\"POST\" name=\"providerSettings" . $displayName . "\" id=\"providerSettings" . $displayName . "\">" . generate_token() . "\n        <input type=\"hidden\" name=\"domainLookupProvider\" value=\"" . $providerName . "\"/>\n        <input type=\"hidden\" name=\"domainLookupRegistrar\" value=\"" . $registrarName . "\"/>\n        <input type=\"hidden\" name=\"action\" value=\"save\" />\n        <div align=\"center\">";
                    foreach ($fields as $name => $output) {
                        $form .= (string) $name . "<br />" . $output . "<br /><br />";
                    }
                    $form = substr($form, 0, strlen($form) - 4);
                    $form .= "</div>\n    </form>\n</div>";
                    $response = $form;
                } else {
                    if ($provider instanceof WHMCS\Domains\DomainLookup\Provider\WhmcsWhois) {
                        if (!function_exists("moduleConfigFieldOutput")) {
                            require_once ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "modulefunctions.php";
                        }
                        $fields = array();
                        $providerName = $provider->getProviderName();
                        foreach ($providerSettings as $name => $values) {
                            $values["Name"] = "providerSettings[WhmcsWhois][" . $name . "]";
                            $values["Value"] = $settings[$name];
                            $fields[$values["FriendlyName"] ?: $name] = moduleConfigFieldOutput($values);
                        }
                        $imgPath = (new WHMCS\View\Asset(WHMCS\Utility\Environment\WebHelper::getBaseUrl(ROOTDIR, $_SERVER["SCRIPT_NAME"])))->getImgPath();
                        if ($providerName == "WhmcsDomains") {
                            $img = $imgPath . "/lookup/whmcs-namespinning-large.png";
                        } else {
                            $img = $imgPath . "/lookup/standard-whois.png";
                        }
                        $form = "\n<div id=\"containerProviderSettingsWhmcsWhois\">\n    <div id=\"settingSaveStatusWhmcsWhois\"></div>\n\n    <div style=\"padding:15px;text-align:center;\">\n        <img src=\"" . $img . "\"/>\n    </div>\n\n    <form action=\"configdomainlookup.php\" method=\"POST\" name=\"providerSettingsWhmcsWhois\" id=\"providerSettingsWhmcsWhois\">" . generate_token() . "\n        <input type=\"hidden\" name=\"domainLookupProvider\" value=\"" . $providerName . "\"/>\n        <input type=\"hidden\" name=\"action\" value=\"save\" />\n        <input type=\"hidden\" name=\"providerSettings[WhmcsWhois][useWhmcsWhoisForSuggestions]\" value=\"on\" />\n        <div align=\"center\">";
                        foreach ($fields as $name => $output) {
                            $form .= (string) $name . "<br />" . $output . "<br /><br />";
                        }
                        $form = substr($form, 0, strlen($form) - 4);
                        $form .= "</div>\n    </form>\n</div>";
                        $response = $form;
                    } else {
                        throw new Exception("Invalid Domain Lookup Provider '" . $providerName . "'");
                    }
                }
            } catch (WHMCS\Exception\Information $e) {
                $response = "<div id=\"containerProviderSettings" . $providerName . "\" class=\"alert alert-info\" role=\"alert\">" . $e->getMessage() . "</div>";
            } catch (Exception $e) {
                logActivity("Error processing request: " . $e->getMessage());
                $response = "<div id=\"containerProviderSettings" . $providerName . "\" class=\"alert alert-danger\" role=\"alert\">" . AdminLang::trans("global.couldNotProcessRequest") . "</div>";
            }
        }
    }
}
ob_end_clean();
$aInt->jsonResponse(is_array($response) ? $response : array("body" => $response));

?>