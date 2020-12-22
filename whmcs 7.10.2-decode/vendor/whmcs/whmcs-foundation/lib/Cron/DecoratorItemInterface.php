<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cron;

interface DecoratorItemInterface
{
    public function getIcon();
    public function getName();
    public function getSuccessCountIdentifier();
    public function getFailureCountIdentifier();
    public function getSuccessKeyword();
    public function getFailureKeyword();
    public function getFailureUrl();
    public function getDetailUrl();
    public function isBooleanStatusItem();
    public function hasDetail();
}

?>