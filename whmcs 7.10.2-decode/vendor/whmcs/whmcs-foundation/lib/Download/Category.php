<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Download;

class Category extends \WHMCS\Model\AbstractModel
{
    protected $table = "tbldownloadcats";
    protected $columnMap = array("isHidden" => "hidden");
    protected $booleans = array("isHidden");
    public function parentCategory()
    {
        return $this->hasOne("WHMCS\\Download\\Category", "id", "parentid");
    }
    public function childCategories()
    {
        return $this->hasMany("WHMCS\\Download\\Category", "parentid");
    }
    public function downloads()
    {
        return $this->hasMany("WHMCS\\Download\\Download", "category");
    }
    public function scopeOfParent(\Illuminate\Database\Eloquent\Builder $query, $parentId = 0)
    {
        return $query->where("parentid", "=", $parentId);
    }
    public function scopeVisible(\Illuminate\Database\Eloquent\Builder $query)
    {
        return $query->where("hidden", "=", "0");
    }
}

?>