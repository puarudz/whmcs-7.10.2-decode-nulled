<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ApplicationLink\OpenID\Claim;

class Email extends AbstractClaim
{
    public $email = NULL;
    public $email_verified = NULL;
    public function hydrate()
    {
        $user = $this->getUser();
        $this->email = $user->email;
        $this->email_verified = false;
        return $this;
    }
}

?>