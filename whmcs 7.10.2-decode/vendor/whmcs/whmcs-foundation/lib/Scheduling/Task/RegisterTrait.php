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

abstract class RegisterTrait
{
    protected $outputInstances = array();
    protected $details = array("success" => array(), "failure" => array());
    public function output($key)
    {
        $namespaceKey = $this->getNamespace() . "." . $key;
        if (empty($this->outputInstances[$key])) {
            $outputKeys = $this->getOutputKeys();
            $friendlyName = isset($outputKeys[$key]["name"]) ? $outputKeys[$key]["name"] : $key;
            $defaultValue = isset($outputKeys[$key]["defaultValue"]) ? $outputKeys[$key]["defaultValue"] : 0;
            $output = new \WHMCS\Log\Register();
            $output->setNamespace($namespaceKey);
            $output->setName($friendlyName);
            $output->setNamespaceId($this->id);
            $output->setValue($defaultValue);
            $this->outputInstances[$key] = $output;
        }
        return $this->outputInstances[$key];
    }
    public function getNamespace()
    {
        if (method_exists($this, "getSystemName")) {
            return $this->getSystemName();
        }
        $classname = static::class;
        $namespaces = explode("\\", $classname);
        return array_pop($namespaces);
    }
    public function getLatestOutputs(array $outputKeys = array())
    {
        if (empty($outputKeys)) {
            $namespaceKeys = array_keys($this->getOutputKeys());
        } else {
            $namespaceKeys = $outputKeys;
        }
        $namespace = $this->getNamespace();
        $applyNamespace = function ($value) use($namespace) {
            return $namespace . "." . $value;
        };
        $namespaces = array_map($applyNamespace, $namespaceKeys);
        return (new \WHMCS\Log\Register())->latestByNamespaces($namespaces, $this->id);
    }
    public function getOutputsSince(\WHMCS\Carbon $since, array $outputKeys = array())
    {
        if (empty($outputKeys)) {
            $namespaceKeys = array_keys($this->getOutputKeys());
        } else {
            $namespaceKeys = $outputKeys;
        }
        $namespace = $this->getNamespace();
        $applyNamespace = function ($value) use($namespace) {
            return $namespace . "." . $value;
        };
        $namespaces = array_map($applyNamespace, $namespaceKeys);
        return (new \WHMCS\Log\Register())->sinceByNamespace($since, $namespaces, $this->id);
    }
    public function addSuccess(array $data)
    {
        if (!array_key_exists("success", $this->details)) {
            $this->details["success"] = array();
        }
        $this->details["success"][] = $data;
        return $this;
    }
    public function getSuccesses()
    {
        if (!array_key_exists("success", $this->details)) {
            $this->details["success"] = array();
        }
        return $this->details["success"];
    }
    public function addFailure(array $data)
    {
        if (!array_key_exists("failure", $this->details)) {
            $this->details["failure"] = array();
        }
        $this->details["failure"][] = $data;
        return $this;
    }
    public function getFailures()
    {
        if (!array_key_exists("failure", $this->details)) {
            $this->details["failure"] = array();
        }
        return $this->details["failure"];
    }
    public function addCustom($type, array $data)
    {
        if (!array_key_exists($type, $this->details)) {
            $this->details[$type] = array();
        }
        $this->details[$type][] = $data;
        return $this;
    }
    public function getCustom($type)
    {
        if (!array_key_exists($type, $this->details)) {
            $this->details[$type] = array();
        }
        return $this->details[$type];
    }
    public function getDetail()
    {
        return $this->details;
    }
    public function setDetails(array $details)
    {
        $this->details = $details;
        return $this;
    }
    public abstract function getOutputKeys();
}

?>