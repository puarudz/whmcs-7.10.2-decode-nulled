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

class Name extends AbstractFilter
{
    private $acceptableName = array();
    public function __construct($name)
    {
        if (!is_array($name)) {
            $name = array($name);
        }
        $this->acceptableName = $name;
    }
    public function filter(\WHMCS\Payment\Adapter\AdapterInterface $adapter)
    {
        $name = $adapter->getName();
        if (in_array($name, $this->acceptableName)) {
            return true;
        }
        return false;
    }
}

?>