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
if (!$limitstart) {
    $limitstart = 0;
}
if (!$limitnum) {
    $limitnum = 25;
}
$log = new WHMCS\Log\Activity();
$log->setCriteria(array("userid" => $whmcs->get_req_var("userid"), "date" => $whmcs->get_req_var("date"), "username" => $whmcs->get_req_var("user"), "description" => $whmcs->get_req_var("description"), "ipaddress" => $whmcs->get_req_var("ipaddress")));
$totalresults = $log->getTotalCount();
$apiresults = array("result" => "success", "totalresults" => $totalresults, "startnumber" => $limitstart);
$offset = $limitstart / $limitnum;
$offset = floor($offset);
if ($offset < 0) {
    $offset = 0;
}
$log->setOutputFormatting($whmcs->get_req_var("format"));
$apiresults["activity"]["entry"] = $log->getLogEntries($offset, $limitnum);
$responsetype = "xml";

?>