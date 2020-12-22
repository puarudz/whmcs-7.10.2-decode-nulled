<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing\Pricing;

class Markup
{
    protected $amount = 0;
    protected $margin = 0;
    protected $rounding = 0;
    protected $decimalPlaces = 2;
    public static function factoryFixed($amount, $margin, $rounding = 0)
    {
        return (new self())->amount($amount)->margin($margin)->rounding($rounding)->fixed();
    }
    public static function factoryPercentage($amount, $margin, $rounding = 0)
    {
        return (new self())->amount($amount)->margin($margin)->rounding($rounding)->percentage();
    }
    public function amount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    public function margin($margin)
    {
        $this->margin = $margin;
        return $this;
    }
    public function rounding($rounding)
    {
        $this->rounding = $rounding;
        return $this;
    }
    public function fixed()
    {
        return $this->doRounding($this->amount + $this->margin);
    }
    public function percentage()
    {
        return $this->doRounding($this->amount * (1 + $this->margin / 100));
    }
    public function decimalPlaces($decimalPlaces)
    {
        if (is_int($decimalPlaces) && 0 <= $decimalPlaces) {
            $this->decimalPlaces = $decimalPlaces;
        }
        return $this;
    }
    protected function doRounding($amount)
    {
        if (0 < $this->rounding) {
            $roundingValue = $this->rounding;
            if (abs($roundingValue - 1) < 1.0E-5) {
                $roundingValue = 0;
            }
            $flooredAmount = floor($amount);
            if ($flooredAmount + $roundingValue < $amount) {
                $amount = $flooredAmount + 1 + $roundingValue;
            } else {
                $amount = $flooredAmount + $roundingValue;
            }
        }
        return round($amount, $this->decimalPlaces);
    }
    public function percentageDifference($costPrice)
    {
        $difference = 0;
        if (0 < $this->amount) {
            $difference = ($costPrice - $this->amount) / $this->amount * 100;
        }
        if (0 < $this->rounding) {
            $isNegative = false;
            if ($difference < 0) {
                $difference *= -1;
                $isNegative = true;
            }
            $difference = floor(round($difference) / $this->rounding) * $this->rounding;
            if ($isNegative) {
                $difference *= -1;
            }
        }
        $difference += 1.0E-6;
        return round($difference, $this->decimalPlaces);
    }
}

?>