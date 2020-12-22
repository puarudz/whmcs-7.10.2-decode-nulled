<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Contracts;

interface ServiceProvisionInterface
{
    public function provision($model, array $params);
    public function configure($model, array $params);
    public function cancel($model, array $params);
    public function renew($model, array $response);
    public function install($model);
}

?>