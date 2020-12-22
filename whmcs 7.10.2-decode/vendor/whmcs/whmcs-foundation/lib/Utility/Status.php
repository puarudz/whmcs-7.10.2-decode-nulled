<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

namespace WHMCS\Utility;

class Status
{
    const PENDING = "Pending";
    const PENDING_REGISTRATION = "Pending Registration";
    const PENDING_TRANSFER = "Pending Transfer";
    const ACTIVE = "Active";
    const INACTIVE = "Inactive";
    const CLOSED = "Closed";
    const COMPLETED = "Completed";
    const SUSPENDED = "Suspended";
    const TERMINATED = "Terminated";
    const GRACE = "Grace";
    const REDEMPTION = "Redemption";
    const EXPIRED = "Expired";
    const CANCELLED = "Cancelled";
    const FRAUD = "Fraud";
    const TRANSFERRED_AWAY = "Transferred Away";
    const CLIENT_STATUSES = NULL;
    const SERVICE_STATUSES = NULL;
    const DOMAIN_STATUSES = NULL;
}

?>