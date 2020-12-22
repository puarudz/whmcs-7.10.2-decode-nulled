<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Admin\ApplicationSupport\View\Traits;

trait BodyContentTrait
{
    protected $bodyContent = "";
    public function getBodyContent()
    {
        return $this->bodyContent;
    }
    public function setBodyContent($content)
    {
        $this->bodyContent = (string) $content;
        return $this;
    }
}

?>