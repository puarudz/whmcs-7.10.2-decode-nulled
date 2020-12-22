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

class Hit extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblaffiliates_hits";
    public $timestamps = false;
    public $dates = array("created_at");
    protected $fillable = array("affiliate_id", "referrer_id", "created_at");
    public function referrer()
    {
        return $this->belongsTo("WHMCS\\Affiliate\\Referrer", "referrer_id", "id");
    }
}

?>