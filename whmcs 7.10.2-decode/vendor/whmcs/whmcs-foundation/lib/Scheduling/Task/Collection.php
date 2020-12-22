<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Scheduling\Task;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
    public function __construct($items)
    {
        $items = $this->filterToTasks($items);
        parent::__construct($items);
    }
    protected function filterToTasks($models)
    {
        $tasks = array();
        foreach ($models as $model) {
            $className = $model->class_name;
            $modelClass = get_class($model);
            if ($className instanceof $modelClass) {
                $tasks[] = $model;
            } else {
                if (class_exists($className)) {
                    $instance = new $className();
                    if ($instance instanceof TaskInterface && $instance instanceof \Illuminate\Database\Eloquent\Model) {
                        $tasks[] = $instance->newInstance($model->getAttributes(), $model->exists);
                    }
                } else {
                    if (!$className && !$model->exists && $model instanceof TaskInterface) {
                        $tasks[] = $model;
                    }
                }
            }
        }
        return $tasks;
    }
    public function transformToTasks()
    {
        return new static($this->filterToTasks($this->items));
    }
    public function isEnabled()
    {
        return $this->filter(function (TaskInterface $task) {
            return $task->isEnabled();
        });
    }
    public function isLevel($level)
    {
        return $this->filter(function ($task) use($level) {
            if ($task->getAccessLevel() == $level) {
                return true;
            }
            return false;
        });
    }
}

?>