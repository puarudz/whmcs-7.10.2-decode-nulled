<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Wizard\Steps\GettingStarted;

class MarketConnect
{
    public function getTemplateVariables()
    {
        $vars = array();
        $assetHelper = \DI::make("asset");
        $vars["IMG_PATH"] = $assetHelper->getImgPath();
        $vars["WEB_PATH"] = $assetHelper->getWebRoot() . "/" . \App::get_admin_folder_name();
        return $vars;
    }
    public function getStepContent()
    {
        return "<div class=\"text-center top-margin-10 bottom-margin-10\">\n    <img width=\"400\" src=\"{\$IMG_PATH}/marketconnect/logo.png\" alt=\"{lang key='wizard.marketConnect'}\">\n</div>\n<div class=\"text-center\" style=\"margin: 20px 50px 0;\">\n    {lang key='wizard.marketConnectDescription'}\n</div>\n<div class=\"text-center\">\n    <a class=\"autoLinked\" href=\"{\$WEB_PATH}/marketconnect.php\">\n        <img src=\"{\$IMG_PATH}/wizard/marketconnect.png\">\n    </a>\n</div>\n<div class=\"text-center\">\n    <a class=\"btn btn-primary autoLinked\" href=\"{\$WEB_PATH}/marketconnect.php\">\n        {lang key='global.learnMore'}\n    </a>\n</div>";
    }
}

?>