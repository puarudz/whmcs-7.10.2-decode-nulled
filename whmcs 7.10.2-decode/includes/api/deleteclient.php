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
$clientId = App::getFromRequest("clientid");
try {
    $client = WHMCS\User\Client::findOrFail($clientId);
    $client->deleteEntireClient();
} catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    $apiresults = array("result" => "error", "message" => "Client ID Not Found");
    return NULL;
} catch (Exception $e) {
    $apiresults = array("result" => "error", "message" => "Client Delete Failed: " . $e->getMessage());
    return NULL;
}
$apiresults = array("result" => "success", "clientid" => $clientId);

?>