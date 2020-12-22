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
$aInt = new WHMCS\Admin("List Services");
$listType = App::getFromRequest("listtype");
switch ($listType) {
    case "hostingaccount":
        $path = "shared";
        break;
    case "reselleraccount":
        $path = "reseller";
        break;
    case "server":
    case "other":
        $path = $listType;
        break;
    default:
        $path = "index";
}
App::redirectToRoutePath("admin-services-" . $path);

?>