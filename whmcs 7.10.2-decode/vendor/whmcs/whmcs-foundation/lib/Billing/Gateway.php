<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing;

class Gateway extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblpaymentgateways";
    public $timestamps = false;
    public function scopeName($query)
    {
        return $query->where("setting", "name");
    }
}

?>