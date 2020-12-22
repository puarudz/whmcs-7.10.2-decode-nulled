<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment;

class PaymentServiceProvider extends \WHMCS\Application\Support\ServiceProvider\AbstractServiceProvider
{
    public function register()
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap(array(Contracts\PayMethodTypeInterface::TYPE_BANK_ACCOUNT => "WHMCS\\Payment\\PayMethod\\Adapter\\BankAccount", Contracts\PayMethodTypeInterface::TYPE_REMOTE_BANK_ACCOUNT => "WHMCS\\Payment\\PayMethod\\Adapter\\RemoteBankAccount", Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_LOCAL => "WHMCS\\Payment\\PayMethod\\Adapter\\CreditCard", Contracts\PayMethodTypeInterface::TYPE_CREDITCARD_REMOTE_MANAGED => "WHMCS\\Payment\\PayMethod\\Adapter\\RemoteCreditCard", "Client" => "WHMCS\\User\\Client", "Contact" => "WHMCS\\User\\Client\\Contact"));
    }
}

?>