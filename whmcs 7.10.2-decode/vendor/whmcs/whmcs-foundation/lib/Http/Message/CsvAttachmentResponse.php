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

class CsvAttachmentResponse extends AbstractAttachmentResponse
{
    public function __construct($data, $attachmentFilename, $status = 200, array $headers = array())
    {
        $body = array();
        if (is_array($data)) {
            $body = $this->prepareData($data);
        }
        parent::__construct(implode(PHP_EOL, $body), $attachmentFilename, $status, $headers);
    }
    protected function prepareData(array $data)
    {
        $body = array();
        foreach ($data as $row) {
            $cellData = array();
            foreach ($row as $cell) {
                $cell = \WHMCS\Input\Sanitize::decode($cell);
                $cell = strip_tags($cell);
                $cellData[] = sprintf("\"%s\"", str_replace("\"", "\"\"", $cell));
            }
            $body[] = implode(",", $cellData);
        }
        return $body;
    }
    protected function createDataStream()
    {
        $body = new \Zend\Diactoros\Stream("php://temp", "wb+");
        $body->write($this->getData());
        $body->rewind();
        return $body;
    }
    protected function getDataContentType()
    {
        return "text/csv";
    }
    protected function getDataContentLength()
    {
        return strlen($this->getData());
    }
}

?>