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

trait HasModuleTrait
{
    protected $instantiatedModule = NULL;
    public function scopeOfModule($query, \WHMCS\Module\AbstractModule $module)
    {
        return $query->where("module_type", $module->getType())->where("module", $module->getLoadedModule());
    }
    public function getModuleNameAttribute()
    {
        return $this->getRawAttribute("module");
    }
    public function setModuleNameAttribute($value)
    {
        $this->attributes["module"] = $value;
    }
    public function getModuleTypeAttribute()
    {
        return $this->getRawAttribute("module_type");
    }
    public function setModuleTypeAttribute($value)
    {
        $this->attributes["module_type"] = $value;
    }
    public function setModuleAttribute($value)
    {
        $this->instantiatedModule = $name = $type = null;
        unset($this->attributes["module"]);
        if ($value instanceof \WHMCS\Module\AbstractModule) {
            $type = $value->getType();
            $name = $value->getLoadedModule();
        } else {
            if (is_string($value)) {
                if (strpos($value, "|") !== false) {
                    list($type, $name) = explode("|", $value, 2);
                } else {
                    $this->attributes["module"] = $value;
                }
            }
        }
        if ($type && $name) {
            $this->attributes["module"] = $name;
            $this->attributes["module_type"] = $type;
            $this->instantiatedModule = $value;
        }
    }
    public function getModuleAttribute()
    {
        if ($this->instantiatedModule) {
            return $this->instantiatedModule;
        }
        $type = $this->getRawAttribute("module_type");
        $name = $this->getRawAttribute("module");
        if ($type && $name) {
            $moduleHelper = new \WHMCS\Module\Module();
            try {
                $class = $moduleHelper->getClassByModuleType($type);
                $module = new $class();
                if ($module->load($name)) {
                    $this->instantiatedModule = $module;
                    return $module;
                }
            } catch (\Exception $e) {
            }
        }
        return null;
    }
}

?>