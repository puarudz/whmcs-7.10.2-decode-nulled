<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Authorization\Contracts;

interface RoleInterface
{
    public function getId();
    public function allow(array $itemsToAllow);
    public function deny(array $itemsToDeny);
    public function getData();
    public function setData(array $data);
}

?>