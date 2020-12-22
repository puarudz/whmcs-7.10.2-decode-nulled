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

interface MatchDecoratorInterface extends \WHMCS\View\Markup\Error\Message\DecoratorInterface, \WHMCS\View\Markup\Error\ErrorLevelInterface
{
    public function wrap(\Iterator $data);
    public function getData();
    public function hasMatch();
}

?>