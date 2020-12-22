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
$notes = array();
$result = select_query("tblticketnotes", "id,admin,date,message,attachments,attachments_removed", array("ticketid" => $ticketid), "date", "ASC");
while ($data = mysql_fetch_assoc($result)) {
    $data["attachments_removed"] = stringLiteralToBool($data["attachments_removed"]);
    $notes[] = $data;
}
$apiresults = array("result" => "success", "totalresults" => count($notes), "notes" => array("note" => $notes));
$responsetype = "xml";

?>