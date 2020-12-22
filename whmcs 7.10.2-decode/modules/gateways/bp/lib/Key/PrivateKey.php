<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\BP\Key;

class PrivateKey extends \Bitpay\PrivateKey
{
    public function setHex($hex)
    {
        $this->hex = $hex;
        $this->dec = \Bitpay\Util\Util::decodeHex($this->hex);
    }
}

?>