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
require dirname(__DIR__) . "/init.php";
$aInt = new WHMCS\Admin("Manage MarketConnect");
$aInt->title = AdminLang::trans("setup.marketconnect");
$aInt->requireAuthConfirmation();
$request = WHMCS\Http\Message\ServerRequest::fromGlobals();
$adminController = new WHMCS\MarketConnect\AdminController();
$aInt->setBodyContent($adminController->dispatch($request));
$aInt->display();

?>