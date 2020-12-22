<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Payment\PayMethod\Traits;

trait GatewayTrait
{
    public function loadGateway($gatewayName)
    {
        $gateway = new \WHMCS\Module\Gateway();
        if ($gateway->load($gatewayName)) {
            return $gateway;
        }
        return null;
    }
}

?>