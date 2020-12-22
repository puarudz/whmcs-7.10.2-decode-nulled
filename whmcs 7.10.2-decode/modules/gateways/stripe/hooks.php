<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

add_hook("AdminAreaFooterOutput", 1, function (array $vars) {
    $filename = $vars["filename"];
    $return = "";
    if ($filename == "clientssummary") {
        $return = "<script type=\"text/javascript\" src=\"https://js.stripe.com/v3/\"></script>";
    }
    return $return;
});
add_hook("ClientAreaFooterOutput", 1, function (array $vars) {
    $filename = $vars["filename"];
    $template = $vars["templatefile"];
    $return = "";
    $requiredFiles = array("cart", "creditcard");
    $requiredTemplates = array("account-paymentmethods-manage", "invoice-payment");
    if (in_array($filename, $requiredFiles) || in_array($template, $requiredTemplates)) {
        $return = "<script type=\"text/javascript\" src=\"https://js.stripe.com/v3/\"></script>";
    }
    return $return;
});

?>