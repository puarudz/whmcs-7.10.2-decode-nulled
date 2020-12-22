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

abstract class AbstractViewableResponse extends \Zend\Diactoros\Response\HtmlResponse
{
    protected $getBodyFromPrivateStream = false;
    public function __construct($data = "", $status = 200, array $headers = array())
    {
        parent::__construct($data, $status, $headers);
    }
    public function getBody()
    {
        if ($this->getBodyFromPrivateStream) {
            return parent::getBody();
        }
        $body = new \Zend\Diactoros\Stream("php://temp", "wb+");
        $body->write($this->getOutputContent());
        $body->rewind();
        return $body;
    }
    protected abstract function getOutputContent();
}

?>