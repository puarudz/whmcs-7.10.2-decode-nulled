<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Network;

class NetworkIssue extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblnetworkissues";
    protected $columnMap = array("affectedType" => "type", "affectedOther" => "affecting", "affectedServerId" => "server", "lastUpdateDate" => "lastupdate");
    protected $dates = array("startdate", "enddate", "lastupdate");
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("order", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->orderBy("tblnetworkissues.startdate", "DESC")->orderBy("tblnetworkissues.enddate")->orderBy("tblnetworkissues.id");
        });
    }
}

?>