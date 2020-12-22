<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\File;

class FileAsset
{
    const TYPE_CLIENT_FILES = "client_files";
    const TYPE_DOWNLOADS = "downloads";
    const TYPE_EMAIL_ATTACHMENTS = "email_attachments";
    const TYPE_EMAIL_IMAGES = "email_images";
    const TYPE_EMAIL_TEMPLATE_ATTACHMENTS = "email_template_attachments";
    const TYPE_KB_IMAGES = "kb_images";
    const TYPE_PM_FILES = "pm_files";
    const TYPE_TICKET_ATTACHMENTS = "ticket_attachments";
    const TYPES = NULL;
    const NO_MIGRATION_TYPES = NULL;
    public static function canMigrate($assetType)
    {
        return !in_array($assetType, self::NO_MIGRATION_TYPES);
    }
    public static function validType($assetType)
    {
        return (bool) array_key_exists($assetType, self::TYPES);
    }
    public static function getTypeName($assetType)
    {
        return self::validType($assetType) ? self::TYPES[$assetType] : null;
    }
    public static function getMimeTypeByExtension($filename)
    {
        $types = array("css" => "text/css", "js" => "application/x-javascript", "pdf" => "application/pdf", "txt" => "text/plain");
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($extension && isset($types[$extension])) {
            return $types[$extension];
        }
        return "application/octet-stream";
    }
    public static function disallowHtmlMimeType($mimeType)
    {
        if (strpos($mimeType, "html") !== false) {
            $contentType = "text/plain";
        }
        return $mimeType;
    }
}

?>