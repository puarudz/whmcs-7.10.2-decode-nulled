<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Service;

class CancellationRequest extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblcancelrequests";
    protected $columnMap = array("serviceId" => "relid", "whenToCancel" => "type");
    protected $dates = array("date");
    public function service()
    {
        return $this->belongsTo("WHMCS\\Service\\Service", "relid");
    }
}

?>