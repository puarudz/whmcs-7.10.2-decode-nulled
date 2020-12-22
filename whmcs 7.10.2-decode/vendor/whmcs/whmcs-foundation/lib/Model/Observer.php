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

class Observer
{
    public function creating(\Illuminate\Database\Eloquent\Model $model)
    {
        $this->enforceUniqueConstraint($model);
    }
    public function updating(\Illuminate\Database\Eloquent\Model $model)
    {
        $this->enforceUniqueConstraint($model);
        $this->enforceGuardedForUpdateProperties($model);
    }
    protected function enforceUniqueConstraint(\Illuminate\Database\Eloquent\Model $model)
    {
        $class = get_class($model);
        foreach ($model->unique as $property) {
            if ($model->isDirty($property)) {
                $existingModelQuery = $class::where($property, "=", $model->{$property});
                if ($model->exists) {
                    $existingModelQuery->where("id", "!=", $model->id);
                }
                if (0 < $existingModelQuery->count()) {
                    throw new \WHMCS\Exception\Model\UniqueConstraint("A \"" . $class . "\" record with \"" . $property . "\" value \"" . $model->{$property} . "\" already exists.");
                }
            }
        }
    }
    protected function enforceGuardedForUpdateProperties(\Illuminate\Database\Eloquent\Model $model)
    {
        $class = get_class($model);
        foreach ($model->guardedForUpdate as $property) {
            if ($model->isDirty($property)) {
                throw new \WHMCS\Exception\Model\GuardedForUpdate("The \"" . $class . "\" record \"" . $property . "\" property is guarded against updates.");
            }
        }
    }
}

?>