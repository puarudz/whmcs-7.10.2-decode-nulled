<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\PayMethod\Adapter;

class CreditCard extends CreditCardModel
{
    use \WHMCS\Payment\PayMethod\Traits\CreditCardDetailsTrait {
        getRawSensitiveData as ccGetRawSensitiveData;
    }
    private $isMigrating = false;
    public static function boot()
    {
        parent::boot();
        static::saving(function (CreditCard $model) {
            $sensitiveData = $model->getSensitiveData();
            $name = $model->getSensitiveDataAttributeName();
            $model->{$name} = $sensitiveData;
        });
    }
    public static function factoryPayMethod(\WHMCS\User\Contracts\UserInterface $client, \WHMCS\User\Contracts\ContactInterface $billingContact = NULL, $description = "", $isMigratingCard = false)
    {
        if (!$isMigratingCard && !(new \WHMCS\Gateways())->isLocalCreditCardStorageEnabled(!defined("ADMINAREA"))) {
            throw new \InvalidArgumentException("No Local Credit Card Payment Gateways Enabled");
        }
        return parent::factoryPayMethod($client, $billingContact, $description, $isMigratingCard);
    }
    protected function getRawSensitiveData()
    {
        return $this->ccGetRawSensitiveData();
    }
    public function getDisplayName()
    {
        return implode("-", array($this->card_type, $this->last_four));
    }
    public function isMigrating()
    {
        return $this->isMigrating;
    }
    public function setIsMigrating($isMigrating)
    {
        $this->isMigrating = $isMigrating;
        return $this;
    }
}

?>