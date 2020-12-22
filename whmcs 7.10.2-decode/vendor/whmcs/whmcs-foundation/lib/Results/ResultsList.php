<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Results;

class ResultsList extends \ArrayObject
{
    public function toArray()
    {
        $result = array();
        foreach ($this->getArrayCopy() as $key => $data) {
            $result[$key] = $data->toArray();
        }
        return $result;
    }
}

?>