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

interface PayMethodAdapterInterface extends \WHMCS\User\Contracts\ContactAwareInterface, PayMethodTypeInterface, SensitiveDataInterface
{
    public function payMethod();
    public static function factoryPayMethod(\WHMCS\User\Contracts\UserInterface $client, \WHMCS\User\Contracts\ContactInterface $billingContact, $description, $isMigratingCard);
    public function getDisplayName();
}

?>