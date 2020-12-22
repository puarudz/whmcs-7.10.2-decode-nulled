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
$credentialId = (int) $whmcs->getFromRequest("credentialId");
$client = WHMCS\ApplicationLink\Client::find($credentialId);
if (is_null($client)) {
    $apiresults = array("result" => "error", "message" => "Invalid Credential ID provided.");
} else {
    $client->delete();
    $apiresults = array("result" => "success", "credentialId" => $credentialId);
}

?>