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

class Version790alpha1 extends IncrementalVersion
{
    protected $updateActions = array("createNewSitelockVPNEmailTemplate", "updateBracketTable", "enableUsageBilling", "addUsageCollectionCronTask");
    protected function createNewSitelockVPNEmailTemplate()
    {
        $templateTitle = "SiteLock VPN Welcome Email";
        $existingTemplatesCount = \WHMCS\Mail\Template::where("name", "=", $templateTitle)->count();
        if (0 < $existingTemplatesCount) {
            return $this;
        }
        $message = "<p>Dear {\$client_name},</p>\n<p>Thank you for your purchase to secure your personal and business data. You now ready to have a secure end-to-end connection for browsing and transmitting data through the internet via SiteLock VPN.</p>\n<p>Below are a few simple steps to setup your VPN credentials and begin securely browsing the web.</p>\n<p><strong>Step 1 – Login and Setup VPN Credentials</strong></p>\n<p>To set up your account you must first access the SiteLock dashboard.  Login to our <a href=\"{\$whmcs_url}\">client area</a> and click the Login button found under the SiteLock VPN service to access the dashboard.</p>\n<p>Once you’re logged in click on ‘VPN’ to be directed to the Sitelock VPN configuration screen.</p>\n<p>Next, setup your user ID and set your password for accessing VPN.</p>\n<p><strong>Step 2 – Download and Install VPN client appStep 2 – Download and Install VPN client app</strong></p>\n<p>Once your VPN login and password has been set, you can download the VPN clients for your preferred devices.</p>\n<p>Apple iOS - <a href=\"https://itunes.apple.com/us/app/sitelock-vpn/id1446325257?ls=1&mt=8\">Download the iOS app</a></p>\n<p>Android - <a href=\"https://play.google.com/store/apps/details?id=com.sitelock.vpn.android\">Download the Android app</a></p>\n<p>macOS - <a href=\"https://s3.us-east-2.amazonaws.com/sitelock-vpn/mac/SiteLock+VPN.dmg\">Download the macOS app</a></p>\n<p>Windows - <a href=\"https://s3.us-east-2.amazonaws.com/sitelock-vpn/app/Setup_1.0.1.0.exe\">Download the Windows app</a></p>\n<p><strong>Step 3 – Login and Begin Browsing Securely</strong></p>\n<p>Once the VPN client installation is complete, log in with your user ID and password that was just setup to connect to any of the 1,100+ secure servers worldwide.</p>\n<p>Complete instructions can also be downloaded on the upper right-hand corner of your VPN configuration screen.</p>\n<p>If you have any questions, please contact us or reply to this email. Thank you for choosing our services.</p>\n<p>{\$signature}</p>";
        $template = new \WHMCS\Mail\Template();
        $template->name = $templateTitle;
        $template->subject = "Getting Started with SiteLock VPN";
        $template->message = $message;
        $template->custom = false;
        $template->attachments = array();
        $template->type = "product";
        $template->plaintext = false;
        $template->fromEmail = "";
        $template->fromName = "";
        $template->language = "";
        $template->save();
        return $this;
    }
    protected function updateBracketTable()
    {
        $bracket = new \WHMCS\UsageBilling\Pricing\Product\Bracket();
        $bracket->updateColumnsForDecimalsAndInclusive();
        return $this;
    }
    protected function enableUsageBilling()
    {
        \WHMCS\UsageBilling\MetricUsageSettings::enableCollection();
        \WHMCS\UsageBilling\MetricUsageSettings::enableInvoicing();
        return $this;
    }
    protected function addUsageCollectionCronTask()
    {
        \WHMCS\Cron\Task\TenantUsageMetrics::register();
        return $this;
    }
}

?>