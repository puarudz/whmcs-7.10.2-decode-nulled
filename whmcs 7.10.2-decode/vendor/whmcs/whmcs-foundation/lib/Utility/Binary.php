<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Utility;

class Binary
{
    public static function strlen($binary_string)
    {
        if (function_exists("mb_strlen")) {
            return mb_strlen($binary_string, "8bit");
        }
        return strlen($binary_string);
    }
    public static function substr($binary_string, $start, $length)
    {
        if (function_exists("mb_substr")) {
            return mb_substr($binary_string, $start, $length, "8bit");
        }
        return substr($binary_string, $start, $length);
    }
}

?>