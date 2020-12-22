<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$serviceId = (int) App::getFromRequest("serviceid");
if (!$serviceId && App::isInRequest("accountid")) {
    $serviceId = (int) App::getFromRequest("accountid");
}
if (!$serviceId) {
    $apiresults = array("result" => "error", "message" => "Service ID is required");
} else {
    $service = WHMCS\Service\Service::with("product")->find($serviceId);
    if (is_null($service)) {
        $apiresults = array("result" => "error", "message" => "Service ID not found");
    } else {
        if (!$service->product->module) {
            $apiresults = array("result" => "error", "message" => "Service not assigned to a module");
        } else {
            $result = $service->legacyProvision();
            if ($result == "success") {
                $apiresults = array("result" => "success");
            } else {
                $apiresults = array("result" => "error", "message" => $result);
            }
        }
    }
}

?>