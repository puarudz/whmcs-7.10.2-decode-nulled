<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Transip_Nameserver
{
    public $hostname = "";
    public $ipv4 = "";
    public $ipv6 = "";
    public function __construct($hostname, $ipv4 = "", $ipv6 = "")
    {
        $this->hostname = $hostname;
        $this->ipv4 = $ipv4;
        $this->ipv6 = $ipv6;
    }
}

?>