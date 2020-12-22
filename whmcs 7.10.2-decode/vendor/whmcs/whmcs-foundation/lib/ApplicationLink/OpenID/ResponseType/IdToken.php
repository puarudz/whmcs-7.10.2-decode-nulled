<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\ApplicationLink\OpenID\ResponseType;

class IdToken extends \OAuth2\OpenID\ResponseType\IdToken
{
    protected function encodeToken(array $token, $client_id = NULL)
    {
        $key = $this->publicKeyStorage->getKeyDetails($client_id);
        return $this->encryptionUtil->encode($token, $key["privateKey"], $key["algorithm"], $key["identifier"]);
    }
}

?>