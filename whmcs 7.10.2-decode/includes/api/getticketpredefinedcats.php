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
$totalresults = WHMCS\Database\Capsule::table("tblticketpredefinedcats")->count();
$apiresults = array("result" => "success", "totalresults" => $totalresults);
$result = full_query("SELECT c.*, COUNT(r.id) AS replycount FROM tblticketpredefinedcats c LEFT JOIN tblticketpredefinedreplies r ON c.id=r.catid GROUP BY c.id ORDER BY c.name ASC");
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["categories"]["category"][] = $data;
}
$responsetype = "xml";

?>