<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

define("CLIENTAREA", true);
require "init.php";
$whmcs = App::self();
$emailId = (int) $whmcs->get_req_var("id");
$ca = new WHMCS\ClientArea();
$ca->setPageTitle(Lang::trans("clientareaemails"));
$ca->addToBreadCrumb("index.php", $whmcs->get_lang("globalsystemname"));
$ca->addToBreadCrumb("viewemail.php?id=" . (int) $emailId . "#", Lang::trans("clientareaemails"));
$ca->initPage();
$ca->requireLogin();
checkContactPermission("emails");
$result = select_query("tblemails", "", array("id" => $emailId, "userid" => $ca->getUserID()));
$data = mysql_fetch_array($result);
$date = $data["date"];
$subject = $data["subject"];
$message = $data["message"];
$date = fromMySQLDate($date, true, true);
$ca->assign("date", WHMCS\Input\Sanitize::makeSafeForOutput($date));
$ca->assign("subject", WHMCS\Input\Sanitize::makeSafeForOutput($subject));
$message = WHMCS\Input\Sanitize::maskEmailVerificationId($message);
$ca->assign("message", $message);
$ca->setTemplate("viewemail");
$ca->disableHeaderFooterOutput();
$ca->addOutputHookFunction("ClientAreaPageViewEmail");
$ca->output();

?>