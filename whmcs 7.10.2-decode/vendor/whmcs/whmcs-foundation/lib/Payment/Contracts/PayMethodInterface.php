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

interface PayMethodInterface extends \WHMCS\User\Contracts\ContactAwareInterface, PayMethodTypeInterface
{
    public function payment();
    public function isDefaultPayMethod();
    public function setAsDefaultPayMethod();
    public function getDescription();
    public function setDescription($value);
    public function getGateway();
    public function setGateway(\WHMCS\Module\Gateway $value);
    public function isUsingInactiveGateway();
    public function getPaymentDescription();
    public function save(array $options);
}

?>