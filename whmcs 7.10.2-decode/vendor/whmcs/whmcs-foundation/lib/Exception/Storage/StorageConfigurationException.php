<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Exception\Storage;

class StorageConfigurationException extends StorageException
{
    private $fields = array();
    public function __construct(array $fields)
    {
        parent::__construct(join(" ", array_values($fields)));
        $this->fields = $fields;
    }
    public function getFields()
    {
        return $this->fields;
    }
}

?>