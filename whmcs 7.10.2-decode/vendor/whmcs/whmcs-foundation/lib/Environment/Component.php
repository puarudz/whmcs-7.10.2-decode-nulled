<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Environment;

class Component implements ComponentInterface
{
    protected $name = NULL;
    protected $topics = array();
    public function __construct($name)
    {
        $this->name = $name;
    }
    public function name()
    {
        return $this->name;
    }
    public function addTopic($name, $closure)
    {
        if ($closure instanceof \Closure || is_object($closure) && is_callable($closure) || is_array($closure) && count($closure) === 2 && is_object($closure[0]) && method_exists($closure[0], $closure[1])) {
            $this->topics[$name] = $closure;
            return $this;
        }
        throw new \RuntimeException("Component topic closure not callable");
    }
    public function report(Report $report)
    {
        return array("name" => $this->name(), "topics" => $this->data($report));
    }
    protected function data(Report $report)
    {
        $data = array();
        foreach ($this->topics as $topic => $closure) {
            try {
                $data[] = array("name" => $topic, "data" => $closure($this, $report));
            } catch (\Exception $e) {
                logActivity("Unexpected error during component data aggregation");
            }
        }
        return $data;
    }
}

?>