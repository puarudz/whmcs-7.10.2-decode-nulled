<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Apps\Meta\Sources;

class LocalFile
{
    public static function build($filePath)
    {
        $local = new self();
        $data = $local->load($filePath);
        return $local->parseJson($data);
    }
    public function load($filePath)
    {
        if ($filePath && file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true);
        }
        throw new \WHMCS\Exception("File not found: " . $filePath);
    }
    protected function getSchemaMajorVersion($data)
    {
        if (isset($data["schema"])) {
            $versionParts = explode(".", $data["schema"]);
            return $versionParts[0];
        }
        throw new \WHMCS\Exception("Schema not defined.");
    }
    public function parseJson($data)
    {
        $majorVersion = $this->getSchemaMajorVersion($data);
        $schemaClass = "\\WHMCS\\Apps\\Meta\\Schema\\Version" . (int) $majorVersion . "\\Local";
        if (class_exists($schemaClass)) {
            return new $schemaClass($data);
        }
        throw new \WHMCS\Exception("Invalid schema version.");
    }
}

?>