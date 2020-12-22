<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Transip_MailForward
{
    public $name = NULL;
    public $targetAddress = NULL;
    public function __construct($name, $targetAddress)
    {
        $this->name = $name;
        $this->targetAddress = $targetAddress;
    }
}

?>