<?php
/*
 * @ https://EasyToYou.eu - IonCube v10 Decoder Online
 * @ PHP 5.6
 * @ Decoder version: 1.0.4
 * @ Release: 02/06/2020
 *
 * @ ZendGuard Decoder PHP 5.6
 */

$this->layout("layouts/admin-content");
$this->start("body");
echo "\n<div class=\"market-connect-apps-container\">\n    <div class=\"row\">\n        ";
$count = 0;
$totalServices = count($services);
$lastClass = "";
$penultimateClass = "";
if ($totalServices % 3 !== 0) {
    if (($totalServices - 1) % 3 === 0) {
        $lastClass = " col-lg-offset-4";
    } else {
        if (($totalServices + 1) % 3 === 0) {
            $penultimateClass = " col-lg-offset-2";
        }
    }
}
foreach ($services as $service => $data) {
    $count++;
    $class = "col-lg-4 col-md-6";
    if ($penultimateClass && $count + 1 == $totalServices) {
        $class .= $penultimateClass;
    }
    if ($lastClass && $count == $totalServices) {
        $class .= $lastClass;
    }
    $this->insert("shared/service", array("service" => $service, "state" => $state, "data" => $data, "count" => $count, "class" => $class));
}
echo "    </div>\n</div>\n\n<a href=\"https://marketplace.whmcs.com/contact/connect\" target=\"_blank\" class=\"btn btn-default pull-right\" style=\"margin-left:6px;\">\n    <i class=\"fas fa-envelope fa-fw\"></i>\n    Contact Support\n</a>\n<a href=\"https://marketplace.whmcs.com/help/connect/kb\" target=\"_blank\" class=\"btn btn-default pull-right\" style=\"margin-left:6px;\">\n    <i class=\"fas fa-question-circle fa-fw\"></i>\n    Visit Knowledgebase\n</a>\n<a href=\"https://marketplace.whmcs.com/promotions\" target=\"_blank\" class=\"btn btn-default pull-right\">\n    <i class=\"fas fa-ticket-alt fa-fw\"></i>\n    Current Promotions\n</a>\n\n";
$this->insert("shared/tour");
$this->end();

?>