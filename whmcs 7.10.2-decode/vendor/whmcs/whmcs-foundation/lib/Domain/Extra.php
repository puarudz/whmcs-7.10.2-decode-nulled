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

class Extra extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbldomains_extra";
    protected $fillable = array("domain_id", "name");
    public $guardedForUpdate = array("domain_id", "name");
    public function domain()
    {
        return $this->belongsTo("WHMCS\\Domain\\Domain", "domain_id");
    }
    public function createTable($drop = false)
    {
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->getTable());
        }
        if (!$schemaBuilder->hasTable($this->getTable())) {
            $schemaBuilder->create($this->getTable(), function ($table) {
                $table->increments("id")->notNull();
                $table->integer("domain_id")->default(0);
                $table->string("name", 32)->default("");
                $table->string("value", 255)->default("");
                $table->timestamp("created_at")->default("0000-00-00 00:00:00");
                $table->timestamp("updated_at")->default("0000-00-00 00:00:00");
                $table->index("type");
                $table->unique(array("domain_id", "type"));
            });
        }
    }
}

?>