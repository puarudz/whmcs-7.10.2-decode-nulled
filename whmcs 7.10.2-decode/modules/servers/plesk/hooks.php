<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

add_hook("ShoppingCartValidateCheckout", 1, function ($vars) {
    require_once "lib/Plesk/Translate.php";
    require_once "lib/Plesk/Config.php";
    require_once "lib/Plesk/Utils.php";
    $translator = new Plesk_Translate();
    $accountLimit = (int) Plesk_Config::get()->account_limit;
    if ($accountLimit <= 0) {
        return array();
    }
    $accountCount = "new" == $vars["custtype"] ? 0 : Plesk_Utils::getAccountsCount($vars["userid"]);
    $pleskAccountsInCart = 0;
    foreach ($_SESSION["cart"]["products"] as $product) {
        $currentProduct = Illuminate\Database\Capsule\Manager::table("tblproducts")->where("id", $product["pid"])->first();
        if ("plesk" == $currentProduct->servertype) {
            $pleskAccountsInCart++;
        }
    }
    if (!$pleskAccountsInCart) {
        return array();
    }
    $summaryAccounts = $accountCount + $pleskAccountsInCart;
    $errors = array();
    if (0 < $accountLimit && $accountLimit < $summaryAccounts) {
        $errors[] = $translator->translate("ERROR_RESTRICTIONS_ACCOUNT_COUNT", array("ACCOUNT_LIMIT" => $accountLimit));
    }
    return $errors;
});

?>