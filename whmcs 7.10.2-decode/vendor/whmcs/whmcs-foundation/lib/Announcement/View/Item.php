<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Announcement\View;

class Item extends Index
{
    protected function initializeView()
    {
        parent::initializeView();
        $this->setTemplate("viewannouncement");
    }
}

?>