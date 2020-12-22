<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Marketing;

class Consent extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblmarketing_consent";
    protected $booleans = array("optIn", "admin");
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public static function logOptIn($userId, $userIp = "")
    {
        if (empty($userIp)) {
            $userIp = \App::getRemoteIp();
        }
        $isAdmin = 0 < \WHMCS\Session::get("adminid");
        $consent = new self();
        $consent->userid = $userId;
        $consent->optIn = true;
        $consent->admin = $isAdmin;
        $consent->ipAddress = $userIp;
        return $consent->save();
    }
    public static function logOptOut($userId, $userIp = "")
    {
        if (empty($userIp)) {
            $userIp = \App::getRemoteIp();
        }
        $isAdmin = 0 < \WHMCS\Session::get("adminid");
        $consent = new self();
        $consent->userid = $userId;
        $consent->optIn = false;
        $consent->admin = $isAdmin;
        $consent->ipAddress = $userIp;
        return $consent->save();
    }
}

?>