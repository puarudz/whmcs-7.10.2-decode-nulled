<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Markup\Error\Message\MatchDecorator;

class NoMatchDecorator extends AbstractMatchDecorator
{
    use GenericMatchDecorationTrait;
    public function getTitle()
    {
        return "Error";
    }
    public function getHelpUrl()
    {
        return "https://docs.whmcs.com/Automatic_Updater#Troubleshooting";
    }
    protected function isKnown($data)
    {
        return true;
    }
}

?>