<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Ticket;

class Watchers extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblticket_watchers";
    public $timestamps = true;
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id");
                $table->integer("ticket_id", false, true);
                $table->integer("admin_id", false, true);
                $table->timestamps();
                $table->unique(array("ticket_id", "admin_id"), "admin_ticket_unique");
            });
        }
    }
    public function scopeOfTicket(\Illuminate\Database\Eloquent\Builder $query, $ticketId)
    {
        return $query->whereTicketId($ticketId);
    }
    public function scopeByAdmin(\Illuminate\Database\Eloquent\Builder $query, $adminId)
    {
        return $query->whereAdminId($adminId);
    }
}

?>