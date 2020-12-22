<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Environment;

class Http
{
    public function siteIsConfiguredForSsl()
    {
        try {
            \App::getSystemSSLURLOrFail();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function siteHasVerifiedSslCert()
    {
        try {
            $url = \App::getSystemSSLURLOrFail();
            $whmcsHeaderVersion = \App::getVersion()->getMajor();
            $request = new \GuzzleHttp\Client(array("verify" => true));
            $request->get($url, array("headers" => array("User-Agent" => "WHMCS/" . $whmcsHeaderVersion)));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

?>