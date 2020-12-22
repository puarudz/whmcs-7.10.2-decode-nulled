<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$autoRegistrar = $currency = NULL;
$extension = App::getFromRequest("extension");
if (!$extension) {
    $apiresults = array("result" => "error", "message" => "Extension is required");
} else {
    if (App::isInRequest("auto_registrar")) {
        $autoRegistrar = App::getFromRequest("auto_registrar");
        if ($autoRegistrar !== "") {
            $registrar = new WHMCS\Module\Registrar();
            $activeRegistrars = $registrar->getActiveModules();
            if (count($activeRegistrars) === 0) {
                $apiresults = array("result" => "error", "message" => "No Active Registrars - auto_registrar value cannot be defined");
                return NULL;
            }
            if (!in_array($autoRegistrar, $activeRegistrars)) {
                arsort($activeRegistrars);
                $apiresults = array("result" => "error", "message" => "Invalid auto_registrar value. Must be empty or one of: " . implode(", ", $activeRegistrars));
                return NULL;
            }
        }
    }
    if ((App::isInRequest("register") || App::isInRequest("renew") || App::isInRequest("transfer") || App::isInRequest("grace_period_fee") || App::isInRequest("redemption_period_fee")) && !App::isInRequest("currency_code")) {
        $apiresults = array("result" => "error", "message" => "Variable currency_code is required when defining pricing");
    } else {
        if (App::isInRequest("currency_code")) {
            $currency = WHMCS\Billing\Currency::where("code", App::getFromRequest("currency_code"))->first();
            if (!$currency) {
                $apiresults = array("result" => "error", "message" => "Provided currency_code value does not exist. Must be one of: " . implode(", ", WHMCS\Billing\Currency::all()->pluck("code")->toArray()));
                return NULL;
            }
        }
        if (App::isInRequest("register") && !is_array(App::getFromRequest("register")) || App::isInRequest("renew") && !is_array(App::getFromRequest("renew")) || App::isInRequest("transfer") && !is_array(App::getFromRequest("transfer"))) {
            $apiresults = array("result" => "error", "message" => "Parameters register, renew and transfer must be arrays");
        } else {
            if (App::isInRequest("register") && (10 < count(App::getFromRequest("register")) || 10 < max(array_keys(App::getFromRequest("register"))))) {
                $apiresults = array("result" => "error", "message" => "The maximum register period is 10 years");
            } else {
                if (App::isInRequest("renew") && (9 < count(App::getFromRequest("renew")) || 9 < max(array_keys(App::getFromRequest("renew"))))) {
                    $apiresults = array("result" => "error", "message" => "The maximum renew period is 9 years");
                } else {
                    if (App::isInRequest("transfer") && 1 < count(App::getFromRequest("transfer"))) {
                        $apiresults = array("result" => "error", "message" => "Only one transfer period can be defined");
                    } else {
                        if (App::isInRequest("transfer")) {
                            list($transferPeriod) = array_keys(App::getFromRequest("transfer"));
                            if (10 < $transferPeriod) {
                                $apiresults = array("result" => "error", "message" => "The maximum transfer period is 10 years");
                                return NULL;
                            }
                        }
                        $group = strtolower(App::getFromRequest("group"));
                        if ($group && !in_array($group, array("new", "hot", "sale"))) {
                            $apiresults = array("result" => "error", "message" => "Invalid group parameter: " . $group . ". Should be one of HOT, NEW, SALE");
                        } else {
                            if (substr($extension, 0, 1) !== ".") {
                                $extension = "." . $extension;
                            }
                            $extensionNewlyCreated = false;
                            $extensionModel = WHMCS\Domains\Extension::firstOrNew(array("extension" => $extension));
                            $displayAfter = App::getFromRequest("display_after");
                            if ($displayAfter) {
                                if (substr($displayAfter, 0, 1) !== ".") {
                                    $displayAfter = "." . $displayAfter;
                                }
                                $order = WHMCS\Database\Capsule::table("tbldomainpricing")->where("extension", $displayAfter)->value("order");
                                if ($order) {
                                    WHMCS\Database\Capsule::table("tbldomainpricing")->where("order", ">", $order)->increment("order");
                                    $extensionModel->order = $order + 1;
                                } else {
                                    $displayAfter = "";
                                }
                            }
                            if (!$extensionModel->exists) {
                                $extensionNewlyCreated = true;
                                if (!$displayAfter) {
                                    $maxOrder = WHMCS\Database\Capsule::table("tbldomainpricing")->max("order");
                                    if (!is_numeric($maxOrder)) {
                                        $maxOrder = 0;
                                    }
                                    $extensionModel->order = $maxOrder + 1;
                                }
                            }
                            if (App::isInRequest("id_protection")) {
                                $extensionModel->supportsIdProtection = (bool) (int) App::getFromRequest("id_protection");
                            }
                            if (App::isInRequest("dns_management")) {
                                $extensionModel->supportsDnsManagement = (bool) (int) App::getFromRequest("dns_management");
                            }
                            if (App::isInRequest("email_forwarding")) {
                                $extensionModel->supportsEmailForwarding = (bool) (int) App::getFromRequest("email_forwarding");
                            }
                            if (App::isInRequest("epp_required")) {
                                $extensionModel->requiresEppCode = (bool) (int) App::getFromRequest("epp_required");
                            }
                            if (App::isInRequest("auto_registrar")) {
                                $extensionModel->autoRegistrationRegistrar = $autoRegistrar;
                            }
                            if (App::isInRequest("grace_period_days")) {
                                $graceDays = (int) App::getFromRequest("grace_period_days");
                                if ($graceDays < 0) {
                                    $graceDays = 0;
                                }
                                $extensionModel->gracePeriod = $graceDays;
                            }
                            if (App::isInRequest("grace_period_fee")) {
                                $graceFee = (double) App::getFromRequest("grace_period_fee");
                                if ($graceFee < -1) {
                                    $graceFee = -1;
                                }
                                if ($currency->id != 1 && 0 < $graceFee) {
                                    $graceFee = convertCurrency($graceFee, $currency->id, 1);
                                }
                                $extensionModel->gracePeriodFee = $graceFee;
                            }
                            if (App::isInRequest("redemption_period_days")) {
                                $redemptionDays = (int) App::getFromRequest("redemption_period_days");
                                if ($redemptionDays < 0) {
                                    $redemptionDays = 0;
                                }
                                $extensionModel->redemptionGracePeriod = $redemptionDays;
                            }
                            if (App::isInRequest("redemption_period_fee")) {
                                $redemptionFee = (double) App::getFromRequest("redemption_period_fee");
                                if ($redemptionFee < -1) {
                                    $redemptionFee = -1;
                                }
                                if ($currency->id != 1 && 0 < $redemptionFee) {
                                    $redemptionFee = convertCurrency($redemptionFee, $currency->id, 1);
                                }
                                $extensionModel->redemptionGracePeriodFee = $redemptionFee;
                            }
                            if ($extensionModel->isDirty()) {
                                if (!function_exists("logAdminActivity")) {
                                    require ROOTDIR . "/includes/adminfunctions.php";
                                }
                                if (!$extensionModel->id) {
                                    logAdminActivity("Domain Pricing Options Created: '" . $extension . "'");
                                } else {
                                    logAdminActivity("Domain Pricing Options Modified: '" . $extension . "'");
                                }
                                $extensionModel->save();
                            }
                            foreach (array("register", "renew", "transfer") as $pricingType) {
                                $minRegisterYears = NULL;
                                if (App::isInRequest($pricingType)) {
                                    $currencies = WHMCS\Billing\Currency::all();
                                    foreach ($currencies as $localCurrency) {
                                        $price = WHMCS\Domains\Extension\Pricing::firstOrNew(array("type" => "domain" . $pricingType, "relid" => $extensionModel->id, "currency" => $localCurrency->id, "tsetupfee" => 0));
                                        $price->year1 = -1;
                                        $price->year2 = -1;
                                        $price->year3 = -1;
                                        $price->year4 = -1;
                                        $price->year5 = -1;
                                        $price->year6 = -1;
                                        $price->year7 = -1;
                                        $price->year8 = -1;
                                        $price->year9 = -1;
                                        $price->year10 = -1;
                                        $transferPricingSet = false;
                                        foreach (App::getFromRequest($pricingType) as $year => $value) {
                                            $year = (int) $year;
                                            if (!$year || $year < 1 || 10 < $year) {
                                                break;
                                            }
                                            if ($pricingType == "renew" && 9 < $year) {
                                                break;
                                            }
                                            if ($pricingType == "transfer" && $transferPricingSet) {
                                                break;
                                            }
                                            if ($localCurrency->id != $currency->id && 0 < $value) {
                                                $value = convertCurrency($value, $currency->id, $localCurrency->id);
                                            }
                                            if ($value < 0) {
                                                $value = -1;
                                            }
                                            if ($pricingType == "transfer" && 0 <= $value) {
                                                $transferPricingSet = true;
                                            }
                                            $yearVar = "year" . $year;
                                            $price->{$yearVar} = $value;
                                        }
                                        if ($price->isDirty()) {
                                            $price->save();
                                        }
                                    }
                                } else {
                                    if ($extensionNewlyCreated) {
                                        $currencies = WHMCS\Billing\Currency::all();
                                        foreach ($currencies as $localCurrency) {
                                            $price = WHMCS\Domains\Extension\Pricing::firstOrNew(array("type" => "domain" . $pricingType, "relid" => $extensionModel->id, "currency" => $localCurrency->id, "tsetupfee" => 0));
                                            $price->year1 = -1;
                                            $price->year2 = -1;
                                            $price->year3 = -1;
                                            $price->year4 = -1;
                                            $price->year5 = -1;
                                            $price->year6 = -1;
                                            $price->year7 = -1;
                                            $price->year8 = -1;
                                            $price->year9 = -1;
                                            $price->year10 = -1;
                                            $price->save();
                                        }
                                    }
                                }
                            }
                            $apiresults = array("result" => "success", "extension" => $extensionModel->extension, "id" => $extensionModel->id);
                        }
                    }
                }
            }
        }
    }
}

?>