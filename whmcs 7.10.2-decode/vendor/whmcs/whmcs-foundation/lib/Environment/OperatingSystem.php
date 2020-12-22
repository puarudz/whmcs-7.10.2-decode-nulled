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

class OperatingSystem
{
    public static function isWindows($phpOs = PHP_OS)
    {
        return in_array($phpOs, array("Windows", "WIN32", "WINNT"));
    }
    public function isOwnedByMe($path)
    {
        return fileowner($path) == Php::getUserRunningPhp();
    }
}

?>