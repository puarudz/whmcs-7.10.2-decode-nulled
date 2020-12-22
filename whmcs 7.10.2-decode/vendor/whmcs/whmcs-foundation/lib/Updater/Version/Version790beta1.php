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

class Version790beta1 extends IncrementalVersion
{
    public function __construct(\WHMCS\Version\SemanticVersion $version)
    {
        parent::__construct($version);
        $config = \DI::make("config");
        $adminFolder = "admin";
        if (!empty($config["customadminpath"])) {
            $adminFolder = $config["customadminpath"];
        }
        $oldWhatsNewImages = array("date-picker.png", "multiple-credit-cards.png", "server-sync-tool.png", "stripe-elements.png", "stripe-logo.png", "time-based-tokens.png");
        foreach ($oldWhatsNewImages as $oldWhatsNewImage) {
            $this->filesToRemove[] = ROOTDIR . DIRECTORY_SEPARATOR . $adminFolder . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "whatsnew" . DIRECTORY_SEPARATOR . $oldWhatsNewImage;
        }
    }
    public function getFeatureHighlights()
    {
        $utmString = "?utm_source=in-product&utm_medium=whatsnew79";
        return array(new \WHMCS\Notification\FeatureHighlight("<span>PayPal</span> Checkout", "All new enhanced PayPal checkout experience", null, "paypal-checkout.png", "Delivering a faster more streamlined checkout experience, support for payment options like Venmo & PayPal Credit, and proactive active subscription management.", "https://docs.whmcs.com/PayPal_Checkout" . $utmString, "Learn More", "configgateways.php", "Try it now"), new \WHMCS\Notification\FeatureHighlight("<span>Usage</span> Billing", "Bill for Disk & Bandwidth Usage, Number of cPanel Accounts and more...", null, "usage-billing.png", "Sell products that charge customers based on their usage. Supporting both snapshot and time based metrics, with flexible pricing and accessible to all module developers.", "https://docs.whmcs.com/Usage_Billing" . $utmString, "Learn More"), new \WHMCS\Notification\FeatureHighlight("<span>SiteLock VPN</span> from", "Secure and Easy to Use VPN Security", "marketconnect.png", "sitelock-vpn.png", "More and more people are choosing to use a VPN for greater online privacy and general security. Now you can offer it to your customers via MarketConnect.", "marketconnect.php?learnmore=sitelockvpn", "Learn More", "marketconnect.php?activate=sitelockvpn", "Start selling"), new \WHMCS\Notification\FeatureHighlight("<span>Stripe</span> ACH & SEPA Support", "Accept payments from bank accounts in the US and Europe", null, "stripe-ach-sepa.png", "ACH and SEPA allows customers to pay using their bank accounts and has the benefit of reduced fees and charges for you.", "https://docs.whmcs.com/Stripe#ACH.2FSEPA" . $utmString, "Learn More", "configgateways.php", "Try it now"), new \WHMCS\Notification\FeatureHighlight("<span>GoCardless</span> ACH Support", "Start accepting direct debit payments in the US", null, "gocardless-ach.png", "Our GoCardless integration has been updated to support the newly launched ACH Direct Debit support.", "https://docs.whmcs.com/GoCardless#ACH" . $utmString, "Learn More", "configgateways.php", "Try it now"));
    }
}

?>