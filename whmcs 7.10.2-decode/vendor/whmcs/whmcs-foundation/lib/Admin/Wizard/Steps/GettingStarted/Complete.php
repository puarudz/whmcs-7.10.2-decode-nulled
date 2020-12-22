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

class Complete
{
    public function getStepContent()
    {
        return "<div class=\"wizard-transition-step\">\n    <div class=\"icon\"><i class=\"far fa-lightbulb\"></i></div>\n    <div class=\"title\">{lang key=\"wizard.setupComplete\"}</div>\n    <div class=\"tag\">{lang key=\"wizard.readyToBeginUsing\"}</div>\n    <div class=\"greyout\">{lang key=\"wizard.runAgainMsg\"}</div>\n    <div style=\"margin:10px 0 0 0;\" class=\"greyout hidden\" id=\"enomEnabled\">\n        {lang key=\"wizard.enomIpWhiteList\" link=\"<a href='https://docs.whmcs.com/Enom#IP_Registration_.28User_not_permitted_from_this_IP_address.29' class='autoLinked'>{lang key=\"global.clickhere\"}</a>\"}\n    </div>\n</div>";
    }
}

?>