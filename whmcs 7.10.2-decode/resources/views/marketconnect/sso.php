<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$this->layout("layouts/admin-content");
$this->start("body");
echo "\n<div class=\"redirect-msg\">\nYou are now being redirected to the WHMCS Marketplace.<br>\nWhen you are finished, simply close this tab to return to WHMCS.<br><br>\n<small>If you are not automatically redirected within 5 seconds, please <a href=\"#\">click here</a></small>\n</div>\n\n<script>\n\$(document).ready(function() {\n    WHMCS.http.jqClient.post('', 'action=doSsoRedirect&destination=";
echo $ssoDestination;
echo "', function(data) {\n        window.location = data.redirectUrl;\n    }, 'json');\n});\n</script>\n";
$this->end();

?>