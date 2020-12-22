<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing;

class Quote extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblquotes";
    public $timestamps = false;
    protected $columnMap = array("status" => "stage", "validUntilDate" => "validuntil", "clientId" => "userid", "lastModifiedDate" => "lastmodified", "customerNotes" => "customernotes", "adminNotes" => "adminnotes", "dateCreated" => "datecreated", "dateSent" => "datesent", "dateAccepted" => "dateaccepted");
    protected $dates = array("validuntil", "datecreated", "lastmodified", "datesent", "dateaccepted");
    public function client()
    {
        return $this->belongsTo("WHMCS\\User\\Client", "userid");
    }
    public function items()
    {
        return $this->hasMany("WHMCS\\Billing\\Quote\\Item", "quoteid");
    }
}

?>