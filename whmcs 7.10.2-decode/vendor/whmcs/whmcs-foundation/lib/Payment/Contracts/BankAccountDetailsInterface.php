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

interface BankAccountDetailsInterface
{
    public function getRoutingNumber();
    public function setRoutingNumber($value);
    public function getAccountNumber();
    public function setAccountNumber($value);
    public function getBankName();
    public function setBankName($value);
    public function getAccountType();
    public function setAccountType($value);
    public function getAccountHolderName();
    public function setAccountHolderName($value);
}

?>