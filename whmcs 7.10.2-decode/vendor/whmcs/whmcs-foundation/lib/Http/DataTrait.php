<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Http;

trait DataTrait
{
    protected $rawData = array();
    public function getRawData()
    {
        return $this->rawData;
    }
    public function setRawData($rawData)
    {
        $this->rawData = $rawData;
        return $this;
    }
}

?>