<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Installer;

class DatabaseContent
{
    protected $schemaDirectory = "";
    public function __construct($schemaDirectory = NULL)
    {
        if (!$schemaDirectory) {
            $schemaDirectory = $this->getDefaultSchemaDirectory();
        }
        $this->setSchemaDirectory($schemaDirectory);
    }
    public function getDefaultSchemaDirectory()
    {
        return ROOTDIR . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "install" . DIRECTORY_SEPARATOR;
    }
    public function getSchemaDirectory()
    {
        return $this->schemaDirectory;
    }
    public function setSchemaDirectory($schemaDirectory)
    {
        $this->schemaDirectory = $schemaDirectory;
        return $this;
    }
    public function getDatabaseSeedContent()
    {
        $installSchema = $installData = "";
        $resourcesPath = $this->getSchemaDirectory();
        foreach (glob($resourcesPath . "*.schema.sql") as $filename) {
            $installSchema .= file_get_contents($filename);
        }
        foreach (glob($resourcesPath . "*.data.sql") as $filename) {
            $installData .= file_get_contents($filename);
        }
        return $installSchema . "\n" . $installData;
    }
}

?>