<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Transip_Forward
{
    public $domainName = NULL;
    public $forwardTo = NULL;
    public $forwardMethod = NULL;
    public $frameTitle = NULL;
    public $frameIcon = NULL;
    public $forwardEverything = NULL;
    public $forwardSubdomains = NULL;
    public $forwardEmailTo = NULL;
    const FORWARDMETHOD_DIRECT = "direct";
    const FORWARDMETHOD_FRAME = "frame";
    public function __construct($domainName, $forwardTo, $forwardMethod = "direct", $frameTitle = "", $frameIcon = "", $forwardEveryThing = true, $forwardSubdomains = "", $forwardEmailTo = "")
    {
        $this->domainName = $domainName;
        $this->forwardTo = $forwardTo;
        $this->forwardMethod = $forwardMethod;
        $this->frameTitle = $frameTitle;
        $this->frameIcon = $frameIcon;
        $this->forwardEveryThing = $forwardEveryThing;
        $this->forwardSubdomains = $forwardSubdomains;
        $this->forwardEmailTo = $forwardEmailTo;
    }
}

?>