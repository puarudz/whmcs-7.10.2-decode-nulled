<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<p>";
echo Lang::trans("twofadisableconfirmation");
echo "</p>\n\n<script>\n\$('.twofa-toggle-switch').bootstrapSwitch('state', false, true);\n\$('.twofa-config-link.disable').hide();\n\$('.twofa-config-link.enable').removeClass('hidden').show();\n</script>\n";

?>