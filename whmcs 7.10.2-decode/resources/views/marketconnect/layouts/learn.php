<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<button aria-label=\"Close\" class=\"close\" data-dismiss=\"modal\" type=\"button\"><span aria-hidden=\"true\">&times;</span></button>\n<div class=\"logo\"><img src=\"";
echo file_exists("../assets/img/marketconnect/" . $vendorSystemName . "/logo.svg") ? "../assets/img/marketconnect/" . $vendorSystemName . "/logo.svg" : "../assets/img/marketconnect/" . $vendorSystemName . "/logo.png";
echo "\" style=\"max-height:";
echo $vendorSystemName == "sitelock" ? "68" : "85";
echo "px;\"></div>\n<div class=\"title\">\n    <h3>";
echo $serviceTitle;
echo "</h3>\n    <h4>From ";
echo $vendorName;
echo "</h4>\n</div>\n<div class=\"clearfix\"></div>\n\n<div>\n    <ul class=\"nav nav-tabs\" role=\"tablist\">\n        ";
echo $this->section("nav-tabs");
echo "        <li class=\"pull-right\" role=\"presentation\">\n            ";
if ($service["attributes"]["status"] != 1) {
    echo "            <a aria-controls=\"activate\" class=\"activate\" data-toggle=\"tab\" href=\"#activate\" role=\"tab\">Activate</a>\n            ";
} else {
    echo "            <a aria-controls=\"deactivate\" class=\"deactivate btn-deactivate\" data-toggle=\"tab\" href=\"#deactivate\" role=\"tab\" data-service=\"";
    echo $serviceOffering["vendorSystemName"];
    echo "\">Deactivate</a>\n            ";
}
echo "        </li>\n    </ul>\n    <div class=\"tab-content\">\n        ";
echo $this->section("content-tabs");
echo "    </div>\n</div>\n";
if (App::getFromRequest("activate")) {
    echo "<script type=\"text/javascript\">\n\$(document).ready(function (){\n    \$('.activate').click();\n});\n</script>";
}
echo "\n<script type=\"text/javascript\">\n\$(document).ready(function() {\n    jQuery(\".product-status\").bootstrapSwitch({size: 'small', onText: 'Active', onColor: 'success', offText: 'Disabled'});\n    jQuery(\".promo-switch, .setting-switch\").bootstrapSwitch({size: 'mini'});\n});\n</script>\n";

?>