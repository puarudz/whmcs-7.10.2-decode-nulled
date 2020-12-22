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
if (!function_exists("getClientsDetails")) {
    require ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "clientfunctions.php";
}
$id = (int) App::getFromRequest("id");
$email = trim(App::getFromRequest("email"));
if ($id) {
    $email = WHMCS\Database\Capsule::table("tblclients")->where("status", "!=", "Closed")->where("id", $id)->value("email");
}
if (!$email) {
    if ($id) {
        $apiresults = array("result" => "error", "message" => "Client ID Not Found");
    } else {
        $apiresults = array("result" => "error", "message" => "Please enter the email address or provide the id");
    }
} else {
    (new WHMCS\Authentication\PasswordReset())->sendPasswordResetEmail($email);
    $apiresults = array("result" => "success", "email" => $email);
}

?>