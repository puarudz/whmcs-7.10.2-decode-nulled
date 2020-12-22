<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Scheduling\Task;

trait DecoratorItemTrait
{
    protected $icon = "fas fa-cube";
    protected $successCountIdentifier = 0;
    protected $successKeyword = "Completed";
    protected $failureCountIdentifier = 0;
    protected $failureKeyword = "Failed";
    protected $isBooleanStatus = false;
    protected $hasDetail = false;
    public function getIcon()
    {
        return $this->icon;
    }
    public function getSuccessCountIdentifier()
    {
        return $this->successCountIdentifier;
    }
    public function getFailureCountIdentifier()
    {
        return $this->failureCountIdentifier;
    }
    public function getSuccessKeyword()
    {
        return $this->successKeyword;
    }
    public function getFailureKeyword()
    {
        return $this->failureKeyword;
    }
    public function isBooleanStatusItem()
    {
        return (bool) $this->isBooleanStatus;
    }
    public function hasDetail()
    {
        return (bool) $this->hasDetail;
    }
}

?>