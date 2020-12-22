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
if (!function_exists("addContact")) {
    require ROOTDIR . "/includes/clientfunctions.php";
}
$clientid = (int) App::getFromRequest("clientid");
$permissions = (string) App::getFromRequest("permissions");
$password2 = (string) App::getFromRequest("password2");
$email = (string) App::getFromRequest("email");
$emailPreferences = App::getFromRequest("email_preferences");
if (!is_array($emailPreferences)) {
    $emailPreferences = array();
}
foreach (WHMCS\Mail\Emailer::CLIENT_EMAILS as $emailField) {
    if (!array_key_exists($emailField, $emailPreferences)) {
        if (App::isInRequest($emailField . "emails")) {
            $value = (int) (bool) App::getFromRequest($emailField . "emails");
        } else {
            $value = 0;
        }
    } else {
        $value = (int) (bool) App::getFromRequest("email_preferences", $emailField);
    }
    $varName = $emailField . "emails";
    ${$varName} = $value;
}
$taxId = App::getFromRequest("tax_id");
$result = select_query("tblclients", "id", array("id" => $clientid));
$data = mysql_fetch_array($result);
if (!$data[0]) {
    $apiresults = array("result" => "error", "message" => "Client ID Not Found");
    return NULL;
}
$permissions = $permissions ? explode(",", $permissions) : array();
if ($password2 || count($permissions)) {
    $result = select_query("tblclients", "id", array("email" => $email));
    $data = mysql_fetch_array($result);
    $result = select_query("tblcontacts", "id", array("email" => $email, "subaccount" => "1"));
    $data2 = mysql_fetch_array($result);
    if ($data["id"] || $data2["id"]) {
        $apiresults = array("result" => "error", "message" => "Duplicate Email Address");
        return NULL;
    }
}
if ($generalemails) {
    $generalemails = "1";
}
if ($productemails) {
    $productemails = "1";
}
if ($domainemails) {
    $domainemails = "1";
}
if ($invoiceemails) {
    $invoiceemails = "1";
}
if ($supportemails) {
    $supportemails = "1";
}
if ($affiliateemails) {
    $affiliateemails = "1";
}
$firstname = (string) App::getFromRequest("firstname");
$lastname = (string) App::getFromRequest("lastname");
$companyname = (string) App::getFromRequest("companyname");
$address1 = (string) App::getFromRequest("address1");
$address2 = (string) App::getFromRequest("address2");
$city = (string) App::getFromRequest("city");
$state = (string) App::getFromRequest("state");
$postcode = (string) App::getFromRequest("postcode");
$country = (string) App::getFromRequest("country");
$phonenumber = App::getFromRequest("phonenumber");
$contactid = addContact($clientid, $firstname, $lastname, $companyname, $email, $address1, $address2, $city, $state, $postcode, $country, $phonenumber, $password2, $permissions, $generalemails, $productemails, $domainemails, $invoiceemails, $supportemails, $affiliateemails, $taxId);
$apiresults = array("result" => "success", "contactid" => $contactid);

?>