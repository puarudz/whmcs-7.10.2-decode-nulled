<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Api\ApplicationSupport\ApplicationLinks;

trait ClientUserTrait
{
    private $user = NULL;
    public function setClientUser($user)
    {
        if ($user instanceof \WHMCS\User\Client || $user instanceof \WHMCS\User\Client\Contact) {
            $this->user = $user;
        }
    }
    public function getClientUser()
    {
        return $this->user;
    }
}

?>