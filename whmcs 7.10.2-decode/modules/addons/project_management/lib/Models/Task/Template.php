<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Addon\ProjectManagement\Models\Task;

class Template extends \WHMCS\Model\AbstractModel
{
    protected $table = "mod_projecttasktpls";
    public $timestamps = false;
    protected $fillable = array("name", "tasks");
    protected $casts = array("tasks" => "array");
    public function createTable($drop = false)
    {
        $tableName = $this->table;
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($tableName);
        }
        if (!$schemaBuilder->hasTable($tableName)) {
            $schemaBuilder->create($tableName, function ($table) {
                $table->increments("id");
                $table->string("name", 256)->default("");
                $table->text("tasks");
            });
        }
    }
    public function dropTable()
    {
        $tableName = $this->table;
        $schemaBuilder = \WHMCS\Database\Capsule::schema();
        $schemaBuilder->dropIfExists($tableName);
    }
}

?>