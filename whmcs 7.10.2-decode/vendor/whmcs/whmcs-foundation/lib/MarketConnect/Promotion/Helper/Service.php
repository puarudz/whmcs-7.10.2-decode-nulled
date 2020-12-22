<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect\Promotion\Helper;

class Service
{
    protected $service = NULL;
    public function __construct($service)
    {
        $this->service = $service;
    }
    public function getAddonProducts()
    {
        return $this->service->addons()->marketConnect()->active()->get();
    }
    public function getAddonProductKeys()
    {
        $serviceAddonIds = $this->service->addons()->active()->pluck("addonid");
        $marketConnectAddonIds = \WHMCS\Product\Addon::where("module", "marketconnect")->pluck("id");
        return \WHMCS\Config\Module\ModuleConfiguration::where("entity_type", "addon")->whereIn("entity_id", $marketConnectAddonIds)->whereIn("entity_id", $serviceAddonIds)->where("setting_name", "configoption1")->pluck("value");
    }
    public function getProductAndAddonProductKeys()
    {
        $addonKeys = $this->getAddonProductKeys();
        $productKey = $this->service->product->configoption1;
        if ($productKey) {
            $addonKeys[] = $productKey;
        }
        return $addonKeys;
    }
    public function getActiveAddonByProductKeys($productKeys)
    {
        $serviceAddonIds = $this->service->addons()->where("status", \WHMCS\Service\Status::ACTIVE)->pluck("addonid");
        $marketConnectAddonIds = \WHMCS\Product\Addon::where("module", "marketconnect")->pluck("id");
        $entityId = \WHMCS\Config\Module\ModuleConfiguration::where("entity_type", "addon")->whereIn("entity_id", $marketConnectAddonIds)->whereIn("entity_id", $serviceAddonIds)->where("setting_name", "configoption1")->whereIn("value", $productKeys)->pluck("entity_id")->first();
        return $this->service->addons()->where("status", \WHMCS\Service\Status::ACTIVE)->where("addonid", $entityId)->first();
    }
}

?>