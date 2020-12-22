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

interface PayMethodTypeInterface
{
    const TYPE_BANK_ACCOUNT = "BankAccount";
    const TYPE_REMOTE_BANK_ACCOUNT = "RemoteBankAccount";
    const TYPE_CREDITCARD_LOCAL = "CreditCard";
    const TYPE_CREDITCARD_REMOTE_MANAGED = "RemoteCreditCard";
    const TYPE_CREDITCARD_REMOTE_UNMANAGED = "PayToken";
    public function getType($instance);
    public function getTypeDescription($instance);
    public function isManageable();
    public function isCreditCard();
    public function isLocalCreditCard();
    public function isRemoteCreditCard();
    public function isBankAccount();
    public function isRemoteBankAccount();
}

?>