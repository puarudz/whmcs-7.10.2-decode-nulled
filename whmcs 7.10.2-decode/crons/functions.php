<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

function getWhmcsInitPath()
{
    $whmcspath = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . "config.php")) {
        require __DIR__ . DIRECTORY_SEPARATOR . "config.php";
    }
    $path = realpath($whmcspath . DIRECTORY_SEPARATOR . "init.php");
    if (!$path) {
        throw new Exception("Unable to determine WHMCS init.php path.");
    }
    return $path;
}
function getInitPathErrorMessage()
{
    return "Unable to communicate with the WHMCS installation.<br />\nPlease verify the path configured within the crons directory config.php file.<br />\nFor more information, please see <a href=\"https://docs.whmcs.com/Custom_Crons_Directory\">https://docs.whmcs.com/Custom_Crons_Directory</a>\n";
}
function cronsFormatOutput($output)
{
    if (cronsIsCli()) {
        $output = strip_tags(str_replace(array("<br>", "<br />", "<br/>", "<hr>"), array("\n", "\n", "\n", "\n---\n"), $output));
    }
    return $output;
}
function cronsIsCli()
{
    switch (php_sapi_name()) {
        case "cli":
        case "cli-server":
            return true;
    }
    if (!isset($_SERVER["SERVER_NAME"]) && !isset($_SERVER["HTTP_HOST"])) {
        return true;
    }
    return false;
}

?>