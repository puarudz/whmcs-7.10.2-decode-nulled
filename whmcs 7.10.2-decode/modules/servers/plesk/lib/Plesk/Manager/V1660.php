<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Plesk_Manager_V1660 extends Plesk_Manager_V1640
{
    protected function _getAddAccountParams($params)
    {
        $result = parent::_getAddAccountParams($params);
        $result["powerUser"] = "on" === $params["configoption4"] ? "true" : "false";
        return $result;
    }
    protected function _addAccount($params)
    {
        return parent::_addAccount($params);
    }
}

?>