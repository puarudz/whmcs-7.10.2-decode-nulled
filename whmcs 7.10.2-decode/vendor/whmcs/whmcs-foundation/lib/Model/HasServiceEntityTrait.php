<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Model;

trait HasServiceEntityTrait
{
    protected static $serviceRelationTypes = NULL;
    public static function loadRelationClassMap()
    {
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap(self::$serviceRelationTypes);
    }
    public function relationEntity()
    {
        return $this->morphTo("rel");
    }
    public function scopeOfRelated($query, $relation)
    {
        $relType = get_class($relation);
        foreach (self::$serviceRelationTypes as $type => $baseClass) {
            if ($relation instanceof $baseClass) {
                $relType = $type;
            }
        }
        return $query->where("rel_type", $relType)->where("rel_id", $relation->id);
    }
}

?>