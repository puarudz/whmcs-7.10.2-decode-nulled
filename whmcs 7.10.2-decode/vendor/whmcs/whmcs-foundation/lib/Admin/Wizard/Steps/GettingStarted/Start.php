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

class Start
{
    public function getStepContent()
    {
        return "<div class=\"wizard-transition-step\">\n    <div class=\"icon\"><i class=\"far fa-lightbulb\"></i></div>\n    <div class=\"title\">{lang key=\"wizard.welcome\"}</div>\n    <div class=\"tag\">{lang key=\"wizard.intro\"}</div>\n    <div class=\"greyout\">{lang key=\"wizard.noTime\"}</div>\n</div>";
    }
}

?>