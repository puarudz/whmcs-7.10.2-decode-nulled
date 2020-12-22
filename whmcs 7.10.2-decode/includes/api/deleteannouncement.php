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
$result = select_query("tblannouncements", "id", array("id" => $announcementid));
$data = mysql_fetch_array($result);
if (!$data["id"]) {
    $apiresults = array("result" => "error", "message" => "Announcement ID Not Found");
    return false;
}
delete_query("tblannouncements", array("id" => $announcementid));
delete_query("tblannouncements", array("parentid" => $announcementid));
$apiresults = array("result" => "success", "announcementid" => $announcementid);

?>