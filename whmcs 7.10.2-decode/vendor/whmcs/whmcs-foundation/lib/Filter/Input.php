<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Filter;

class Input
{
    public static function url($url)
    {
        if (function_exists("filter_var")) {
            return filter_var($url, FILTER_VALIDATE_URL);
        }
        $streamPattern = "/^[a-zA-Z0-9]+\\s?:\\s?\\//";
        if (preg_match($streamPattern, $url)) {
            return $url;
        }
        return false;
    }
}

?>