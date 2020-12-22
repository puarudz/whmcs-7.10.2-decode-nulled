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
$result = select_query("tblcurrencies", "", "", "id", "ASC");
$apiresults = array("result" => "success", "totalresults" => mysql_num_rows($result));
while ($data = mysql_fetch_array($result)) {
    $id = $data["id"];
    $code = $data["code"];
    $prefix = $data["prefix"];
    $suffix = $data["suffix"];
    $format = $data["format"];
    $rate = $data["rate"];
    $apiresults["currencies"]["currency"][] = array("id" => $id, "code" => $code, "prefix" => $prefix, "suffix" => $suffix, "format" => $format, "rate" => $rate);
}
$responsetype = "xml";

?>