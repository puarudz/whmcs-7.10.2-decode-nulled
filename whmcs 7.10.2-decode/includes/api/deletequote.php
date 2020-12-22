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
$result = select_query("tblquotes", "", array("id" => $quoteid));
$data = mysql_fetch_array($result);
$quoteid = $data["id"];
if (!$quoteid) {
    $apiresults = array("result" => "error", "message" => "Quote ID Not Found");
} else {
    delete_query("tblquotes", array("id" => $quoteid));
    delete_query("tblquoteitems", array("quoteid" => $quoteid));
    $apiresults = array("result" => "success");
}

?>