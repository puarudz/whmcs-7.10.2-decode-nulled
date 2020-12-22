<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Billing;

interface PricingInterface
{
    const TYPE_PRODUCT = "product";
    const TYPE_ADDON = "addon";
    const TYPE_CONFIGOPTION = "configoptions";
    const TYPE_DOMAIN_REGISTER = "domainregister";
    const TYPE_DOMAIN_TRANSFER = "domaintransfer";
    const TYPE_DOMAIN_RENEW = "domainrenew";
    const TYPE_DOMAIN_ADDON = "domainaddons";
    const TYPE_USAGE = "usage";
    public function pricingType();
}

?>