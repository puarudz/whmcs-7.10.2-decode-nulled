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

interface InvoiceItemInterface
{
    const TYPE_NONE = "";
    const TYPE_MASS_PAY_INVOICE = "Invoice";
    const TYPE_ADD_FUNDS_INVOICE = "AddFunds";
    const TYPE_SERVICE = "Hosting";
    const TYPE_SERVICE_ADDON = "Addon";
    const TYPE_DOMAIN = "Domain";
    const TYPE_DOMAIN_REGISTRATION = "DomainRegister";
    const TYPE_DOMAIN_TRANSFER = "DomainTransfer";
    const TYPE_DOMAIN_EMAIL_FORWARDING = "DomainAddonEMF";
    const TYPE_DOMAIN_ID_PROTECTION = "DomainAddonIDP";
    const TYPE_DOMAIN_DNS_MANAGEMENT = "DomainAddonDNS";
    const TYPE_UPGRADE = "Upgrade";
    const TYPE_BILLABLE_ITEM = "Item";
    const TYPE_BILLABLE_USAGE = "Usage";
    const PSEUDO_TYPE_PRORATA_PRODUCT = "ProrataProduct";
    const PSEUDO_TYPE_PRORATA_PRODUCT_ADDON = "ProrataAddon";
}

?>