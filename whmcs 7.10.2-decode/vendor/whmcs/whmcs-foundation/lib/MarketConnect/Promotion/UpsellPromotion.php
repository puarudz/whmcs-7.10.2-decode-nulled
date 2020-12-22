<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect\Promotion;

class UpsellPromotion extends Promotion
{
    protected $supportsUpgrades = NULL;
    protected function serviceSupportsUpgrade()
    {
        if (is_null($this->supportsUpgrades)) {
            $service = $this->getUpsellService();
            if ($service->isService()) {
                $product = $service->product()->first();
            } else {
                $product = $service->productAddon()->first();
            }
            $promoHelper = \WHMCS\MarketConnect\MarketConnect::factoryPromotionalHelperByProductKey($product->productKey);
            $this->supportsUpgrades = $promoHelper->supportsUpgrades();
        }
        return $this->supportsUpgrades;
    }
    protected function getTargetUrl()
    {
        return $this->serviceSupportsUpgrade() ? routePath("upgrade") : parent::getTargetUrl();
    }
    protected function getInputParameters()
    {
        if ($this->serviceSupportsUpgrade()) {
            return array("isproduct" => $this->getUpsellService()->isService(), "serviceid" => $this->getUpsellService()->id);
        }
        if ($this->getUpsellService()->isAddon()) {
            $this->upsellService = $this->getUpsellService()->service()->first();
        }
        return parent::getInputParameters();
    }
}

?>