<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Route\Contracts;

interface MapInterface
{
    public function mapRoute($route);
    public function getMappedRoute($key);
    public function getMappedAttributeName();
}

?>