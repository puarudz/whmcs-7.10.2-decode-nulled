<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Formatter;

class Price
{
    protected $price = 0;
    protected $currency = NULL;
    protected $defaultFormat = NULL;
    protected $defaultCurrencyDescriptor = array("format" => "1", "prefix" => "", "suffix" => "");
    const PREFIX = "{PREFIX}";
    const PRICE = "{PRICE}";
    const SUFFIX = "{SUFFIX}";
    public function __construct($price, $currency = NULL)
    {
        $this->price = $price;
        $this->currency = $currency;
    }
    public function __toString()
    {
        return $this->toFull();
    }
    public function toFull()
    {
        return $this->format(self::PREFIX . self::PRICE . self::SUFFIX);
    }
    public function toPrefixed()
    {
        return $this->format(self::PREFIX . self::PRICE);
    }
    public function toSuffixed()
    {
        return $this->format(self::PRICE . self::SUFFIX);
    }
    public function toNumeric()
    {
        return $this->format(self::PRICE, array("format" => 1));
    }
    public function format($format = NULL, $currency = NULL)
    {
        if (is_null($format)) {
            $format = $this->defaultFormat;
        }
        if (is_null($currency)) {
            $currency = $this->currency;
        }
        if (!is_array($currency)) {
            $currency = $this->defaultCurrencyDescriptor;
        } else {
            foreach ($this->defaultCurrencyDescriptor as $key => $value) {
                if (!isset($currency[$key])) {
                    $currency[$key] = $value;
                }
            }
        }
        $format_dm = "2";
        $format_dp = ".";
        $format_ts = "";
        if ($currency["format"] == 2) {
            $format_dm = "2";
            $format_dp = ".";
            $format_ts = ",";
        } else {
            if ($currency["format"] == 3) {
                $format_dm = "2";
                $format_dp = ",";
                $format_ts = ".";
            } else {
                if ($currency["format"] == 4) {
                    $format_dm = "0";
                    $format_dp = "";
                    $format_ts = ",";
                }
            }
        }
        $formattedAmount = number_format($this->price, $format_dm, $format_dp, $format_ts);
        $format = str_replace(self::PREFIX, $currency["prefix"], $format);
        $format = str_replace(self::PRICE, $formattedAmount, $format);
        $format = str_replace(self::SUFFIX, $currency["suffix"], $format);
        return $format;
    }
    public function getCurrency()
    {
        return $this->currency;
    }
    public static function adjustDecimals($amount, $currencyCode)
    {
        if (is_numeric($amount)) {
            $currenciesWithoutDecimals = array("BYR", "BIF", "CLP", "KMF", "DJF", "HUF", "ISK", "JPY", "MGA", "MZN", "PYG", "RWF", "KRW", "VUV");
            if (in_array($currencyCode, $currenciesWithoutDecimals)) {
                $amount = (int) round($amount);
            }
        }
        return $amount;
    }
}

?>