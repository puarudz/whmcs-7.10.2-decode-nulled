<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\Filter\Iterator;

class CallbackIterator extends \FilterIterator
{
    private $callback = NULL;
    public function __construct(\Iterator $iterator, $conditionalCallback)
    {
        $this->setCallback($conditionalCallback);
        parent::__construct($iterator);
    }
    public function accept()
    {
        $item = $this->getInnerIterator()->current();
        if (call_user_func($this->getCallback(), $item)) {
            return true;
        }
        return false;
    }
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }
    public function getCallback()
    {
        return $this->callback;
    }
}

?>