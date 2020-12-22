<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Config;

interface DatabaseInterface
{
    public function getDatabaseName();
    public function getDatabaseUsername();
    public function getDatabasePassword();
    public function getDatabaseHost();
    public function getDatabaseCharset();
    public function getDatabasePort();
    public function setDatabasePort($value);
    public function setDatabaseName($value);
    public function setDatabaseUsername($value);
    public function setDatabasePassword($value);
    public function setDatabaseHost($value);
    public function setDatabaseCharset($value);
    public function getSqlMode();
}

?>