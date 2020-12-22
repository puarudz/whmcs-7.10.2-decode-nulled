<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

function addPrintInputToForm($formContainer)
{
    return preg_replace("/(<form\\W[^>]*\\bmethod=('|\"|)POST('|\"|)\\b[^>]*>)/i", "\\1" . "\n" . "<input type=\"hidden\" name=\"print\" value=\"true\" />", $formContainer);
}
function getReportsList()
{
    static $textReports = NULL;
    if (!$textReports) {
        $textReports = array();
        $reportDir = ROOTDIR . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . "reports" . DIRECTORY_SEPARATOR;
        $dh = opendir($reportDir);
        while (false !== ($file = readdir($dh))) {
            if ($file != "index.php" && is_file($reportDir . $file)) {
                $file = str_replace(".php", "", $file);
                if (substr($file, 0, 5) != "graph") {
                    $niceName = str_replace("_", " ", $file);
                    $niceName = titleCase($niceName);
                    $textReports[$file] = $niceName;
                }
            }
        }
        closedir($dh);
        asort($textReports);
    }
    return $textReports;
}

?>