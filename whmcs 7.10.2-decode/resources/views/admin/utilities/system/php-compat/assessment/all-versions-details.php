<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$assessments = empty($assessments) ? array() : $assessments;
$panels = array();
$tabs = array();
$loader = new WHMCS\Environment\Ioncube\Loader\Loader100100();
foreach ($assessments as $versionDetail) {
    $phpVersion = $versionDetail->getPhpVersion();
    $active = $phpVersion == PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION ? " active" : "";
    $class = $active ? "class=\"" . $active . "\"" : "";
    $phpId = $versionDetail->getPhpVersionId();
    $tabs[] = "    <li role=\"presentation\" " . $class . ">\n        <a href=\"#tabPhp" . $phpId . "\" \n            id=\"btnPhp" . $phpId . "\" \n            aria-controls=\"tabPhp" . $phpId . "\"\n            role=\"tab\" \n            data-toggle=\"tab\">\n            PHP " . $phpVersion . "\n        </a>\n    </li>";
    $panelContent = $versionDetail->getHtml();
    $active = $active ? " in active" : "";
    $panels[] = "    <div id=\"tabPhp" . $phpId . "\"\n        class=\"tab-pane fade" . $active . "\"\n        role=\"tabpanel\"  \n        >\n        " . $panelContent . "\n    </div>";
}
echo "<p>\n    ";
echo AdminLang::trans("Please choose the version of PHP that you wish to upgrade to in order to view encoded file compatibility results for that version.");
echo "</p>\n<br/>\n<div role=\"tabpanel\">\n    <ul class=\"nav nav-tabs\" role=\"tablist\">\n        ";
echo implode("\n", $tabs);
echo "    </ul>\n    <br />\n    <div class=\"tab-content\">\n        ";
echo implode("\n", $panels);
echo "    </div>\n</div>\n";

?>