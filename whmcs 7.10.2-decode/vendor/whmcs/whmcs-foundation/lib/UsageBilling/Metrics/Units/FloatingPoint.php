<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Metrics\Units;

class FloatingPoint extends AbstractUnit
{
    public function type()
    {
        return \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_FLOAT_PRECISION_LOW;
    }
}

?>