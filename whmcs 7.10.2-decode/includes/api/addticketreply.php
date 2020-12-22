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
if (!function_exists("saveCustomFields")) {
    require ROOTDIR . "/includes/customfieldfunctions.php";
}
if (!function_exists("AddReply")) {
    require ROOTDIR . "/includes/ticketfunctions.php";
}
$useMarkdown = stringLiteralToBool(App::get_req_var("markdown"));
$from = "";
$ticketData = WHMCS\Support\Ticket::find($ticketid);
if (!$ticketData) {
    $apiresults = array("result" => "error", "message" => "Ticket ID Not Found");
} else {
    if ($clientid) {
        $result = select_query("tblclients", "id", array("id" => $clientid));
        $data = mysql_fetch_array($result);
        if (!$data["id"]) {
            $apiresults = array("result" => "error", "message" => "Client ID Not Found");
            return NULL;
        }
        if ($contactid) {
            $result = select_query("tblcontacts", "id", array("id" => $contactid, "userid" => $clientid));
            $data = mysql_fetch_array($result);
            if (!$data["id"]) {
                $apiresults = array("result" => "error", "message" => "Contact ID Not Found");
                return NULL;
            }
        }
    } else {
        if ((!$name || !$email) && !$adminusername) {
            $apiresults = array("result" => "error", "message" => "Name and email address are required if not a client");
            return NULL;
        }
        $validEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$validEmail && !$adminusername) {
            $apiresults = array("result" => "error", "message" => "Email Address Invalid");
            return NULL;
        }
        $from = array("name" => $name, "email" => $email);
    }
    if (!$message) {
        $apiresults = array("result" => "error", "message" => "Message is required");
    } else {
        if ($status && $status !== $ticketData->status) {
            $validStatus = false;
            $ticketStatuses = WHMCS\Database\Capsule::table("tblticketstatuses")->select(array("title"))->get();
            foreach ($ticketStatuses as $ticketStatus) {
                if (strtolower($ticketStatus->title) === strtolower($status)) {
                    $status = $ticketStatus->title;
                    $validStatus = true;
                    break;
                }
            }
            if (!$validStatus) {
                $apiresults = array("result" => "error", "message" => "Invalid Ticket Status");
                return NULL;
            }
        }
        $adminusername = App::getFromRequest("adminusername");
        if ($attachment = App::getFromRequest("attachments")) {
            if (!is_array($attachment)) {
                $attachment = json_decode(base64_decode($attachment), true);
            }
            if (is_array($attachment)) {
                $attachments = saveTicketAttachmentsFromApiCall($attachment);
            }
        } else {
            $attachments = uploadTicketAttachments();
        }
        AddReply($ticketData->id, $clientid, $contactid, $message, $adminusername, $attachments, $from, $status, $noemail, true, $useMarkdown);
        if ($customfields) {
            $customfields = base64_decode($customfields);
            $customfields = safe_unserialize($customfields);
            saveCustomFields($ticketid, $customfields, "support", true);
        }
        $apiresults = array("result" => "success");
    }
}

?>