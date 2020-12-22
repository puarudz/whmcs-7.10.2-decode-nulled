<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

add_hook("ClientAreaFooterOutput", 1, function (array $vars) {
    $return = "";
    try {
        WHMCS\Module\Gateway::factory("stripe");
    } catch (Exception $e) {
        $filename = $vars["filename"];
        $template = $vars["templatefile"];
        $requiredFiles = array("cart", "creditcard");
        $templateFiles = array("account-paymentmethods-manage", "invoice-payment");
        if (in_array($filename, $requiredFiles) || in_array($template, $templateFiles)) {
            $return = "<script type=\"text/javascript\" src=\"https://js.stripe.com/v3/\">" . "</script>";
        }
    }
    return $return;
});

?>