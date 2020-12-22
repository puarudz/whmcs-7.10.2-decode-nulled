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
$redirectUrl = routePath("subscription-manage");
if (strpos($redirectUrl, "?") === false) {
    $redirectUrl .= "?";
} else {
    $redirectUrl .= "&";
}
$redirectUrl .= "action=optout" . "&email=" . App::getFromRequest("email") . "&key=" . App::getFromRequest("key");
header("Location: " . $redirectUrl);

?>