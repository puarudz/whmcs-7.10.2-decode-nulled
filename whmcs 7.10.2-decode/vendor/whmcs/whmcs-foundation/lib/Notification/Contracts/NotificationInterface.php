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

interface NotificationInterface
{
    public function getTitle();
    public function setTitle($title);
    public function getMessage();
    public function setMessage($message);
    public function getUrl();
    public function setUrl($url);
    public function getAttributes();
    public function setAttributes($attributes);
    public function addAttribute(NotificationAttributeInterface $attribute);
}

?>