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

trait GenericMatchDecorationTrait
{
    public function toHtml()
    {
        return $this->toGenericHtml(implode("\n", $this->getParsedMessageList()));
    }
    public function toPlain()
    {
        return $this->toGenericPlain(implode("\n", $this->getParsedMessageList()));
    }
}

?>