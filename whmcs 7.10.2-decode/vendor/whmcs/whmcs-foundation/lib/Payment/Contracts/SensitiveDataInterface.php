<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\Contracts;

interface SensitiveDataInterface
{
    public function getEncryptionKey();
    public function wipeSensitiveData();
    public function getSensitiveDataAttributeName();
    public function getSensitiveProperty($property);
    public function setSensitiveProperty($property, $value);
    public function getSensitiveData();
}

?>