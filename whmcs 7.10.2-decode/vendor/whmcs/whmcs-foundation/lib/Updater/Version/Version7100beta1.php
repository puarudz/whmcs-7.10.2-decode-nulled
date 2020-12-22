<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version7100beta1 extends IncrementalVersion
{
    protected $updateActions = array("updateProductIdsInMarketConnectServices", "renameSymantecProductsAndDisable2YearCycles", "renameSymantecAddonsAndDisable2YearCycles", "updateSellingPriceOfDigiCertCertificates");
    protected function updateProductIdsInMarketConnectServices()
    {
        $mcServices = \WHMCS\Database\Capsule::table("tblmarketconnect_services")->where("name", "symantec")->get();
        foreach ($mcServices as $mcService) {
            $id = $mcService->id;
            $productIds = str_replace("symantec_", "digicert_", $mcService->product_ids);
            \WHMCS\Database\Capsule::table("tblmarketconnect_services")->where("id", $id)->update(array("product_ids" => $productIds));
        }
        return $this;
    }
    protected function renameSymantecProductsAndDisable2YearCycles()
    {
        $products = \WHMCS\Product\Product::marketConnect()->where("configoption1", "like", "symantec\\_%")->get();
        $productIds = array();
        foreach ($products as $product) {
            switch ($product->moduleConfigOption1) {
                case "symantec_securesite":
                    $product->name = "Secure Site SSL";
                    $product->moduleConfigOption1 = "digicert_securesite";
                    $product->description = "Protect your website or email traffic with industrial-strength 2048-bit encryption with a Secure Site SSL Certificate.";
                    $product->save();
                    $productIds[] = $product->id;
                    break;
                case "symantec_securesitepro":
                    $product->name = "Secure Site Pro";
                    $product->moduleConfigOption1 = "digicert_securesitepro";
                    $product->description = "With Secure Site Pro SSL offer high-assurance certificate with added features for comprehensive website security.";
                    $product->save();
                    $productIds[] = $product->id;
                    break;
                case "symantec_securesiteev":
                    $product->name = "Secure Site EV SSL";
                    $product->moduleConfigOption1 = "digicert_securesiteev";
                    $product->description = "Secure Site Extended Validation (EV) SSL Certificates protect your most valuable assets–your customers and your brand–from phishing scams and online fraud.";
                    $product->save();
                    $productIds[] = $product->id;
                    break;
                case "symantec_securesiteproev":
                    $product->name = "Secure Site Pro EV";
                    $product->moduleConfigOption1 = "digicert_securesiteproev";
                    $product->description = "Secure Site Pro EV SSL is the highest authentication with extra features for comprehensive website security and robust protection against identity-targeted attacks.";
                    $product->save();
                    $productIds[] = $product->id;
                    break;
                default:
                    continue;
            }
        }
        if (0 < count($productIds)) {
            \WHMCS\Database\Capsule::table("tblpricing")->where("type", "product")->whereIn("relid", $productIds)->update(array("bsetupfee" => 0, "tsetupfee" => 0, "biennially" => -1, "triennially" => -1));
        }
        return $this;
    }
    protected function renameSymantecAddonsAndDisable2YearCycles()
    {
        $configurations = \WHMCS\Config\Module\ModuleConfiguration::with("productAddon")->where("entity_type", "addon")->where("setting_name", "configoption1")->where("value", "like", "symantec\\_%")->get();
        $addonIds = array();
        foreach ($configurations as $configuration) {
            if (!$configuration->productAddon || $configuration->productAddon->module != "marketconnect") {
                continue;
            }
            switch ($configuration->value) {
                case "symantec_securesite":
                    $configuration->productAddon->name = "SSL Certificates - Secure Site SSL";
                    $configuration->productAddon->description = "Protect your website or email traffic with industrial-strength 2048-bit encryption with a Secure Site SSL Certificate.";
                    $configuration->productAddon->save();
                    $configuration->value = "digicert_securesite";
                    $configuration->save();
                    $addonIds[] = $configuration->entityId;
                    break;
                case "symantec_securesitepro":
                    $configuration->productAddon->name = "SSL Certificates - Secure Site Pro";
                    $configuration->productAddon->description = "With Secure Site Pro SSL offer high-assurance certificate with added features for comprehensive website security.";
                    $configuration->productAddon->save();
                    $configuration->value = "digicert_securesitepro";
                    $configuration->save();
                    $addonIds[] = $configuration->entityId;
                    break;
                case "symantec_securesiteev":
                    $configuration->productAddon->name = "SSL Certificates - Secure Site EV SSL";
                    $configuration->productAddon->description = "Secure Site Extended Validation (EV) SSL Certificates protect your most valuable assets–your customers and your brand–from phishing scams and online fraud.";
                    $configuration->productAddon->save();
                    $configuration->value = "digicert_securesiteev";
                    $configuration->save();
                    $addonIds[] = $configuration->entityId;
                    break;
                case "symantec_securesiteproev":
                    $configuration->productAddon->name = "SSL Certificates - Secure Site Pro EV";
                    $configuration->productAddon->description = "Secure Site Pro EV SSL is the highest authentication with extra features for comprehensive website security and robust protection against identity-targeted attacks.";
                    $configuration->productAddon->save();
                    $configuration->value = "digicert_securesiteproev";
                    $configuration->save();
                    $addonIds[] = $configuration->entityId;
                    break;
                default:
                    continue;
            }
        }
        if (0 < count($addonIds)) {
            \WHMCS\Database\Capsule::table("tblpricing")->where("type", "addon")->whereIn("relid", $addonIds)->update(array("bsetupfee" => 0, "tsetupfee" => 0, "biennially" => -1, "triennially" => -1));
        }
        return $this;
    }
    protected function updateSellingPriceOfDigiCertCertificates()
    {
        $usdCurrency = null;
        $defaultCurrency = \WHMCS\Billing\Currency::defaultCurrency()->first();
        if ($defaultCurrency->code === "USD") {
            $usdCurrency = $defaultCurrency;
        }
        if (is_null($usdCurrency)) {
            $usdCurrency = \WHMCS\Billing\Currency::whereCode("USD")->first();
        }
        if (is_null($usdCurrency)) {
            $exchangeRates = \WHMCS\Utility\CurrencyExchange::fetchCurrentRates();
            $defaultCurrency = \WHMCS\Billing\Currency::defaultCurrency()->first();
            $usdExchangeRate = 100;
            if ($exchangeRates->hasCurrencyCode($defaultCurrency->code)) {
                $usdExchangeRate = $exchangeRates->getUsdExchangeRate($defaultCurrency->code);
            }
            $usdCurrency = new \WHMCS\Billing\Currency();
            $usdCurrency->code = "USD";
            $usdCurrency->rate = $usdExchangeRate;
        }
        $allCurrencies = \WHMCS\Billing\Currency::all();
        $products = \WHMCS\Product\Product::marketConnect()->where("configoption1", "like", "digicert\\_%")->get();
        foreach ($products as $product) {
            $newCost = null;
            switch ($product->moduleConfigOption1) {
                case "digicert_securesite":
                    $newCost = "399";
                    break;
                case "digicert_securesitepro":
                case "digicert_securesiteev":
                    $newCost = "995";
                    break;
                case "digicert_securesiteproev":
                    $newCost = "1499";
                    break;
                default:
                    continue;
            }
            foreach ($allCurrencies as $currency) {
                \WHMCS\Database\Capsule::table("tblpricing")->where("type", "product")->where("relid", $product->id)->where("currency", $currency->id)->update(array("annually" => convertCurrency($newCost, null, $currency->id, $usdCurrency->rate)));
            }
        }
        $configurations = \WHMCS\Config\Module\ModuleConfiguration::with("productAddon")->where("entity_type", "addon")->where("setting_name", "configoption1")->where("value", "like", "digicert\\_%")->get();
        foreach ($configurations as $configuration) {
            if (!$configuration->productAddon || $configuration->productAddon->module != "marketconnect") {
                continue;
            }
            $newCost = null;
            switch ($configuration->value) {
                case "digicert_securesite":
                    $newCost = "399";
                    break;
                case "digicert_securesitepro":
                case "digicert_securesiteev":
                    $newCost = "995";
                    break;
                case "digicert_securesiteproev":
                    $newCost = "1499";
                    break;
                default:
                    continue;
            }
            foreach ($allCurrencies as $currency) {
                \WHMCS\Database\Capsule::table("tblpricing")->where("type", "addon")->where("relid", $configuration->entityId)->where("currency", $currency->id)->update(array("annually" => convertCurrency($newCost, null, $currency->id, $usdCurrency->rate)));
            }
        }
        return $this;
    }
}

?>