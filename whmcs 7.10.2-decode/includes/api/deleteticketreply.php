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
if (!function_exists("deleteTicket")) {
    require ROOTDIR . "/includes/ticketfunctions.php";
}
$ticketId = App::getFromRequest("ticketid");
$replyId = App::getFromRequest("replyid");
if (!$ticketId) {
    $apiresults = array("result" => "error", "message" => "Ticket ID Required");
} else {
    if (!$replyId) {
        $apiresults = array("result" => "error", "message" => "Reply ID Required");
    } else {
        $ticket = WHMCS\Database\Capsule::table("tbltickets")->find($ticketId);
        if (!$ticket) {
            $apiresults = array("result" => "error", "message" => "Ticket ID Not Found");
        } else {
            $reply = WHMCS\Database\Capsule::table("tblticketreplies")->where("tid", $ticketId)->find($replyId);
            if (!$reply) {
                $apiresults = array("result" => "error", "message" => "Reply ID Not Found");
            } else {
                try {
                    deleteTicket($ticketId, $replyId);
                    $apiresults = array("result" => "success");
                } catch (Exception $e) {
                    $apiresults = array("result" => "error", "message" => $e->getMessage());
                    return NULL;
                }
            }
        }
    }
}

?>