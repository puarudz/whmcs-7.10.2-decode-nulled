<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Apps\Hero;

class Collection
{
    public $heros = NULL;
    public function __construct()
    {
        $this->heros = (new \WHMCS\Apps\Feed())->heros();
    }
    public function get()
    {
        $country = strtolower(\WHMCS\Config\Setting::getValue("DefaultCountry"));
        $heros = array_key_exists($country, $this->heros) ? $this->heros[$country] : $this->heros["default"];
        foreach ($heros as $key => $values) {
            $heros[$key] = new Model($values);
        }
        return $heros;
    }
}

?>