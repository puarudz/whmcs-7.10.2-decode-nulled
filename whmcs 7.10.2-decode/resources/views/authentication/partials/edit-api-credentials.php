<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

echo "<form name=\"frmApiCredentialManage\" action=\"";
echo routePath("admin-setup-authz-api-devices-update");
echo "\">\n    <input type=\"hidden\" name=\"token\" value=\"";
echo $csrfToken;
echo "\">\n    <input type=\"hidden\" name=\"id\" value=\"";
echo $device->id;
echo "\">\n    ";
echo $this->insert("partials/attributes-api-credentials");
echo "</form>\n";

?>