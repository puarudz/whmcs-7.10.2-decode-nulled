<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Domains\DomainLookup\Provider;

class WhmcsWhois extends BasicWhois
{
    public function getSettings()
    {
        static $tlds = NULL;
        if (is_null($tlds)) {
            $tlds = \WHMCS\Database\Capsule::table("tbldomainpricing")->orderBy("order", "ASC")->pluck("extension", "extension");
        }
        return array("suggestTlds" => array("FriendlyName" => \AdminLang::trans("general.suggesttldsinfo"), "Type" => "dropdown", "Description" => "<div class=\"text-muted text-center small\">" . \AdminLang::trans("global.ctrlclickmultiselection") . "</div>", "Default" => "", "Size" => 10, "Options" => $tlds, "Multiple" => true));
    }
}

?>