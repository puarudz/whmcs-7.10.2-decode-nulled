<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

define("ADMINAREA", true);
require "../init.php";
$aInt = new WHMCS\Admin("View Clients Products/Services");
if ($userid && $hostingid) {
    redir("userid=" . $userid . "&id=" . $hostingid, "clientsservices.php");
}
if ($userid && $id) {
    redir("userid=" . $userid . "&id=" . $id, "clientsservices.php");
}
if ($id) {
    redir("id=" . $id, "clientsservices.php");
}
if ($userid) {
    redir("userid=" . $userid, "clientsservices.php");
}
redir("", "clientsservices.php");

?>