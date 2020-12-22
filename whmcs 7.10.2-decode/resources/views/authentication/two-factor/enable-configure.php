<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<form action=\"";
echo routePath(($isAdmin ? "admin-" : "") . "account-security-two-factor-enable-verify");
echo "\" onsubmit=\"dialogSubmit();return false\">\n    ";
echo generate_token("form");
echo "    <input type=\"hidden\" name=\"step\" value=\"verify\" />\n    <input type=\"hidden\" name=\"module\" value=\"";
echo $module;
echo "\" />\n    ";
echo $twoFactorConfigurationOutput;
echo "</form>\n";

?>