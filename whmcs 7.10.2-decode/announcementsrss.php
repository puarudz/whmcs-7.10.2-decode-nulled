<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require_once "init.php";
$rss = new WHMCS\Announcement\Rss();
$request = Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = $rss->toXml($request);
(new Zend\Diactoros\Response\SapiEmitter())->emit($response);

?>