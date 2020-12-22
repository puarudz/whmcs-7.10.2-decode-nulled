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
$_SESSION["adminid"] = "";
$password2 = (string) App::getFromRequest("password2");
$email = (string) App::getFromRequest("email");
$password2 = WHMCS\Input\Sanitize::decode($password2);
$authentication = new WHMCS\Authentication\Client($email, $password2);
if ($authentication->verifyFirstFactor()) {
    $user = $authentication->getUser();
    $apiresults = array("result" => "success", "userid" => $user->id);
    $contactId = 0;
    if ($user instanceof WHMCS\User\Client\Contact) {
        $apiresults["contactid"] = $user->id;
        $apiresults["userid"] = $user->clientId;
        $contactId = $user->id;
    }
    if (!$authentication->needsSecondFactorToFinalize()) {
        $apiresults["passwordhash"] = WHMCS\Authentication\Client::generateClientLoginHash($apiresults["userid"], $contactId, $user->passwordHash, $user->email);
        $apiresults["twoFactorEnabled"] = false;
    } else {
        $apiresults["twoFactorEnabled"] = true;
    }
} else {
    $apiresults = array("result" => "error", "message" => "Email or Password Invalid");
}

?>