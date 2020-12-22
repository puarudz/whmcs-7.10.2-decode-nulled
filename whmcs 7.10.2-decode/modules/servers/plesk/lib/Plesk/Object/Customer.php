<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Plesk_Object_Customer
{
    const STATUS_ACTIVE = 0;
    const STATUS_SUSPENDED_BY_ADMIN = 16;
    const STATUS_SUSPENDED_BY_RESELLER = 32;
    const TYPE_CLIENT = "hostingaccount";
    const TYPE_RESELLER = "reselleraccount";
    const EXTERNAL_ID_PREFIX = "whmcs_plesk_";
    public static function getCustomerExternalId($params)
    {
        if (isset($params["clientsdetails"]["panelExternalId"]) && "" != $params["clientsdetails"]["panelExternalId"]) {
            return $params["clientsdetails"]["panelExternalId"];
        }
        return $params["clientsdetails"]["uuid"];
    }
}

?>