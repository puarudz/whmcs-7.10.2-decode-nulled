<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\MarketConnect\Promotion\Service;

class Marketgoo extends AbstractService
{
    protected $name = "marketgoo";
    protected $friendlyName = "Marketgoo";
    protected $primaryIcon = "assets/img/marketconnect/marketgoo/logo.png";
    protected $primaryLandingPageRouteName = "store-marketgoo-index";
    protected $productKeys = array("marketgoo_lite", "marketgoo_pro");
    protected $qualifyingProductTypes = NULL;
    protected $loginPanel = array("label" => "marketConnect.marketgoo.manageSEO", "icon" => "fa-search", "image" => "assets/img/marketconnect/marketgoo/logo-sml.svg", "color" => "blue", "dropdownReplacementText" => "");
    protected $recommendedUpgradePaths = array("marketgoo_lite" => "marketgoo_pro");
    protected $upsells = array("marketgoo_lite" => array("marketgoo_pro"));
    protected $upsellPromoContent = array("marketgoo_pro" => array("imagePath" => "assets/img/marketconnect/marketgoo/logo-sml.svg", "headline" => "Upgrade to Marketgoo Pro", "tagline" => "Get a step-by-step plan", "features" => array("Daily scanning of up to 1000 Pages", "Track more competitors and keywords", "Daily PDF Reports", "Complete with step-by-step guide"), "learnMoreRoute" => "store-marketgoo-index", "cta" => "Upgrade to"));
    protected $defaultPromotionalContent = array("imagePath" => "assets/img/marketconnect/marketgoo/logo-sml.svg", "headline" => "Improve Website Traffic", "tagline" => "With SEO Tools from marketgoo", "features" => array("Search engine submission", "Weekly scanning of up to 50 Pages", "Track your competitors", "Monthly PDF Progress Report"), "learnMoreRoute" => "store-marketgoo-index", "cta" => "Buy", "ctaRoute" => "store-marketgoo-index");
    public function getPlanFeatures($key)
    {
        $planFeatures = $planFeatures = array("marketgoo_lite" => array("Search engine submission" => true, "Connect Google Analytics" => true, "Download SEO report as PDF" => true, "Pages scanned" => \Lang::trans("upTo", array(":num" => 50)), "Competitor tracking" => \Lang::trans("upTo", array(":num" => 2)), "Keyword tracking & optimization" => \Lang::trans("upTo", array(":num" => 5)), "Updated report & plan" => \Lang::trans("weekly"), "Custom SEO Plan" => \Lang::trans("limited"), "Monthly progress report" => true), "marketgoo_pro" => array("Search engine submission" => true, "Connect Google Analytics" => true, "Download SEO report as PDF" => true, "Pages scanned" => \Lang::trans("upTo", array(":num" => 1000)), "Competitor tracking" => \Lang::trans("upTo", array(":num" => 4)), "Keyword tracking & optimization" => \Lang::trans("upTo", array(":num" => 20)), "Updated report & plan" => \Lang::trans("daily"), "Custom SEO Plan" => \Lang::trans("store.marketgoo.completeStepByStep"), "Monthly progress report" => true));
        return isset($planFeatures[$key]) ? $planFeatures[$key] : array();
    }
    public function getFeaturesForUpgrade($key)
    {
        return $this->getPlanFeatures($key);
    }
    protected function getAddonToSelectByDefault()
    {
        if ($this->getModel()->setting("general.include-marketgoo-basic-by-default")) {
            $litePlan = \WHMCS\Config\Module\ModuleConfiguration::with("productAddon")->where("entity_type", "addon")->where("setting_name", "configoption1")->where("value", "marketgoo_lite")->get()->where("productAddon.module", "marketconnect")->first();
            return $litePlan->productAddon->id;
        }
        return null;
    }
}

?>