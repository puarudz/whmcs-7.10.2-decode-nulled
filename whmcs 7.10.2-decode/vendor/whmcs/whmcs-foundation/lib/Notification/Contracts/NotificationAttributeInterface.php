<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Notification\Contracts;

interface NotificationAttributeInterface
{
    public function getLabel();
    public function setLabel($label);
    public function getValue();
    public function setValue($value);
    public function getUrl();
    public function setUrl($url);
    public function getStyle();
    public function setStyle($style);
    public function getIcon();
    public function setIcon($icon);
}

?>