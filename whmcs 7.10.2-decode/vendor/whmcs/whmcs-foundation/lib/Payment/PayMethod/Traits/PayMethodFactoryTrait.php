<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\PayMethod\Traits;

trait PayMethodFactoryTrait
{
    public static function factoryPayMethod(\WHMCS\User\Contracts\UserInterface $client, \WHMCS\User\Contracts\ContactInterface $billingContact = NULL, $description = "", $isMigratingCard = false)
    {
        if (!$isMigratingCard && static::class === "WHMCS\\Payment\\PayMethod\\Adapter\\CreditCard" && !(new \WHMCS\Gateways())->isLocalCreditCardStorageEnabled(!defined("ADMINAREA"))) {
            throw new InvalidArgumentException("No Local Credit Card Payment Gateways Enabled");
        }
        $payment = new static();
        $payment->save();
        return $payment->newPayMethod($client, $billingContact, $description);
    }
    public function newPayMethod(\WHMCS\User\Contracts\UserInterface $client, \WHMCS\User\Contracts\ContactInterface $billingContact = NULL, $description = "")
    {
        $payMethod = new \WHMCS\Payment\PayMethod\Model();
        $payMethod->description = $description;
        $payMethod->order_preference = \WHMCS\Payment\PayMethod\Model::totalPayMethodsOnFile($client);
        if (!$billingContact) {
            $billingContact = $client->defaultBillingContact;
        }
        $payMethod->save();
        $payMethod->contact()->associate($billingContact);
        $payMethod->client()->associate($client);
        $payMethod->payment()->associate($this);
        $this->pay_method_id = $payMethod->id;
        $payMethod->push();
        return $payMethod;
    }
}

?>