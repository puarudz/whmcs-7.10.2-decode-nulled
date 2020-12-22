<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Application\Support\Facades;

class App extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "app";
    }
}

?>