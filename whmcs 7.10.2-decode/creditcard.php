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
$invoiceId = App::getFromRequest("invoiceid");
if (!$invoiceId) {
    App::redirect("clientarea.php");
}
App::redirectToRoutePath("invoice-pay", $invoiceId);

?>