<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Affiliate;

class Referrer extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblaffiliates_referrers";
    protected $fillable = array("affiliate_id", "referrer");
    public function hits()
    {
        return $this->hasMany("WHMCS\\Affiliate\\Hit", "referrer_id");
    }
}

?>