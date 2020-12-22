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
$ticketId = App::getFromRequest("ticketid");
$delete = (bool) App::getFromRequest("delete");
if (!$ticketId) {
    $apiresults = array("result" => "error", "message" => "Ticket ID Required");
} else {
    $ticket = WHMCS\Database\Capsule::table("tbltickets")->find($ticketId);
    if (!$ticket) {
        $apiresults = array("result" => "error", "message" => "Ticket ID Not Found");
    } else {
        if ($ticket->userid) {
            $apiresults = array("result" => "error", "message" => "A Client Cannot Be Blocked");
        } else {
            $email = $ticket->email;
            if (!$email) {
                $apiresults = array("result" => "error", "message" => "Missing Email Address");
            } else {
                $blockedAlready = WHMCS\Database\Capsule::table("tblticketspamfilters")->where("type", "sender")->where("content", $email)->count();
                if ($blockedAlready === 0) {
                    WHMCS\Database\Capsule::table("tblticketspamfilters")->insert(array("type" => "sender", "content" => $email));
                }
                $apiresults = array("result" => "success", "deleted" => false);
                if ($delete) {
                    if (!function_exists("deleteTicket")) {
                        require ROOTDIR . "/includes/ticketfunctions.php";
                    }
                    try {
                        deleteTicket($ticketId);
                        $apiresults["deleted"] = true;
                    } catch (Exception $e) {
                        $apiresults = array("result" => "error", "message" => $e->getMessage());
                        return NULL;
                    }
                }
            }
        }
    }
}

?>