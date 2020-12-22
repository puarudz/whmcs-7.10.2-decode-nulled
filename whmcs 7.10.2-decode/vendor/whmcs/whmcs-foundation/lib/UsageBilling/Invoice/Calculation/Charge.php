<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\UsageBilling\Invoice\Calculation;

class Charge implements \WHMCS\UsageBilling\Contracts\Invoice\UsageCalculationInterface
{
    private $consumed = 0;
    private $bracket = NULL;
    private $price = NULL;
    private $isIncluded = false;
    public function __construct($consumed = 0, \WHMCS\Billing\PricingInterface $price = NULL, \WHMCS\UsageBilling\Contracts\Pricing\PriceBracketInterface $bracket = NULL, $isIncluded = false)
    {
        if (!is_numeric($consumed) || $consumed < 0) {
            $consumed = 0;
        }
        $this->consumed = $consumed;
        $this->isIncluded = (bool) $isIncluded;
        $this->price = $price;
        $this->bracket = $bracket;
    }
    public function consumed()
    {
        return $this->consumed;
    }
    public function bracket()
    {
        return $this->bracket;
    }
    public function price()
    {
        return $this->price;
    }
    public function isIncluded()
    {
        return $this->isIncluded;
    }
}

?>