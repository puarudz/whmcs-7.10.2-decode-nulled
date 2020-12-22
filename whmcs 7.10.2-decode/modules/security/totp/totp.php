<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class MyOauth
{
    private $tokendata = "";
    public $twoFactorAuthentication = NULL;
    public function setTokenData($token)
    {
        $this->tokendata = $token;
    }
    public function getData($username)
    {
        $twofa = $this->twoFactorAuthentication;
        $tokendata = $this->tokendata ? $this->tokendata : $twofa->getUserSetting("tokendata");
        return $tokendata;
    }
    public function putData($username, $data)
    {
        $twofa = $this->twoFactorAuthentication;
        $twofa->saveUserSettings(array("tokendata" => $data));
        return true;
    }
    public function getUsers()
    {
        return false;
    }
}
function totp_config()
{
    return array("FriendlyName" => array("Type" => "System", "Value" => "Time Based Tokens"), "ShortDescription" => array("Type" => "System", "Value" => "Get codes from an app like Google Authenticator or Duo."), "Description" => array("Type" => "System", "Value" => "TOTP requires that a user enter a 6 digit code that changes every 30 seconds to complete login. This works with mobile apps such as OATH Token and Google Authenticator."));
}
function totp_activate($params)
{
    $username = $params["user_info"]["username"];
    $tokendata = isset($params["user_settings"]["tokendata"]) ? $params["user_settings"]["tokendata"] : "";
    totp_loadgaclass();
    $gaotp = new MyOauth();
    $gaotp->twoFactorAuthentication = $params["twoFactorAuthentication"];
    $username = implode(":", array(WHMCS\Config\Setting::getValue("CompanyName"), $username));
    $username = App::sanitize("a-z", $username);
    $sessionKey = WHMCS\Session::get("totpKey");
    if ($sessionKey) {
        $sessionKey = decrypt($sessionKey);
    }
    $key = $gaotp->setUser($username, "TOTP", $sessionKey);
    $url = $gaotp->createUrl($username);
    WHMCS\Session::set("totpQrUrl", encrypt($url));
    WHMCS\Session::set("totpKey", encrypt($key));
    $qrRoutePath = "account-security-two-factor-qr-code";
    if (defined("ADMINAREA")) {
        $qrRoutePath = "admin-" . $qrRoutePath;
    }
    $twoIpInstruct = sprintf(totp_getLangString("twoipinstruct", "twofa.twoipinstruct"), "<a href=\"https://itunes.apple.com/gb/app/google-authenticator/id388497605\" target=\"_blank\">" . totp_getLangString("twoipgoogleauth", "twofa.twoipgoogleauth") . "</a>", "<a href=\"https://itunes.apple.com/gb/app/duo-mobile/id422663827\" target=\"_blank\">" . totp_getLangString("twoipduo", "twofa.twoipduo") . "</a>");
    $twoIpConnect = totp_getLangString("twoipconnect", "twofa.twoipconnect");
    $twoIpVerificationStepMsg = totp_getLangString("twoipverificationstepmsg", "twofa.twoipverificationstepmsg");
    $twoIpEnterAuth = totp_getLangString("twoipenterauth", "twofa.twoipenterauth");
    $twoIpSubmit = totp_getLangString("submit", "global.submit");
    $twoIpMissing = totp_getLangString("twoipgdmissing", "twofa.twoipgdmissing");
    return "<h3 style=\"margin-top:0;\">" . $twoIpConnect . "</h3>\n<p>" . $twoIpInstruct . " <strong>" . $gaotp->helperhex2b32($gaotp->getKey($username)) . "</strong></p>\n<div align=\"center\">" . (function_exists("imagecreate") ? "<img src=\"" . routePath($qrRoutePath, "totp") . "\" style=\"border: 1px solid #ccc;border-radius: 4px;margin:15px 0;\"/>" : "<em>" . $twoIpMissing . "</em>") . "</div>\n<p>" . $twoIpVerificationStepMsg . "</p>\n" . ($params["verifyError"] ? "<div class=\"alert alert-danger\">" . $params["verifyError"] . "</div>" : "") . "\n<div class=\"row\">\n    <div class=\"col-sm-8\">\n        <input type=\"text\" name=\"verifykey\" maxlength=\"6\" style=\"font-size:18px;\" class=\"form-control input-lg\" placeholder=\"" . $twoIpEnterAuth . "\" autofocus>\n    </div>\n    <div class=\"col-sm-4\">\n        <input type=\"button\" value=\"" . $twoIpSubmit . "\" class=\"btn btn-primary btn-block btn-lg\" onclick=\"dialogSubmit()\" />\n    </div>\n</div>\n<br>";
}
function totp_activateverify($params)
{
    $username = $params["user_info"]["username"];
    $tokendata = isset($params["user_settings"]["tokendata"]) ? $params["user_settings"]["tokendata"] : "";
    totp_loadgaclass();
    $gaotp = new MyOauth();
    $gaotp->twoFactorAuthentication = $params["twoFactorAuthentication"];
    $username = implode(":", array(WHMCS\Config\Setting::getValue("CompanyName"), $username));
    $username = App::sanitize("a-z", $username);
    if (!$gaotp->authenticateUser($username, App::getFromRequest("verifykey"))) {
        throw new WHMCS\Exception(totp_getLangString("twoipcodemissmatch", "twofa.twoipcodemissmatch"));
    }
    WHMCS\Session::delete("totpKey");
    return array("settings" => array("tokendata" => $tokendata));
}
function totp_challenge($params)
{
    return "<form method=\"post\" action=\"dologin.php\">\n            <div align=\"center\">\n            <input type=\"text\" name=\"key\" maxlength=\"6\" class=\"form-control input-lg\" autofocus>\n        <br/>\n            <input id=\"btnLogin\" type=\"submit\" class=\"btn btn-primary btn-block btn-lg\" value=\"" . totp_getLangString("loginbutton", "twofa.loginbutton") . "\">\n            </div>\n</form>";
}
function totp_get_used_otps()
{
    $usedotps = WHMCS\Config\Setting::getValue("TOTPUsedOTPs");
    $usedotps = $usedotps ? safe_unserialize($usedotps) : array();
    if (!is_array($usedotps)) {
        $usedotps = array();
    }
    return $usedotps;
}
function totp_verify($params)
{
    $username = $params["admin_info"]["username"];
    $tokendata = $params["admin_settings"]["tokendata"];
    $key = $params["post_vars"]["key"];
    totp_loadgaclass();
    $gaotp = new MyOauth();
    $gaotp->twoFactorAuthentication = $params["twoFactorAuthentication"];
    $gaotp->setTokenData($tokendata);
    $username = "WHMCS:" . $username;
    $usedotps = totp_get_used_otps();
    $hash = md5($username . $key);
    if (array_key_exists($hash, $usedotps)) {
        return false;
    }
    $ans = false;
    $ans = $gaotp->authenticateUser($username, $key);
    if ($ans) {
        $usedotps[$hash] = time();
        $expiretime = time() - 5 * 60;
        foreach ($usedotps as $k => $time) {
            if ($time < $expiretime) {
                unset($usedotps[$k]);
            } else {
                break;
            }
        }
        WHMCS\Config\Setting::setValue("TOTPUsedOTPs", safe_serialize($usedotps));
    }
    return $ans;
}
function totp_getqrcode()
{
    $totpQrUrl = WHMCS\Session::getAndDelete("totpQrUrl");
    if (empty($totpQrUrl)) {
        exit;
    }
    require_once ROOTDIR . "/modules/security/totp/phpqrcode.php";
    QRcode::png(decrypt($totpQrUrl), false, 6, 6);
}
function totp_loadgaclass()
{
    if (!class_exists("GoogleAuthenticator")) {
        include ROOTDIR . "/modules/security/totp/ga4php.php";
        class MyOauth extends GoogleAuthenticator
        {
            private $tokendata = "";
            public $twoFactorAuthentication = NULL;
            public function setTokenData($token)
            {
                $this->tokendata = $token;
            }
            public function getData($username)
            {
                $twofa = $this->twoFactorAuthentication;
                $tokendata = $this->tokendata ? $this->tokendata : $twofa->getUserSetting("tokendata");
                return $tokendata;
            }
            public function putData($username, $data)
            {
                $twofa = $this->twoFactorAuthentication;
                $twofa->saveUserSettings(array("tokendata" => $data));
                return true;
            }
            public function getUsers()
            {
                return false;
            }
        }
    }
}
function totp_getLangString($clientString, $adminString)
{
    if (defined("ADMINAREA")) {
        $langString = AdminLang::trans($adminString);
    } else {
        $langString = Lang::trans($clientString);
    }
    return $langString;
}

?>