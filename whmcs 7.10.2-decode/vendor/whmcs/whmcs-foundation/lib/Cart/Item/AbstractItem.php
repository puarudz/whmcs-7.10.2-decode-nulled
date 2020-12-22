<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Cart\Item;

abstract class AbstractItem
{
    public $id = NULL;
    public $name = NULL;
    public $billingCycle = NULL;
    public $billingPeriod = 1;
    public $qty = 1;
    public $amount = NULL;
    public $recurring = NULL;
    public $taxed = false;
    public $initialPeriod = 0;
    public $initialCycle = NULL;
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    public function setBillingCycle($billingCycle)
    {
        $billingCycle = (new \WHMCS\Billing\Cycles())->getNormalisedBillingCycle($billingCycle);
        if ($billingCycle == "quarterly") {
            $this->setBillingPeriod(3);
            $billingCycle = "monthly";
        } else {
            if ($billingCycle == "semiannually") {
                $this->setBillingPeriod(6);
                $billingCycle = "monthly";
            } else {
                if ($billingCycle == "biennially") {
                    $this->setBillingPeriod(2);
                    $billingCycle = "annually";
                } else {
                    if ($billingCycle == "triennially") {
                        $this->setBillingPeriod(3);
                        $billingCycle = "annually";
                    }
                }
            }
        }
        $this->billingCycle = $billingCycle;
        return $this;
    }
    public function setBillingPeriod($billingPeriod)
    {
        $this->billingPeriod = $billingPeriod;
        return $this;
    }
    public function setQuantity($qty)
    {
        $this->qty = $qty;
        return $this;
    }
    public function setAmount(\WHMCS\View\Formatter\Price $amount)
    {
        $this->amount = $amount;
        return $this;
    }
    public function setRecurring(\WHMCS\View\Formatter\Price $recurring = NULL)
    {
        $this->recurring = $recurring;
        return $this;
    }
    public function setTaxed($taxed)
    {
        $this->taxed = (bool) $taxed;
        return $this;
    }
    public function setInitialPeriod($period, $cycle)
    {
        $this->initialPeriod = $period;
        $this->initialCycle = $cycle;
        return $this;
    }
    public function hasInitialPeriod()
    {
        return !is_null($this->initialCycle);
    }
}

?>