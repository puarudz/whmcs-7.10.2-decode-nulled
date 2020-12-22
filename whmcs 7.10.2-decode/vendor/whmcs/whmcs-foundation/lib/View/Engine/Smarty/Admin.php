<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\View\Engine\Smarty;

class Admin extends \WHMCS\Smarty implements \WHMCS\View\Engine\VariableAccessorInterface
{
    public function __construct($admin = true, $policyName = NULL)
    {
        parent::__construct($admin, $policyName);
    }
}

?>