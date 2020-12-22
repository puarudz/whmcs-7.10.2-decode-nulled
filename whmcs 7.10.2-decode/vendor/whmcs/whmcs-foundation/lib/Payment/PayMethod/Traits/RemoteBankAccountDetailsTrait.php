<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\PayMethod\Traits;

trait RemoteBankAccountDetailsTrait
{
    use SensitiveDataTrait;
    public function getSensitiveDataAttributeName()
    {
        return "bank_data";
    }
    public function getAccountNumber()
    {
        return (string) $this->getSensitiveProperty("accountNumber");
    }
    public function setAccountNumber($value)
    {
        $this->setSensitiveProperty("accountNumber", substr($value, -4));
        return $this;
    }
    public function getAccountHolderName()
    {
        return (string) $this->getSensitiveProperty("accountHolderName");
    }
    public function setAccountHolderName($value)
    {
        $this->setSensitiveProperty("accountHolderName", $value);
        return $this;
    }
    public function getDisplayName()
    {
        $displayName = $this->getName();
        if (!$displayName) {
            $displayName = "Bank Account";
        }
        if ($this->getAccountNumber()) {
            $displayName .= "-" . substr($this->getAccountNumber(), -4);
        }
        return $displayName;
    }
    public function setMigrated()
    {
        $this->setSensitiveProperty("migrated", 1);
        return $this;
    }
    public function isMigrated()
    {
        return (bool) (int) $this->getSensitiveProperty("migrated");
    }
}

?>