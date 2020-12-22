<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Registrar\GoDaddy\Api;

class Response
{
    public $headers = NULL;
    public $status_code = NULL;
    public $body = NULL;
    public function __construct(\GuzzleHttp\Message\ResponseInterface $response)
    {
        $this->headers = $response->getHeaders();
        $this->status_code = $response->getStatusCode();
        $this->body = json_decode($response->getBody());
    }
}

?>