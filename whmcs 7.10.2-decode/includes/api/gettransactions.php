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
$where = array();
if ($clientid) {
    $where["userid"] = $clientid;
}
if ($invoiceid) {
    $where["invoiceid"] = $invoiceid;
}
if ($transid) {
    $where["transid"] = $transid;
}
$result = select_query("tblaccounts", "", $where);
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result), "startnumber" => 0, "numreturned" => mysql_num_rows($result));
while ($data = mysql_fetch_assoc($result)) {
    $apiresults["transactions"]["transaction"][] = $data;
}
$responsetype = "xml";

?>