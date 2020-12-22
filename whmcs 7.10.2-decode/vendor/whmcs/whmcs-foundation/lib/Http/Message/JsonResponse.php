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

class JsonResponse extends \Zend\Diactoros\Response\JsonResponse
{
    use \WHMCS\Http\DataTrait;
    use \WHMCS\Http\PriceDataTrait;
    public function __construct($data, $status = 200, array $headers = array(), $encodingOptions = \Zend\Diactoros\Response\JsonResponse::DEFAULT_JSON_FLAGS)
    {
        $data = $this->preprocessData($data);
        \Zend\Diactoros\Response\JsonResponse::__construct($data, $status, $headers, $encodingOptions);
    }
    private function preprocessData($data)
    {
        $data = $this->mutatePriceToFull($data);
        $this->setRawData($data);
        return $data;
    }
    public function withData($data, $encodingOptions = \Zend\Diactoros\Response\JsonResponse::DEFAULT_JSON_FLAGS)
    {
        $data = $this->preprocessData($data);
        if (is_resource($data)) {
            throw new \InvalidArgumentException("Cannot JSON encode resources");
        }
        json_encode(null);
        $json = json_encode($data, $encodingOptions);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(sprintf("Unable to encode data to JSON in %s: %s", "WHMCS\\Http\\Message\\JsonResponse", json_last_error_msg()));
        }
        $body = new \Zend\Diactoros\Stream("php://temp", "wb+");
        $body->write($json);
        $body->rewind();
        return parent::withBody($body);
    }
}

?>