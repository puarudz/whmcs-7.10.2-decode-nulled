<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<div class=\"apps active\">\n    ";
$hasActiveApps = false;
foreach ($apps->active() as $app) {
    $this->insert("apps/shared/app", array("app" => $app));
    $hasActiveApps = true;
}
echo "    ";
if (!$hasActiveApps) {
    echo "        <div class=\"no-active-apps\">\n            <span>";
    echo AdminLang::trans("apps.noActiveApps");
    echo "</span>\n            <br><br>\n            ";
    echo AdminLang::trans("apps.description");
    echo "            <br>\n            ";
    echo AdminLang::trans("apps.activateToGetStarted");
    echo "            <br>\n            <a href=\"#\" class=\"btn btn-default btn-lg\" onclick=\"\$('#tabBrowse').click();\">Browse Apps</a>\n        </div>\n    ";
}
echo "</div>\n";

?>