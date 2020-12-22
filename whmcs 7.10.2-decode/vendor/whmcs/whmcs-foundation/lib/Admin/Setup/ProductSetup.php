<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Setup;

class ProductSetup
{
    protected $product = NULL;
    protected $moduleInterface = NULL;
    protected $mode = NULL;
    protected function getProduct($productId)
    {
        if (is_null($this->product)) {
            $this->product = \WHMCS\Product\Product::findOrFail($productId);
            $this->mode = null;
        }
        return $this->product;
    }
    protected function getModuleSetupRequestMode()
    {
        if (!$this->mode) {
            $hasSimpleMode = $this->hasSimpleConfigMode();
            if (!$hasSimpleMode) {
                $mode = "advanced";
            } else {
                $mode = \App::getFromRequest("mode");
                if (!$mode) {
                    $mode = "simple";
                }
            }
            $this->mode = $mode;
        }
        return $this->mode;
    }
    protected function getModuleInterface()
    {
        if (is_null($this->moduleInterface)) {
            $module = \App::isInRequest("module") ? \App::getFromRequest("module") : $this->product->module;
            $this->moduleInterface = new \WHMCS\Module\Server();
            if (!$this->moduleInterface->load($module)) {
                throw new \Exception("Invalid module");
            }
        }
        return $this->moduleInterface;
    }
    protected function hasSimpleConfigMode()
    {
        $moduleInterface = $this->getModuleInterface();
        if ($moduleInterface->functionExists("ConfigOptions")) {
            $configArray = $moduleInterface->call("ConfigOptions", array("producttype" => $this->product->type));
            foreach ($configArray as $values) {
                if (array_key_exists("SimpleMode", $values) && $values["SimpleMode"]) {
                    return true;
                }
            }
        }
        return false;
    }
    protected function getModuleSettingsFields()
    {
        $mode = $this->getModuleSetupRequestMode();
        $moduleInterface = $this->getModuleInterface();
        if ($moduleInterface->isMetaDataValueSet("NoEditModuleSettings") && $moduleInterface->getMetaDataValue("NoEditModuleSettings")) {
            return array();
        }
        $isSimpleModeRequest = false;
        $noServerFound = false;
        $params = array();
        if ($mode == "simple") {
            $isSimpleModeRequest = true;
            $serverId = (int) \App::getFromRequest("server");
            if (!$serverId) {
                $serverId = getServerID($moduleInterface->getLoadedModule(), \App::isInRequest("servergroup") ? \App::getFromRequest("servergroup") : $this->getProduct(\App::getFromRequest("id"))->serverGroupId);
                if (!$serverId && $moduleInterface->getMetaDataValue("RequiresServer") !== false) {
                    $noServerFound = true;
                } else {
                    $params = $moduleInterface->getServerParams($serverId);
                }
            }
        }
        $moduleInterface = $this->getModuleInterface();
        $configArray = $moduleInterface->call("ConfigOptions", array("producttype" => $this->product->type, "isAddon" => false));
        $i = 0;
        $isConfigured = false;
        foreach ($configArray as $key => &$values) {
            $i++;
            if (!array_key_exists("FriendlyName", $values)) {
                $values["FriendlyName"] = $key;
            }
            $values["Name"] = "packageconfigoption[" . $i . "]";
            $variable = "moduleConfigOption" . $i;
            $values["Value"] = \App::isInRequest($values["Name"]) ? \App::getFromRequest($values["Name"]) : $this->product->{$variable};
            if ($values["Value"] !== "") {
                $isConfigured = true;
            }
        }
        unset($values);
        $i = 0;
        $fields = array();
        foreach ($configArray as $key => $values) {
            $i++;
            if (!$isConfigured) {
                $values["Value"] = null;
            }
            if ($mode == "advanced" || $mode == "simple" && array_key_exists("SimpleMode", $values) && $values["SimpleMode"]) {
                $dynamicFetchError = null;
                $supportsFetchingValues = false;
                if (in_array($values["Type"], array("text", "dropdown", "radio")) && $isSimpleModeRequest && !empty($values["Loader"])) {
                    if ($noServerFound) {
                        $dynamicFetchError = "No server found so unable to fetch values";
                    } else {
                        $supportsFetchingValues = true;
                        try {
                            $loader = $values["Loader"];
                            $values["Options"] = $loader($params);
                            if ($values["Type"] == "text") {
                                $values["Type"] = "dropdown";
                                if ($values["Value"] && !array_key_exists($values["Value"], $values["Options"])) {
                                    $values["Options"][$values["Value"]] = ucwords($values["Value"]);
                                }
                            }
                        } catch (\WHMCS\Exception\Module\InvalidConfiguration $e) {
                            $dynamicFetchError = \AdminLang::trans("products.serverConfigurationInvalid");
                        } catch (\Exception $e) {
                            $dynamicFetchError = $e->getMessage();
                        }
                    }
                }
                $html = moduleConfigFieldOutput($values);
                if (!is_null($dynamicFetchError)) {
                    $html .= "<i id=\"errorField" . $i . "\" class=\"fas fa-exclamation-triangle icon-warning\" data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"" . $dynamicFetchError . "\"></i>";
                }
                if ($supportsFetchingValues) {
                    $html .= "<i id=\"refreshField" . $i . "\" class=\"fas fa-sync icon-refresh\" data-product-id=\"" . \App::getFromRequest("id") . "\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . \AdminLang::trans("products.refreshDynamicInfo") . "\"></i>";
                }
                $fields[$values["FriendlyName"]] = $html;
            }
        }
        return $fields;
    }
    public function getModuleSettings($productId)
    {
        $whmcs = \App::self();
        $product = $this->getProduct($productId);
        $fields = $this->getModuleSettingsFields();
        $i = 1;
        $html = "<tr>";
        foreach ($fields as $friendlyName => $fieldOutput) {
            $i++;
            $html .= "<td class=\"fieldlabel\" width=\"20%\">" . $friendlyName . "</td>" . "<td class=\"fieldarea\">" . $fieldOutput . "</td>";
            if ($i % 2 !== 0) {
                $html .= "</tr><tr>";
            }
        }
        $html .= "</tr>";
        $moduleInterface = $this->getModuleInterface();
        $enabled = \WHMCS\UsageBilling\Product\UsageItem::ofRelated($product)->ofModule($moduleInterface)->pluck("id", "metric");
        $metricProvider = $moduleInterface->call("MetricProvider");
        if ($metricProvider instanceof \WHMCS\UsageBilling\Contracts\Metrics\ProviderInterface) {
            $metrics = $metricProvider->metrics();
            $numberOfMetrics = count($metrics);
            if ($numberOfMetrics == 1) {
                $columnWidth = 12;
            } else {
                if ($numberOfMetrics == 2) {
                    $columnWidth = 6;
                } else {
                    $columnWidth = 4;
                }
            }
            $metricsHtml = "<div class=\"row\">";
            foreach ($metrics as $metric) {
                $metricsHtml .= "<div class=\"col-md-" . $columnWidth . "\">" . "<div class=\"metric\">" . "<div>" . "<span class=\"name\">" . $metric->displayName() . "</span>" . "<span class=\"toggle\">" . "<input type=\"checkbox\" class=\"metric-toggle\" data-metric=\"" . $metric->systemName() . "\"" . (isset($enabled[$metric->systemName()]) ? " checked" : "") . " ></span>" . "</div>" . "<span class=\"pricing\">" . "<a href=\"#\" class=\"btn-link open-metric-pricing\" data-metric=\"" . $metric->systemName() . "\">" . \AdminLang::trans("usagebilling.configurepricing") . "</a></span>" . "</div>" . "</div>";
            }
            $metricsHtml .= "</div>";
        }
        return array("content" => $html, "mode" => $this->mode, "metrics" => $metricsHtml);
    }
    public static function formatSubDomainValuesToEnsureLeadingDotAndUnique(array $subDomains = array())
    {
        array_walk($subDomains, function (&$value, $key) {
            if ($value && substr($value, 0, 1) != ".") {
                $value = "." . $value;
            }
        });
        return array_unique($subDomains);
    }
    protected function getUsageItem($productId, \WHMCS\Http\Message\ServerRequest $request)
    {
        $product = \WHMCS\Product\Product::find($productId);
        if (!$product) {
            throw new \WHMCS\Exception("Invalid product ID.");
        }
        $moduleInterface = $this->getModuleInterface();
        $requestMetricName = $request->get("metric", "");
        $usageItem = \WHMCS\UsageBilling\Product\UsageItem::firstOrNewByRelations($requestMetricName, $product, $moduleInterface);
        $metric = $usageItem->getModuleMetric();
        if (!$usageItem->exists) {
            $usageItem->save();
        }
        return $usageItem;
    }
    public function toggleMetric($productId, \WHMCS\Http\Message\ServerRequest $request)
    {
        check_token("WHMCS.admin.default");
        $usageItem = $this->getUsageItem($productId, $request);
        $enabledText = \App::getFromRequest("enable", null);
        if (empty($enabledText) || strtolower(trim($enabledText)) === "false") {
            $usageItem->isHidden = true;
        } else {
            $usageItem->isHidden = false;
        }
        $usageItem->save();
        if (!$usageItem->isHidden) {
            $schema = $usageItem->pricingSchema;
            if ($schema->count() < 1) {
                $usageItem->createPriceSchemaZero();
            }
        }
        return array("success" => true);
    }
    public function getMetricPricing($productId, \WHMCS\Http\Message\ServerRequest $request)
    {
        $usageItem = $this->getUsageItem($productId, $request);
        $helper = new Product\MetricPriceViewHelper();
        $html = $helper->getMetricPricingModalBody($usageItem);
        return array("body" => $html);
    }
    public function saveMetricPricing($productId, \WHMCS\Http\Message\ServerRequest $request)
    {
        check_token("WHMCS.admin.default");
        $usageItem = $this->getUsageItem($productId, $request);
        $existingBrackets = $usageItem->pricingSchema;
        $oldBracketIds = array();
        foreach ($existingBrackets as $bracket) {
            $oldBracketIds[] = $bracket->id;
        }
        $schemaType = $request->get("schemaType", \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_SIMPLE);
        $isSimple = $schemaType === \WHMCS\UsageBilling\Contracts\Pricing\PricingSchemaInterface::TYPE_SIMPLE;
        $pricingByCurrency = $request->get("pricing", array());
        $bracketFloors = $request->get("above", array());
        $pricingDetails = array();
        $minimumCycle = "monthly";
        $metric = $usageItem->getModuleMetric();
        $units = $metric->units();
        $errors = array();
        $iterationCount = 0;
        $usedBracketFloors = array();
        $included = $units->roundForType($request->get("included", 0));
        $usageItem->included = $included;
        $usageItem->save();
        foreach ($pricingByCurrency as $currencyId => $minimumTermBracketPricings) {
            $iterationCount++;
            foreach ($minimumTermBracketPricings as $bracketId => $amount) {
                if ($bracketId < 1) {
                    continue;
                }
                if (1 < $bracketId && $isSimple) {
                    break;
                }
                $nextIndex = $bracketId + 1;
                if (isset($bracketFloors[$nextIndex])) {
                    $nextStartNumber = str_replace(",", "", $bracketFloors[$nextIndex]);
                } else {
                    $nextStartNumber = null;
                }
                $startNumber = str_replace(",", "", $bracketFloors[$bracketId]);
                $floor = abs($startNumber);
                $formattedFloor = $units->roundForType($floor);
                if (!is_null($nextStartNumber)) {
                    $ceiling = abs($nextStartNumber);
                    $formattedCeiling = $units->roundForType($ceiling);
                } else {
                    $formattedCeiling = 0;
                }
                $amount = str_replace(",", "", $amount);
                $formattedAmount = number_format(round($amount, 2), 2, ".", "");
                if ($iterationCount == 1 && in_array($formattedFloor, $usedBracketFloors)) {
                    $errors["duplicate"] = \AdminLang::trans("usagebilling.duplicateerror");
                }
                $usedBracketFloors[] = $formattedFloor;
                if (!valueIsZero($formattedAmount) && strlen($formattedAmount) < strlen($amount)) {
                    $errors["price"] = \AdminLang::trans("usagebilling.precisionerror.price");
                }
                if (strlen($formattedFloor) < strlen($floor)) {
                    if ($units->type() == $units::TYPE_INT) {
                        $errors["range"] = \AdminLang::trans("usagebilling.precisionerror.rangewholenumber");
                    } else {
                        $errors["range"] = \AdminLang::trans("usagebilling.precisionerror.rangeprecision", array(":precision" => $units->formatForType(0)));
                    }
                }
                $pricingDetails[$bracketId]["price"][$currencyId][$minimumCycle] = $formattedAmount;
                $pricingDetails[$bracketId]["floor"] = $formattedFloor;
                $pricingDetails[$bracketId]["ceiling"] = $formattedCeiling;
                $pricingDetails[$bracketId]["type"] = $schemaType;
            }
        }
        if ($errors) {
            return array("error" => true, "errorMsgTitle" => "", "errorMsg" => implode("<br/>\n", $errors));
        }
        $usageItem->createPriceSchema($pricingDetails);
        \WHMCS\UsageBilling\Pricing\Product\Bracket::whereIn("id", $oldBracketIds)->each(function ($model) {
            $model->delete();
        });
        return array("dismiss" => true, "success" => true, "successMsgTitle" => "", "successMsg" => \AdminLang::trans("usagebilling.pricingsaved"));
    }
}

?>