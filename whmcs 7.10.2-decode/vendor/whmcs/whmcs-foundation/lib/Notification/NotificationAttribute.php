<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Notification;

class NotificationAttribute implements Contracts\NotificationAttributeInterface
{
    protected $label = "";
    protected $value = "";
    protected $url = "";
    protected $style = "";
    protected $icon = "";
    public function setLabel($label)
    {
        $this->label = trim($label);
        return $this;
    }
    public function setValue($value)
    {
        $this->value = trim($value);
        return $this;
    }
    public function setUrl($url)
    {
        $this->url = trim($url);
        return $this;
    }
    public function setStyle($style)
    {
        $this->style = trim($style);
        return $this;
    }
    public function setIcon($icon)
    {
        $this->icon = trim($icon);
        return $this;
    }
    public function getLabel()
    {
        return $this->label;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function getStyle()
    {
        return $this->style;
    }
    public function getIcon()
    {
        return $this->icon;
    }
}

?>