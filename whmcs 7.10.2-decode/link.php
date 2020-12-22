<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require "init.php";
$id = (int) $whmcs->get_req_var("id");
$url = get_query_val("tbllinks", "link", array("id" => $id));
if ($url) {
    update_query("tbllinks", array("clicks" => "+1"), array("id" => $id));
    WHMCS\Cookie::set("LinkID", $id, "3m");
    run_hook("LinkTracker", array("linkid" => $id));
    header("Location: " . WHMCS\Input\Sanitize::decode($url));
    exit;
}
redir("", "index.php");

?>