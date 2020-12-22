<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

class Plesk_Registry
{
    private static $_instance = NULL;
    private $_instances = array();
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __get($name)
    {
        if (isset($this->_instances[$name])) {
            return $this->_instances[$name];
        }
        throw new Exception("There is no object \"" . $name . "\" in the registry.");
    }
    public function __set($name, $value)
    {
        $this->_instances[$name] = $value;
    }
    public function __isset($name)
    {
        return isset($this->_instances[$name]) ? true : false;
    }
}

?>