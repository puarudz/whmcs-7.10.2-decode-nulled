<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Support\Ticket;

class Reply extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblticketreplies";
    protected $columnMap = array("clientId" => "userid", "contactId" => "contactid");
    protected $dates = array("date");
    protected $hidden = array("editor");
    public $timestamps = false;
    const CREATED_AT = "date";
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope("ordered", function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->orderBy("tblticketreplies.date");
        });
    }
    public function ticket()
    {
        return $this->belongsTo("WHMCS\\Support\\Ticket", "tid");
    }
}

?>