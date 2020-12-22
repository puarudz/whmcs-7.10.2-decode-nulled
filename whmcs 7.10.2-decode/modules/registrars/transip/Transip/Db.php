<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Transip_Db
{
    public $name = NULL;
    public $username = NULL;
    public $maxDiskUsage = NULL;
    public function __construct($name, $username = "", $maxDiskUsage = 100)
    {
        $this->name = $name;
        $this->username = $username;
        $this->maxDiskUsage = $maxDiskUsage;
    }
}

?>