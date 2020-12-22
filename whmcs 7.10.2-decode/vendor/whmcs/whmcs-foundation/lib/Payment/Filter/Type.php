<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\Filter;

class Type extends AbstractFilter
{
    private $acceptableTypes = array();
    public function __construct($type)
    {
        if (!is_array($type)) {
            $type = array($type);
        }
        $this->acceptableTypes = $type;
    }
    public function filter(\WHMCS\Payment\Adapter\AdapterInterface $adapter)
    {
        $adapterType = $adapter->getSolutionType();
        if (in_array($adapterType, $this->acceptableTypes)) {
            return true;
        }
        return false;
    }
}

?>