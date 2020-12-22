<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Notification\Hipchat;

class Message
{
    public $format = "html";
    public $notify = false;
    public $level = "info";
    public $color = "gray";
    public $from = "";
    public $content = "";
    public $card = NULL;
    public function from($from)
    {
        $this->from = trim($from);
        return $this;
    }
    public function message($content = "")
    {
        $this->content = $content;
        return $this;
    }
    public function notify($notify = true)
    {
        $this->notify = $notify;
        return $this;
    }
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }
    public function card($card)
    {
        $this->card = $card;
        return $this;
    }
    public function toArray()
    {
        $message = array("from" => $this->from, "message_format" => $this->format, "color" => $this->color, "notify" => $this->notify, "message" => $this->content);
        if (!empty($this->card)) {
            $message["card"] = $this->card->toArray();
        }
        return $message;
    }
}

?>