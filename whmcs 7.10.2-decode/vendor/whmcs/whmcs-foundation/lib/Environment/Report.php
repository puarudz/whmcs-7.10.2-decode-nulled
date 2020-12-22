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

class Report
{
    private $type = NULL;
    private $components = NULL;
    const VERSION = "1.0.0";
    const API_SPEC = "1.0.0";
    const TYPE_EVENT = 0;
    const TYPE_STATE = 1;
    public function __construct($type, array $components = array())
    {
        $this->type = $type;
        $this->components = $components;
    }
    public function report()
    {
        return array("info" => $this->info(), "data" => $this->data(), "api" => $this->apiSpec());
    }
    public function toJson()
    {
        return json_encode($this->report());
    }
    public function info()
    {
        return array("type" => $this->type(), "systemId" => $this->systemId(), "version" => $this->version());
    }
    public function data()
    {
        $data = array();
        foreach ($this->components as $component) {
            if ($component instanceof ComponentInterface) {
                $data[] = $component->report($this);
            }
        }
        return $data;
    }
    public function type()
    {
        return $this->type;
    }
    public function systemId()
    {
        return WHMCS::systemId();
    }
    public function apiSpec()
    {
        return static::API_SPEC;
    }
    public function version()
    {
        return static::VERSION;
    }
}

?>