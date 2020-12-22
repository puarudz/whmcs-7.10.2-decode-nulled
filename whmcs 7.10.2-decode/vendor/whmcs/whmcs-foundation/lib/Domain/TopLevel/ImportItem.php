<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Domain\TopLevel;

class ImportItem
{
    protected $extension = NULL;
    protected $registerPrice = NULL;
    protected $renewPrice = NULL;
    protected $graceFeePrice = NULL;
    protected $graceFeeDays = NULL;
    protected $redemptionFeePrice = NULL;
    protected $redemptionFeeDays = NULL;
    protected $transferPrice = NULL;
    protected $currencyCode = NULL;
    protected $minYears = 1;
    protected $yearsStep = 1;
    protected $maxYears = 1;
    protected $years = array();
    protected $eppRequired = false;
    public function setExtension($extension)
    {
        if (!is_string($extension)) {
            throw new \InvalidArgumentException("Extension must be a string");
        }
        $this->extension = "." . ltrim($extension, ".");
        return $this;
    }
    public function setRegisterPrice($price = NULL)
    {
        if (!is_null($price) && !is_numeric($price)) {
            throw new \InvalidArgumentException("Price must be numeric");
        }
        $this->registerPrice = $price;
        return $this;
    }
    public function setRenewPrice($price = NULL)
    {
        if (!is_null($price) && !is_numeric($price)) {
            throw new \InvalidArgumentException("Price must be numeric");
        }
        $this->renewPrice = $price;
        return $this;
    }
    public function setGraceFeePrice($price = NULL)
    {
        if (!is_null($price) && !is_numeric($price)) {
            throw new \InvalidArgumentException("Price must be numeric");
        }
        $this->graceFeePrice = $price;
        return $this;
    }
    public function setGraceFeeDays($days = NULL)
    {
        if (!is_null($days) && !is_numeric($days)) {
            throw new \InvalidArgumentException("Days must be numeric");
        }
        $this->graceFeeDays = $days;
        return $this;
    }
    public function setRedemptionFeePrice($price = NULL)
    {
        if (!is_null($price) && !is_numeric($price)) {
            throw new \InvalidArgumentException("Price must be numeric");
        }
        $this->redemptionFeePrice = $price;
        return $this;
    }
    public function setRedemptionFeeDays($days = NULL)
    {
        if (!is_null($days) && !is_numeric($days)) {
            throw new \InvalidArgumentException("Days must be numeric");
        }
        $this->redemptionFeeDays = $days;
        return $this;
    }
    public function setTransferPrice($price = NULL)
    {
        if (!is_null($price) && !is_numeric($price)) {
            throw new \InvalidArgumentException("Price must be numeric");
        }
        $this->transferPrice = $price;
        return $this;
    }
    public function setCurrency($currencyCode)
    {
        if (!is_string($currencyCode)) {
            throw new \InvalidArgumentException("Currency code must be a string");
        }
        $this->currencyCode = $currencyCode;
        return $this;
    }
    public function setYears(array $years)
    {
        $savedYears = array();
        foreach ($years as $key => $year) {
            if (0 < $year && $year <= 10) {
                $savedYears[] = $year;
            }
        }
        sort($savedYears, SORT_NUMERIC);
        $this->years = $savedYears;
        return $this;
    }
    public function setMinYears($years)
    {
        $this->minYears = (int) $years;
        return $this;
    }
    public function setMaxYears($years)
    {
        $this->maxYears = (int) $years;
        return $this;
    }
    public function setYearsStep($years)
    {
        $this->yearsStep = (int) $years;
        return $this;
    }
    public function setEppRequired($required)
    {
        $this->eppRequired = (bool) $required;
        return $this;
    }
    public function getExtension()
    {
        return $this->extension;
    }
    protected function isIdn()
    {
        return strpos($this->getExtension(), ".xn--") === 0;
    }
    protected function hasAsteriskInTld()
    {
        return stristr($this->getExtension(), "*") !== false;
    }
    public function isUnsupportedTld()
    {
        return $this->isIdn() || $this->hasAsteriskInTld();
    }
    public function getRegisterPrice()
    {
        return $this->registerPrice;
    }
    public function getRenewPrice()
    {
        return $this->renewPrice;
    }
    public function getGraceFeePrice()
    {
        return $this->graceFeePrice;
    }
    public function getGraceFeeDays()
    {
        return $this->graceFeeDays;
    }
    public function getRedemptionFeePrice()
    {
        return $this->redemptionFeePrice;
    }
    public function getRedemptionFeeDays()
    {
        return $this->redemptionFeeDays;
    }
    public function getTransferPrice()
    {
        return $this->transferPrice;
    }
    public function getMinYears()
    {
        $minYears = $this->minYears;
        if (1 <= count($this->getYears())) {
            $minYears = min($this->getYears());
        }
        return $minYears;
    }
    public function getMaxYears()
    {
        $maxYears = $this->maxYears;
        if (1 <= count($this->getYears())) {
            $maxYears = max($this->getYears());
        }
        return $maxYears;
    }
    public function getYearsStep()
    {
        return $this->yearsStep;
    }
    public function getCurrency()
    {
        return $this->currencyCode;
    }
    public function getRequiresEpp()
    {
        return $this->eppRequired;
    }
    public function getYears()
    {
        if (1 <= count($this->years)) {
            return $this->years;
        }
        return range($this->minYears, $this->maxYears, $this->yearsStep);
    }
    public function toArray()
    {
        return array("extension" => $this->getExtension(), "registerPrice" => $this->getRegisterPrice(), "renewPrice" => $this->getRenewPrice(), "transferPrice" => $this->getTransferPrice(), "graceFeePrice" => $this->getGraceFeePrice(), "graceFeeDays" => $this->getGraceFeeDays(), "redemptionFeePrice" => $this->getRedemptionFeePrice(), "redemptionFeeDays" => $this->getRedemptionFeeDays(), "currency" => $this->getCurrency(), "years" => $this->years, "minYears" => $this->getMinYears(), "maxYears" => $this->getMaxYears(), "yearsStep" => $this->getYearsStep(), "eppRequired" => $this->getRequiresEpp());
    }
    public static function fromArray(array $data)
    {
        $item = (new self())->setExtension($data["extension"])->setCurrency($data["currency"])->setYears($data["years"])->setMinYears($data["minYears"])->setMaxYears($data["maxYears"])->setYearsStep($data["yearsStep"])->setEppRequired($data["eppRequired"]);
        if (isset($data["registerPrice"]) && is_numeric($data["registerPrice"])) {
            $item->setRegisterPrice($data["registerPrice"]);
        }
        if (isset($data["renewPrice"]) && is_numeric($data["renewPrice"])) {
            $item->setRenewPrice($data["renewPrice"]);
        }
        if (isset($data["transferPrice"]) && is_numeric($data["transferPrice"])) {
            $item->setTransferPrice($data["transferPrice"]);
        }
        if (isset($data["graceFeePrice"]) && is_numeric($data["graceFeePrice"])) {
            $item->setGraceFeePrice($data["graceFeePrice"]);
        }
        if (isset($data["graceFeeDays"]) && is_numeric($data["graceFeeDays"])) {
            $item->setGraceFeeDays($data["graceFeeDays"]);
        }
        if (isset($data["redemptionFeePrice"]) && is_numeric($data["redemptionFeePrice"])) {
            $item->setRedemptionFeePrice($data["redemptionFeePrice"]);
        }
        if (isset($data["redemptionFeeDays"]) && is_numeric($data["redemptionFeeDays"])) {
            $item->setRedemptionFeeDays($data["redemptionFeeDays"]);
        }
        return $item;
    }
}

?>