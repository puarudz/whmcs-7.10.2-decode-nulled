<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Client;

class ProfileController
{
    public function consentHistory(\WHMCS\Http\Message\ServerRequest $request)
    {
        try {
            $client = \WHMCS\User\Client::findOrFail($request->get("client_id"));
            $body = view("admin.client.profile.consent-history", array("consentHistory" => $client->marketingConsent()->orderBy("created_at", "desc")));
        } catch (\Exception $e) {
            $body = "An error occurred: " . $e->getMessage();
        }
        return new \WHMCS\Http\Message\JsonResponse(array("body" => $body));
    }
    public function profileContacts(\WHMCS\Http\Message\ServerRequest $request)
    {
        $userId = $request->getAttribute("userId");
        redir(array("userid" => $userId), \WHMCS\Utility\Environment\WebHelper::getAdminBaseUrl() . "/clientscontacts.php");
    }
}

?>