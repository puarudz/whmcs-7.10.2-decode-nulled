<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Contracts;

interface NotificationModuleInterface
{
    public function settings();
    public function isActive();
    public function getName();
    public function getDisplayName();
    public function getLogoPath();
    public function testConnection($settings);
    public function notificationSettings();
    public function getDynamicField($fieldName, $settings);
    public function sendNotification(\WHMCS\Notification\Contracts\NotificationInterface $notification, $moduleSettings, $notificationSettings);
}

?>