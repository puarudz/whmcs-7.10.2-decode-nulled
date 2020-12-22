<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\Utilities\Tools;

class EmailMarketer extends \WHMCS\Model\AbstractModel
{
    protected $table = "tblemailmarketer";
    protected $pivotTable = "tblemailmarketer_related_pivot";
    protected $columnMap = array("disabled" => "disable");
    public function createPivotTable($drop = false)
    {
        $schemaBuilder = \Illuminate\Database\Capsule\Manager::schema();
        if ($drop) {
            $schemaBuilder->dropIfExists($this->pivotTable);
        }
        if (!$schemaBuilder->hasTable($this->pivotTable)) {
            $schemaBuilder->create($this->pivotTable, function ($table) {
                $table->increments("id");
                $table->integer("task_id", false, true)->default(0);
                $table->integer("product_id", false, true)->default(0);
                $table->integer("addon_id", false, true)->default(0);
                $table->timestamp("created_at")->default("0000-00-00 00:00:00");
                $table->timestamp("updated_at")->default("0000-00-00 00:00:00");
            });
        }
    }
    public function setSettingsAttribute($settings)
    {
        if (is_array($settings)) {
            $settings = json_encode($settings);
        }
        if (!is_string($settings) || substr($settings, 0, 1) !== "{") {
            $settings = json_encode(array());
        }
        $this->attributes["settings"] = $settings;
    }
    public function getSettingsAttribute()
    {
        return json_decode($this->getRawAttribute("settings"), true);
    }
    public function products()
    {
        return $this->belongsToMany("WHMCS\\Product\\Product", $this->pivotTable, "task_id", "product_id")->withTimestamps();
    }
    public function addons()
    {
        return $this->belongsToMany("WHMCS\\Product\\Addon", $this->pivotTable, "task_id", "addon_id")->withTimestamps();
    }
}

?>