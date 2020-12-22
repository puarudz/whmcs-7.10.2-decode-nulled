<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Domain;

class AdditionalField extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbldomainsadditionalfields";
    protected $fillable = array("domainid", "name");
    public function domain()
    {
        return $this->belongsTo("WHMCS\\Domain\\Domain", "domainid");
    }
}

?>