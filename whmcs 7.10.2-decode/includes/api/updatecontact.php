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
    $contact = WHMCS\User\Client\Contact::with("client")->findOrFail($contactid);
    $subaccount = $contact->isSubAccount;
} catch (Exception $e) {
    $apiresults = array("result" => "error", "message" => "Contact ID Not Found");
    return NULL;
}
$email = App::getFromRequest("email");
if (($subaccount || App::getFromRequest("subaccount")) && $email) {
    $clients = WHMCS\User\Client::where("email", $email)->first();
    $contacts = WHMCS\User\Client\Contact::where("email", $email)->where("subaccount", 1)->where("id", "!=", $contact->id)->count();
    if ($clients || $contacts) {
        $apiresults = array("result" => "error", "message" => "Duplicate Email Address");
        return NULL;
    }
}
$emailPreferences = App::getFromRequest("email_preferences");
if (!$emailPreferences) {
    foreach (WHMCS\Mail\Emailer::CLIENT_EMAILS as $legacyField) {
        if (App::isInRequest($legacyField . "emails")) {
            $emailPreferences[$legacyField] = App::getFromRequest($legacyField . "emails");
        }
    }
}
if (array_key_exists(WHMCS\Mail\Emailer::EMAIL_TYPE_DOMAIN, $emailPreferences)) {
    try {
        $contact->validateEmailPreferences($emailPreferences);
    } catch (WHMCS\Exception\Validation\Required $e) {
        $apiresults = array("result" => "error", "message" => "You must have at least one email address enabled to receive" . " domain related notifications as required by ICANN." . " To disable domain notifications, please enable domain notifications" . " for the primary account holder or another contact");
        return NULL;
    } catch (Exception $e) {
        $apiresults = array("result" => "error", "message" => $e->getMessage());
        return NULL;
    }
}
if (App::isInRequest("firstname")) {
    $contact->firstName = App::getFromRequest("firstname");
}
if (App::isInRequest("lastname")) {
    $contact->lastName = App::getFromRequest("lastname");
}
if (App::isInRequest("companyname")) {
    $contact->companyName = App::getFromRequest("companyname");
}
if (App::isInRequest("email")) {
    $contact->email = App::getFromRequest("email");
}
if (App::isInRequest("address1")) {
    $contact->address1 = App::getFromRequest("address1");
}
if (App::isInRequest("address2")) {
    $contact->address2 = App::getFromRequest("address2");
}
if (App::isInRequest("city")) {
    $contact->city = App::getFromRequest("city");
}
if (App::isInRequest("state")) {
    $contact->state = App::getFromRequest("state");
}
if (App::isInRequest("postcode")) {
    $contact->postcode = App::getFromRequest("postcode");
}
if (App::isInRequest("country")) {
    $contact->country = App::getFromRequest("country");
}
if (App::isInRequest("phonenumber")) {
    $contact->phoneNumber = App::getFromRequest("phonenumber");
}
if (App::isInRequest("subaccount")) {
    $contact->isSubAccount = (int) App::getFromRequest("subaccount");
}
if (App::isInRequest("password2")) {
    $hasher = new WHMCS\Security\Hash\Password();
    $contact->passwordHash = $hasher->hash(WHMCS\Input\Sanitize::decode(App::getFromRequest("password2")));
}
if (App::isInRequest("permissions")) {
    $contact->permissions = App::getFromRequest("permissions");
}
$contact->setEmailPreferences($emailPreferences);
if ($contact->isDirty()) {
    $contact->save();
}
$apiresults = array("result" => "success", "contactid" => $contactid);

?>