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

class Version7101release1 extends IncrementalVersion
{
    protected $updateActions = array("addWeeblyFreeProductId", "fixWeeblyFreeAddonMissingModuleConfig", "fixWeeblySettingIncludeFreeLocation", "fixWeeblyProductSortOrder", "fixWeeblyFreeStockControl");
    protected function addWeeblyFreeProductId()
    {
        $weeblyService = \WHMCS\MarketConnect\Service::where("name", "weebly")->first();
        if (!is_null($weeblyService)) {
            $productIds = $weeblyService->productIds;
            if (!in_array("weebly_free", $productIds)) {
                array_unshift($productIds, "weebly_free");
                $weeblyService->productIds = $productIds;
                $weeblyService->save();
            }
        }
        return $this;
    }
    protected function fixWeeblyFreeAddonMissingModuleConfig()
    {
        $freeName = "Weebly Website Builder - Free";
        $weeblyFreeAddon = \WHMCS\Product\Addon::where("name", $freeName)->first();
        if (!is_null($weeblyFreeAddon)) {
            $hasConfiguration = $weeblyFreeAddon->moduleConfiguration()->where("setting_name", "configoption1")->where("value", "weebly_free")->count();
            if (!$hasConfiguration) {
                $newConfig = new \WHMCS\Config\Module\ModuleConfiguration();
                $newConfig->entity_type = "addon";
                $newConfig->entity_id = $weeblyFreeAddon->id;
                $newConfig->setting_name = "configoption1";
                $newConfig->value = "weebly_free";
                $newConfig->save();
                $newConfig = new \WHMCS\Config\Module\ModuleConfiguration();
                $newConfig->entity_type = "addon";
                $newConfig->entity_id = $weeblyFreeAddon->id;
                $newConfig->setting_name = "configoption2";
                $newConfig->value = "";
                $newConfig->save();
            }
        }
    }
    protected function fixWeeblySettingIncludeFreeLocation()
    {
        $weeblyService = \WHMCS\MarketConnect\Service::whereName("weebly")->first();
        if (!is_null($weeblyService)) {
            $settings = $weeblyService->settings;
            $settingName = "include-weebly-free-by-default";
            if (array_key_exists($settingName, $settings)) {
                unset($settings[$settingName]);
            }
            if (!array_key_exists($settingName, $settings["general"])) {
                $settings["general"][$settingName] = true;
            }
            $weeblyService->settings = $settings;
            $weeblyService->save();
        }
        return $this;
    }
    protected function fixWeeblyProductSortOrder()
    {
        $sortingMap = array("weebly_free", "weebly_lite", "weebly_starter", "weebly_pro", "weebly_business");
        foreach ($sortingMap as $order => $configoption1) {
            \WHMCS\Product\Product::where("servertype", "marketconnect")->where("configoption1", $configoption1)->update(array("order" => $order + 1));
        }
        return $this;
    }
    protected function fixWeeblyFreeStockControl()
    {
        $weebly = \WHMCS\MarketConnect\Service::whereName("weebly")->first();
        if ($weebly && $weebly->status) {
            $weeblyFree = \WHMCS\Product\Product::where("servertype", "marketconnect")->where("configoption1", "weebly_free")->first();
            if ($weeblyFree) {
                $weeblyFree->stockcontrol = 0;
                $weeblyFree->save();
            }
        }
        return $this;
    }
}

?>