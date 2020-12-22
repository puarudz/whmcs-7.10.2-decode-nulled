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

class Version7100release1 extends IncrementalVersion
{
    protected $updateActions = array("updateEmailImagesAssetSetting", "updateMarketGooWelcomeEmail");
    protected function updateEmailImagesAssetSetting()
    {
        \WHMCS\Database\Capsule::table("tblfileassetsettings")->where("asset_type", "email images")->update(array("asset_type" => "email_images"));
        return $this;
    }
    protected function updateMarketGooWelcomeEmail()
    {
        $md5Value = "4315dc494ac2e803207685543015e718";
        $template = \WHMCS\Mail\Template::master()->where("name", "Marketgoo Welcome Email")->where("language", "")->first();
        if ($template && md5($template->message) === $md5Value) {
            $template->message = "<p>Hi {\$client_first_name},</p>\n<p>Thank you for your purchase. Your website is now being analyzed by marketgoo. You’re now ready to take the next steps to improve your search engine ranking.</p>\n<p>To login and get started straight away, or check your SEO progress at any time, simply login to our client area and follow the link to access the marketgoo dashboard.</p>\n<p><a href=\"{\$whmcs_url}clientarea.php\">{\$whmcs_url}clientarea.php</a></p>\n<p>If you have any questions or need help, please contact us by opening a <a href=\"{\$whmcs_url}submitticket.php\">support ticket</a></p>\n<p>{\$signature}</p>";
            $template->save();
        }
        return $this;
    }
}

?>