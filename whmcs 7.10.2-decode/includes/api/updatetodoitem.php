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
$id = get_query_val("tbltodolist", "id", array("id" => $itemid));
if (!$itemid) {
    $apiresults = array("result" => "error", "message" => "TODO Item ID Not Found");
} else {
    $adminid = get_query_val("tbladmins", "id", array("id" => $adminid));
    if (!$adminid) {
        $apiresults = array("result" => "error", "message" => "Admin ID Not Found");
    } else {
        $todoarray = array();
        if ($date) {
            $todoarray["date"] = toMySQLDate($date);
        }
        if ($title) {
            $todoarray["title"] = $title;
        }
        if ($description) {
            $todoarray["description"] = $description;
        }
        if ($adminid) {
            $todoarray["admin"] = $adminid;
        }
        if ($status) {
            $todoarray["status"] = $status;
        }
        if ($duedate) {
            $todoarray["duedate"] = toMySQLDate($duedate);
        }
        update_query("tbltodolist", $todoarray, array("id" => $itemid));
        $apiresults = array("result" => "success", "itemid" => $itemid);
    }
}

?>