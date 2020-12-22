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
$masterTicketId = (int) App::getFromRequest("ticketid");
$mergeTicketIds = array_filter(explode(",", App::getFromRequest("mergeticketids")));
$newSubject = App::getFromRequest("newsubject");
if (!$masterTicketId) {
    $apiresults = array("result" => "error", "message" => "Ticket ID Required");
} else {
    try {
        $masterTicket = WHMCS\Support\Ticket::where("merged_ticket_id", 0)->findOrFail($masterTicketId);
    } catch (Exception $e) {
        $apiresults = array("result" => "error", "message" => "Ticket ID Invalid");
        return NULL;
    }
    if (count($mergeTicketIds) === 0) {
        $apiresults = array("result" => "error", "message" => "Merge Ticket IDs Required");
    } else {
        $invalidMergeTicketIds = array();
        foreach ($mergeTicketIds as $mergeTicketId) {
            try {
                $mergeTicket = WHMCS\Support\Ticket::findOrFail($mergeTicketId);
            } catch (Exception $e) {
                $invalidMergeTicketIds[] = $mergeTicketId;
            }
        }
        if (0 < count($invalidMergeTicketIds)) {
            $apiresults = array("result" => "error", "message" => "Invalid Merge Ticket IDs: " . implode(", ", $invalidMergeTicketIds));
            return NULL;
        }
        if ($newSubject) {
            $masterTicket->title = $newSubject;
            $masterTicket->save();
        }
        $masterTicket->mergeOtherTicketsInToThis($mergeTicketIds);
        $apiresults = array("result" => "success", "ticketid" => $masterTicketId);
    }
}

?>