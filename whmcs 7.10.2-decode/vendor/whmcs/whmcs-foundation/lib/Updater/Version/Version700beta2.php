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

class Version700beta2 extends IncrementalVersion
{
    protected $updateActions = array("migrateSystemSslUrl", "decodeEmailTemplates");
    public function migrateSystemSslUrl()
    {
        $systemSslUrl = trim(\WHMCS\Config\Setting::getValue("SystemSSLURL"));
        if (!empty($systemSslUrl)) {
            \WHMCS\Config\Setting::setValue("SystemURL", $systemSslUrl);
        }
        $setting = \WHMCS\Config\Setting::find("SystemSSLURL");
        if ($setting) {
            $setting->delete();
        }
        return $this;
    }
    public function decodeEmailTemplates()
    {
        $emails = \WHMCS\Mail\Template::all();
        foreach ($emails as $email) {
            $email->subject = \WHMCS\Input\Sanitize::decode($email->subject);
            $email->message = \WHMCS\Input\Sanitize::decode($email->message);
            $email->save();
        }
        return $this;
    }
}

?>