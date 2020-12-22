<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . "functions.php";
if (!defined("PROXY_FILE")) {
    try {
        $path = getWhmcsInitPath();
    } catch (Exception $e) {
        echo cronsFormatOutput(getInitPathErrorMessage());
        exit(1);
    }
    require_once $path;
}

?>