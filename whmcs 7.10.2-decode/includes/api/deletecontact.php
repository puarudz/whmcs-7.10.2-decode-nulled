<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$contactid = App::getFromRequest("contactid");
try {
    $contact = WHMCS\User\Client\Contact::findOrFail($contactid);
} catch (Exception $e) {
    $apiresults = array("result" => "error", "message" => "Contact ID Not Found");
    return NULL;
}
$client = $contact->client;
$legacyClient = new WHMCS\Client($client);
$legacyClient->deleteContact($contactid);
$apiresults = array("result" => "success", "message" => $contactid);

?>