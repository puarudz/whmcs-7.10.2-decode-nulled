<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Apps\Meta\Schema;

class AbstractVersion
{
    public $metaData = array();
    public function __construct(array $metaData)
    {
        $this->metaData = $metaData;
    }
    protected function meta($key)
    {
        $parts = explode(".", $key);
        $response = $this->metaData;
        foreach ($parts as $part) {
            if (isset($response[$part])) {
                $response = $response[$part];
            } else {
                return null;
            }
        }
        return $response;
    }
}

?>