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
if (!function_exists("AddReply")) {
    require ROOTDIR . "/includes/ticketfunctions.php";
}
if ($ticketnum) {
    $result = select_query("tbltickets", "", array("tid" => $ticketnum));
} else {
    $result = select_query("tbltickets", "", array("id" => $ticketid));
}
$data = mysql_fetch_array($result);
$id = $data["id"];
$tid = $data["tid"];
$deptid = $data["did"];
$userid = $data["userid"];
$contactID = $data["contactid"];
$name = $data["name"];
$email = $data["email"];
$cc = $data["cc"];
$c = $data["c"];
$date = $data["date"];
$subject = $data["title"];
$message = $data["message"];
$status = $data["status"];
$priority = $data["urgency"];
$admin = $data["admin"];
$attachment = $data["attachment"];
$attachmentsRemoved = stringLiteralToBool($data["attachments_removed"]);
$lastreply = $data["lastreply"];
$flag = $data["flag"];
$service = $data["service"];
$message = strip_tags($message);
if (!$id) {
    $apiresults = array("result" => "error", "message" => "Ticket ID Not Found");
} else {
    if ($userid) {
        $result2 = select_query("tblclients", "", array("id" => $userid));
        $data = mysql_fetch_array($result2);
        $name = $data["firstname"] . " " . $data["lastname"];
        if ($data["companyname"]) {
            $name .= " (" . $data["companyname"] . ")";
        }
        $email = $data["email"];
        if ($contactID) {
            $contactData = get_query_vals("tblcontacts", "", array("id" => $contactID));
            $contactName = (string) $contactData["firstname"] . " " . $contactData["lastname"];
            if ($contactData["companyname"]) {
                $contactName .= " (" . $contactData["companyname"] . ")";
            }
            $contactEmail = $contactData["email"];
        }
    }
    $attachments = array();
    if ($attachment) {
        $attachmentArray = explode("|", $attachment);
        foreach ($attachmentArray as $key => $value) {
            $attachments[] = array("filename" => substr($value, 7), "index" => $key);
        }
    }
    $apiresults = array("result" => "success", "ticketid" => $id, "tid" => $tid, "c" => $c, "deptid" => $deptid, "deptname" => getDepartmentName($deptid), "userid" => $userid, "contactid" => $contactID, "name" => $name, "email" => $email, "cc" => $cc, "date" => $date, "subject" => $subject, "status" => $status, "priority" => $priority, "admin" => $admin, "lastreply" => $lastreply, "flag" => $flag, "service" => $service);
    $first_reply = array("replyid" => "0", "userid" => $userid, "contactid" => $contactID, "name" => isset($contactName) ? $contactName : $name, "email" => isset($contactEmail) ? $contactEmail : $email, "date" => $date, "message" => $message, "attachment" => $attachment, "attachments" => $attachments, "attachments_removed" => $attachmentsRemoved, "admin" => $admin);
    $sortorder = $_REQUEST["repliessort"] ? $_REQUEST["repliessort"] : "ASC";
    if ($sortorder == "ASC") {
        $apiresults["replies"]["reply"][] = $first_reply;
    }
    $result = select_query("tblticketreplies", "", array("tid" => $id), "id", $sortorder);
    while ($data = mysql_fetch_array($result)) {
        $replyid = $data["id"];
        $userid = $data["userid"];
        $contactID = $data["contactid"];
        $name = $data["name"];
        $email = $data["email"];
        $date = $data["date"];
        $message = $data["message"];
        $attachment = $data["attachment"];
        $attachmentsRemoved = stringLiteralToBool($data["attachments_removed"]);
        $admin = $data["admin"];
        $rating = $data["rating"];
        $message = strip_tags($message);
        if ($userid) {
            $result2 = select_query("tblclients", "", array("id" => $userid));
            $data = mysql_fetch_array($result2);
            $name = $data["firstname"] . " " . $data["lastname"];
            if ($data["companyname"]) {
                $name .= " (" . $data["companyname"] . ")";
            }
            $email = $data["email"];
            if ($contactID) {
                $contactData = get_query_vals("tblcontacts", "", array("id" => $contactID));
                $name = (string) $contactData["firstname"] . " " . $contactData["lastname"];
                if ($contactData["companyname"]) {
                    $name .= " (" . $contactData["companyname"] . ")";
                }
                $email = $contactData["email"];
            }
        }
        $attachments = array();
        if ($attachment) {
            $attachmentArray = explode("|", $attachment);
            foreach ($attachmentArray as $key => $value) {
                $attachments[] = array("filename" => substr($value, 7), "index" => $key);
            }
        }
        $apiresults["replies"]["reply"][] = array("replyid" => $replyid, "userid" => $userid, "contactid" => $contactID, "name" => $name, "email" => $email, "date" => $date, "message" => $message, "attachment" => $attachment, "attachments" => $attachments, "attachments_removed" => $attachmentsRemoved, "admin" => $admin, "rating" => $rating);
    }
    if ($sortorder != "ASC") {
        $apiresults["replies"]["reply"][] = $first_reply;
    }
    $apiresults["notes"] = array();
    $result = select_query("tblticketnotes", "", array("ticketid" => $id), "id", "ASC");
    while ($data = mysql_fetch_array($result)) {
        $noteid = $data["id"];
        $admin = $data["admin"];
        $date = $data["date"];
        $message = $data["message"];
        $attachment = $data["attachments"];
        $attachmentsRemoved = stringLiteralToBool($data["attachments_removed"]);
        $attachments = array();
        if ($attachment) {
            $attachmentArray = explode("|", $attachment);
            foreach ($attachmentArray as $key => $value) {
                $attachments[] = array("filename" => substr($value, 7), "index" => $key);
            }
        }
        $apiresults["notes"]["note"][] = array("noteid" => $noteid, "date" => $date, "message" => $message, "attachment" => $attachment, "attachments" => $attachments, "attachments_removed" => $attachmentsRemoved, "admin" => $admin);
    }
    $responsetype = "xml";
}

?>