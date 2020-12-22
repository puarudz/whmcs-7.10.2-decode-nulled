<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<input type=\"hidden\" id=\"offlineTransactionPayMethod\" value=\"";
echo $payMethod->id;
echo "\" />\n<div class=\"row\">\n    <div class=\"col-xs-12\" style=\"font-size: 1.2em; margin-bottom: 10px;\">\n        ";
echo $cardData["cctype"];
echo "        ";
echo $cardData["ccnum"];
echo "    </div>\n</div>\n<div class=\"row\">\n    <div class=\"col-xs-5\">";
echo AdminLang::trans("fields.expdate");
echo ":</div>\n    <div class=\"col-xs-7\">";
echo $cardData["expdate"];
echo "</div>\n</div>\n";
if ($cardData["issuenumber"]) {
    echo "<div class=\"row\">\n    <div class=\"col-xs-5\">";
    echo AdminLang::trans("fields.issueno");
    echo ":</div>\n    <div class=\"col-xs-7\">";
    echo $cardData["issuenumber"];
    echo "</div>\n</div>\n";
}
if ($cardData["startdate"]) {
    echo "<div class=\"row\">\n    <div class=\"col-xs-5\">";
    echo AdminLang::trans("fields.startdate");
    echo ":</div>\n    <div class=\"col-xs-7\">";
    echo $cardData["startdate"];
    echo "</div>\n</div>\n";
}

?>