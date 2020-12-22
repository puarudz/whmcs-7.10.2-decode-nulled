<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Updater\Version;

class Version720beta2 extends IncrementalVersion
{
    protected $updateActions = array("addPaymentReversalChangeSettings");
    protected function addPaymentReversalChangeSettings()
    {
        \WHMCS\Config\Setting::setValue("ReversalChangeInvoiceStatus", 1);
        \WHMCS\Config\Setting::setValue("ReversalChangeDueDates", 1);
        return $this;
    }
}

?>