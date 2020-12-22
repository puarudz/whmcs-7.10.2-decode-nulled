<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Search;

class IntelligentSearchAutoSearch
{
    const SESSION_STORAGE_NAME = "intelligentSearchAutoSearch";
    public static function isEnabled()
    {
        if (\WHMCS\Session::exists(self::SESSION_STORAGE_NAME)) {
            return (bool) \WHMCS\Session::get(self::SESSION_STORAGE_NAME);
        }
        return true;
    }
    public static function setStatus($enabled)
    {
        \WHMCS\Session::set(self::SESSION_STORAGE_NAME, (bool) $enabled);
    }
}

?>