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

abstract class AbstractUnit implements \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface
{
    private $name = NULL;
    private $singlePerUnitName = NULL;
    private $pluralPerUnitName = NULL;
    private $prefix = NULL;
    private $suffix = NULL;
    public function __construct($name, $singlePerUnitName = NULL, $pluralPerUnitName = NULL, $prefix = NULL, $suffix = NULL)
    {
        $this->name = $name;
        $this->singlePerUnitName = $singlePerUnitName;
        $this->pluralPerUnitName = $pluralPerUnitName;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }
    public function name()
    {
        return (string) $this->name;
    }
    public function perUnitName($value = 0)
    {
        if (round($value) == 1) {
            $name = $this->singlePerUnitName;
            if (is_null($name)) {
                $name = $this->pluralPerUnitName;
            }
        } else {
            $name = $this->pluralPerUnitName;
            if (is_null($name)) {
                $name = $this->singlePerUnitName;
            }
        }
        if (is_null($name)) {
            $name = $this->suffix();
        }
        if (is_null($name)) {
            $name = $this->name();
        }
        return $name;
    }
    public function prefix()
    {
        return (string) $this->prefix;
    }
    public function suffix()
    {
        return (string) $this->suffix;
    }
    public function decorate($value)
    {
        $value = $this->formatForType($value);
        return sprintf("%s%s%s", $this->prefix() ? $this->prefix() . " " : "", $value, $this->suffix() ? " " . $this->suffix() : "");
    }
    public function roundForType($value)
    {
        switch ($this->type()) {
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT:
                $value = round($value, 0);
                break;
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_FLOAT_PRECISION_LOW:
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_CURRENCY:
                $value = round($value, 2);
                break;
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_MICROTIME:
                $value = round($value, 6);
                break;
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_FLOAT_PRECISION_HIGH:
            default:
                $value = round($value, 4);
                break;
        }
        return $value;
    }
    public function formatForType($value)
    {
        switch ($this->type()) {
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_INT:
                $value = number_format($value, 0, ".", "");
                break;
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_FLOAT_PRECISION_LOW:
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_CURRENCY:
                $value = number_format($value, 2, ".", "");
                break;
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_MICROTIME:
                $value = number_format($value, 6, ".", "");
                break;
            case \WHMCS\UsageBilling\Contracts\Metrics\UnitInterface::TYPE_FLOAT_PRECISION_HIGH:
            default:
                $value = number_format($value, 4, ".", "");
                break;
        }
        return $value;
    }
    public abstract function type();
}

?>