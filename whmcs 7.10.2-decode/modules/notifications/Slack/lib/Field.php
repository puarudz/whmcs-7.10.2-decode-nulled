<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Notification\Slack;

class Field
{
    public $title = "";
    public $value = "";
    public $short = false;
    public function title($title)
    {
        $this->title = trim($title);
        return $this;
    }
    public function value($value)
    {
        $this->value = trim($value);
        return $this;
    }
    public function short()
    {
        $this->short = true;
        return $this;
    }
    public function toArray()
    {
        $field = array("title" => $this->title, "value" => $this->value, "short" => $this->short);
        return $field;
    }
}

?>