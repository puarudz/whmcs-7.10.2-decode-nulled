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

final class Storage
{
    private function createFilesystem($assetType, $subPath = "")
    {
        $subPath = ltrim($subPath, DIRECTORY_SEPARATOR);
        $assetSetting = Configuration\FileAssetSetting::forAssetType($assetType)->first();
        if (!$assetSetting) {
            throw new \WHMCS\Exception\Storage\StorageException(sprintf("Cannot find storage setting for asset type \"%s\" (%s)", $assetType, FileAsset::getTypeName($assetType) ?: "unknown"));
        }
        $fileSystem = new Filesystem($assetSetting->createFilesystemAdapter($subPath));
        $fileSystem->setAssetSetting($assetSetting);
        return $fileSystem;
    }
    public function clientFiles()
    {
        return $this->createFilesystem(FileAsset::TYPE_CLIENT_FILES);
    }
    public function downloads()
    {
        return $this->createFilesystem(FileAsset::TYPE_DOWNLOADS);
    }
    public function emailAttachments()
    {
        return $this->createFilesystem(FileAsset::TYPE_EMAIL_ATTACHMENTS);
    }
    public function emailTemplateAttachments()
    {
        return $this->createFilesystem(FileAsset::TYPE_EMAIL_TEMPLATE_ATTACHMENTS);
    }
    public function projectManagementFiles($projectId = NULL)
    {
        $subPath = "";
        if (!is_null($projectId)) {
            $subPath = DIRECTORY_SEPARATOR . (int) $projectId;
        }
        return $this->createFilesystem(FileAsset::TYPE_PM_FILES, $subPath);
    }
    public function ticketAttachments()
    {
        return $this->createFilesystem(FileAsset::TYPE_TICKET_ATTACHMENTS);
    }
    public function kbImages()
    {
        return $this->createFilesystem(FileAsset::TYPE_KB_IMAGES);
    }
    public function emailImages()
    {
        return $this->createFilesystem(FileAsset::TYPE_EMAIL_IMAGES);
    }
}

?>