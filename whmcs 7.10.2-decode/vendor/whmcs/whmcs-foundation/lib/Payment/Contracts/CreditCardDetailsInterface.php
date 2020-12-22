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

interface CreditCardDetailsInterface
{
    public function getCardNumber();
    public function setCardNumber($value);
    public function getCardCvv();
    public function setCardCvv($value);
    public function getLastFour();
    public function setLastFour($value);
    public function getMaskedCardNumber();
    public function getExpiryDate();
    public function setExpiryDate(\WHMCS\Carbon $value);
    public function getCardType();
    public function setCardType($value);
    public function getStartDate();
    public function setStartDate(\WHMCS\Carbon $value);
    public function getIssueNumber();
    public function setIssueNumber($value);
}

?>