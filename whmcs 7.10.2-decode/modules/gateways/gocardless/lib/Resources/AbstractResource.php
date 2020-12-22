<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Module\Gateway\GoCardless\Resources;

class AbstractResource
{
    protected $params = array();
    protected $client = NULL;
    public function __construct(array $gatewayParams)
    {
        $this->params = $gatewayParams;
        $this->client = \WHMCS\Module\Gateway\GoCardless\Client::factory($gatewayParams["accessToken"]);
    }
}

?>