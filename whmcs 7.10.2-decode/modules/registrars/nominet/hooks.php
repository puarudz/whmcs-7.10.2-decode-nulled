<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

add_hook("ClientAreaPrimarySidebar", -1, "nominet_HideReleaseDomain");
function nominet_HideReleaseDomain(WHMCS\View\Menu\Item $primarySidebar)
{
    $settingAllowClientTag = get_query_val("tblregistrars", "value", "registrar = 'nominet' AND setting = 'AllowClientTAGChange'");
    $settingAllowClientTag = decrypt($settingAllowClientTag);
    if ($settingAllowClientTag == "on") {
        return NULL;
    }
    if (!is_null($primarySidebar->getChild("Domain Details Management"))) {
        $primarySidebar->getChild("Domain Details Management")->removeChild("Release Domain");
    }
}

?>