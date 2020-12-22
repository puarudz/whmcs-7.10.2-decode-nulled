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

class SitelockVPN extends AbstractService
{
    protected $name = "sitelockvpn";
    protected $friendlyName = "Sitelock VPN";
    protected $primaryIcon = "assets/img/marketconnect/sitelockvpn/logo.png";
    protected $primaryLandingPageRouteName = "store-sitelockvpn-index";
    protected $promosRequireQualifyingProducts = false;
    protected $requiresDomain = false;
    protected $productKeys = array("sitelockvpn_standard");
    protected $qualifyingProductTypes = array();
    protected $loginPanel = array("label" => "marketConnect.sitelockvpn.manageVPN", "icon" => "fa-network-wired", "image" => "assets/img/marketconnect/sitelockvpn/logo-sml.png", "color" => "pomegranate", "dropdownReplacementText" => "sitelockvpn.loginPanelText");
    protected $defaultPromotionalContent = array("imagePath" => "assets/img/marketconnect/sitelockvpn/logo.png", "headline" => "Secure Your Web Browsing", "tagline" => "High speed and secure VPN service", "features" => array("No Restrictions", "High Speed Network", "Unlimited bandwidth", "256-bit AES Encryption"), "learnMoreRoute" => "store-sitelockvpn-index", "cta" => "Buy", "ctaRoute" => "store-sitelockvpn-index");
    protected $planFeatures = array("sitelockvpn_standard" => array("noRestrictions" => "No Restrictions", "highSpeed" => "High Speed Network", "unlimited" => "Unlimited bandwidth", "encryption" => "256-bit AES Encryption"));
    public function getPlanFeatures($key)
    {
        return isset($this->planFeatures[$key]) ? $this->planFeatures[$key] : array();
    }
}

?>