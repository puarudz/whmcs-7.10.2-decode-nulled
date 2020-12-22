<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Api\ApplicationSupport\ApplicationLinks\GrantType;

class ClientOtp extends \WHMCS\ApplicationLink\GrantType\SingleSignOn
{
    use \WHMCS\Api\ApplicationSupport\ApplicationLinks\ClientUserTrait;
    public function getUserId()
    {
        $uuid = "";
        $user = $this->getClientUser();
        if ($user) {
            $client = $contact = null;
            if ($user instanceof \WHMCS\User\Client\Contact) {
                $contact = $user;
                $client = $user->client;
            } else {
                if ($user instanceof \WHMCS\User\Client) {
                    $client = $user;
                }
            }
            if (!$client->isAllowedToAuthenticate() || !$client->hasSingleSignOnPermission()) {
                throw new \WHMCS\Exception("SSO authentication blocked for client " . $client->id);
            }
            $uuid = $client->uuid;
            if ($contact) {
                if (!$contact->isSubAccount) {
                    throw new \WHMCS\Exception("SSO authentication blocked for contact " . $contact->id);
                }
                $uuid .= ":" . $contact->id;
            }
        }
        return $uuid;
    }
}

?>