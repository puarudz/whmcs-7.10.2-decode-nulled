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

interface RemoteTokenDetailsInterface
{
    public function getRemoteToken();
    public function setRemoteToken($value);
    public function createRemote();
    public function updateRemote();
    public function deleteRemote();
    public function getBillingContactParamsForRemoteCall(\WHMCS\User\Contracts\UserInterface $client, \WHMCS\User\Contracts\ContactInterface $contact);
}

?>