<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing\Quote;

class Item extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblquoteitems";
    protected $booleans = array("taxable");
    protected $columnMap = array("isTaxable" => "taxable");
    public function quote()
    {
        return $this->belongsTo("WHMCS\\Billing\\Quote", "quoteid");
    }
}

?>