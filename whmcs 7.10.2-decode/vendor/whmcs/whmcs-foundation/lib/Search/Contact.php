<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Search;

class Contact implements SearchInterface
{
    public function search($searchTerm = NULL)
    {
        if (is_array($searchTerm)) {
            $clientId = isset($searchTerm["clientId"]) ? $searchTerm["clientId"] : null;
            $searchTerm = isset($searchTerm["searchTerm"]) ? $searchTerm["searchTerm"] : "";
        } else {
            $clientId = null;
        }
        $data = array();
        if (!is_null($searchTerm) && $clientId) {
            $client = \WHMCS\User\Client::find($clientId);
            if ($client) {
                $data = $this->fuzzySearch($searchTerm, $client);
            }
        }
        return $data;
    }
    public function fuzzySearch($searchTerm, \WHMCS\User\Client $client)
    {
        $searchResults = array();
        $matchingContacts = $client->contacts();
        if ($searchTerm) {
            $matchingContacts->where(\WHMCS\Database\Capsule::raw("CONCAT(firstname, ' ', lastname)"), "LIKE", "%" . $searchTerm . "%")->orWhere("email", "LIKE", "%" . $searchTerm . "%")->orWhere("companyname", "LIKE", "%" . $searchTerm . "%");
            if (is_numeric($searchTerm)) {
                $matchingContacts->orWhere("id", "=", (int) $searchTerm)->orWhere("id", "LIKE", "%" . (int) $searchTerm . "%");
            }
        } else {
            $matchingContacts->limit(30);
        }
        foreach ($matchingContacts->get() as $contact) {
            $searchResults[] = array("id" => $contact->id, "type" => "contact", "name" => \WHMCS\Input\Sanitize::decode($contact->fullName), "companyname" => \WHMCS\Input\Sanitize::decode($contact->companyname), "email" => \WHMCS\Input\Sanitize::decode($contact->email));
        }
        return $searchResults;
    }
}

?>