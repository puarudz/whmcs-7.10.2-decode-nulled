<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Http\Message;

class FileAttachmentResponse extends AbstractAttachmentResponse
{
    public function __construct($file, $attachmentFilename = NULL, $status = 200, array $headers = array())
    {
        $file = new \SplFileInfo($file);
        if (!$attachmentFilename) {
            $attachmentFilename = $file->getFilename();
        }
        parent::__construct($file, $attachmentFilename, $status, $headers);
    }
    protected function createDataStream()
    {
        return new \Zend\Diactoros\Stream($this->getData()->getRealPath(), "r");
    }
    protected function getDataContentType()
    {
        return (new \finfo(FILEINFO_MIME_TYPE))->file($this->getData()->getRealPath());
    }
    protected function getDataContentLength()
    {
        return $this->getData()->getSize();
    }
}

?>