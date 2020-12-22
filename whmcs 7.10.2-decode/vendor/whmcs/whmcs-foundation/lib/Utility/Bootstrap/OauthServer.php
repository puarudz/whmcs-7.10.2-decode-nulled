<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Utility\Bootstrap;

class OauthServer extends Application
{
    public static function boot(\WHMCS\Config\RuntimeStorage $preBootInstances = NULL)
    {
        parent::boot($preBootInstances);
        \Di::make("app");
    }
}

?>